<?php
/**
 * CRON Worker — processes blast jobs in the background
 * 
 * Call this every few seconds/minutes via cron:
 *   * * * * * php /path/to/api/cron.php
 *   or via web: GET /api/cron.php?key=your-secret-key
 * 
 * How it works:
 * 1. Find a pending/running job
 * 2. Send ONE message (respects rate limit & cooldown)
 * 3. Update job progress
 * 4. Exit (next cron call continues)
 * 
 * This avoids timeout issues — each cron execution only sends 1 message.
 */

// Security: optional secret key to prevent unauthorized cron triggers
$secretKey = 'blastwa-secret-2024'; // change this!

if (php_sapi_name() !== 'cli') {
    // Web request — require key
    $key = $_GET['key'] ?? '';
    if ($key !== $secretKey) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid key']));
    }
    header('Content-Type: application/json');
}

$storageDir = __DIR__ . '/../blasts';
$apiUrl     = "http://202.8.28.198:3000/chat/send/text";
$token      = "takeoff";

// Find active job (pending or running)
$files = glob($storageDir . '/job_*.json');
$job = null;

foreach ($files as $f) {
    $data = json_decode(file_get_contents($f), true);
    if (!$data) continue;
    if ($data['status'] === 'pending' || $data['status'] === 'running') {
        $job = &$data; // reference for modification
        $jobPath = $f;
        break;
    }
}

if (!$job) {
    $msg = "No active job found.\n";
    if (php_sapi_name() === 'cli') echo $msg;
    else echo json_encode(['success' => false, 'message' => trim($msg)]);
    exit;
}

// Mark as running
if ($job['status'] === 'pending') {
    $job['status'] = 'running';
    $job['started'] = date('Y-m-d H:i:s');
}

// Check if all numbers processed
if ($job['index'] >= $job['total']) {
    $job['status'] = 'completed';
    $job['completed'] = date('Y-m-d H:i:s');
    file_put_contents($jobPath, json_encode($job, JSON_PRETTY_PRINT));
    $msg = "Job {$job['id']} completed.\n";
    if (php_sapi_name() === 'cli') echo $msg;
    else echo json_encode(['success' => true, 'message' => trim($msg)]);
    exit;
}

// Rate limit check
$now = time();
if ($now - $job['window_start'] >= 60) {
    // Reset window
    $job['window_sent'] = 0;
    $job['window_start'] = $now;
}

$limitPerMin = $job['config']['limitPerMin'] ?? 10;
$cooldown    = $job['config']['cooldown'] ?? 3;

if ($job['window_sent'] >= $limitPerMin) {
    // Rate limit reached — need to wait until window resets
    $msg = "Rate limit reached ({$limitPerMin}/min). Try again later.\n";
    if (php_sapi_name() === 'cli') echo $msg;
    else echo json_encode(['success' => false, 'message' => trim($msg)]);
    exit;
}

// Check cooldown from last send
if ($cooldown > 0 && !empty($job['results'])) {
    $lastResult = end($job['results']);
    $lastTime = strtotime($lastResult['time']);
    $elapsed = $now - $lastTime;
    if ($elapsed < $cooldown) {
        $msg = "Cooldown active. Wait " . ($cooldown - $elapsed) . "s more.\n";
        if (php_sapi_name() === 'cli') echo $msg;
        else echo json_encode(['success' => false, 'message' => trim($msg)]);
        exit;
    }
}

// Get next number
$phone = $job['numbers'][$job['index']];
$message = $job['message'];

// Send via API
$payload = json_encode([
    "Phone" => $phone,
    "Body"  => $message,
]);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        "Token: $token",
        "Content-Type: application/json",
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

// Determine status
if ($error) {
    $status = 'fail';
    $respText = $error;
} elseif ($httpCode >= 200 && $httpCode < 300) {
    $status = 'ok';
    $respText = 'OK';
} else {
    $status = 'fail';
    $respText = "HTTP {$httpCode}: " . substr($response, 0, 100);
}

// Record result
$job['results'][] = [
    'phone'    => $phone,
    'status'   => $status,
    'response' => $respText,
    'time'     => date('Y-m-d H:i:s'),
];

if ($status === 'ok') {
    $job['sent']++;
} else {
    $job['failed']++;
}

$job['index']++;
$job['window_sent']++;
$job['last_run'] = date('Y-m-d H:i:s');

// Save job
file_put_contents($jobPath, json_encode($job, JSON_PRETTY_PRINT));

// Output
$msg = "[{$job['index']}/{$job['total']}] {$phone} => {$status}\n";
if (php_sapi_name() === 'cli') {
    echo $msg;
} else {
    echo json_encode([
        'success' => true,
        'job_id'  => $job['id'],
        'index'   => $job['index'],
        'total'   => $job['total'],
        'phone'   => $phone,
        'status'  => $status,
        'sent'    => $job['sent'],
        'failed'  => $job['failed'],
    ]);
}
