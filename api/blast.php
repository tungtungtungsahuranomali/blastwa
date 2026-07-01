<?php
/**
 * BLAST API — kirim pesan massal ke banyak nomor
 * 
 * CLI:
 *   php api/blast.php -f listnomordiblast.txt -m "Halo" --limit 10 --cooldown 3
 *   php api/blast.php -f contacts.json -m "Promo!" --limit 20 --cooldown 2
 * 
 * HTTP (pakai curl atau dashboard):
 *   POST /api/blast.php
 *   Header: Token: technical
 *   Body: {
 *     "numbers": ["628117774884", "6281234567890"],
 *     "message": "Halo",
 *     "limit": 10,
 *     "cooldown": 3
 *   }
 * 
 * Cek progress: lihat file di folder logs/ 
 *   GET /api/blast-log.php?file=blast-20260701-143000.log
 */

$apiUrl = "http://202.8.28.198:3000/chat/send/text";
$token  = "technical";
$logsDir = __DIR__ . '/../logs';

if (!is_dir($logsDir)) mkdir($logsDir, 0777, true);

// ============ CLI MODE ============
if (php_sapi_name() === 'cli') {
    $opts = getopt('f:m:', ['limit:', 'cooldown:']);
    $file    = $opts['f'] ?? '';
    $message = $opts['m'] ?? '';
    $limit   = (int)($opts['limit'] ?? 10);
    $cooldown = (int)($opts['cooldown'] ?? 3);

    if (empty($file) || empty($message)) {
        die("Usage: php api/blast.php -f <file|json> -m \"message\" --limit 10 --cooldown 3\n");
    }

    if (!file_exists($file)) die("File not found: $file\n");

    // Load numbers — support .json or .txt
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if ($ext === 'json') {
        $numbers = json_decode(file_get_contents($file), true);
        if (!is_array($numbers)) die("Invalid JSON file\n");
    } else {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $numbers = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || str_starts_with($line, '//')) continue;
            foreach (preg_split('/[\s,\/]+/', $line) as $part) {
                $part = trim(preg_replace('/[^0-9\+]/', '', $part));
                if (!empty($part)) $numbers[] = $part;
            }
        }
    }

    // Clean numbers
    $cleaned = [];
    foreach ($numbers as $n) {
        $n = preg_replace('/[^0-9]/', '', $n);
        if (str_starts_with($n, '0')) $n = '62' . substr($n, 1);
        elseif (str_starts_with($n, '+62')) $n = '62' . substr($n, 3);
        if (strlen($n) >= 10) $cleaned[$n] = true;
    }
    $numbers = array_values(array_unique(array_keys($cleaned)));
    sort($numbers);

    if (empty($numbers)) die("No valid numbers found\n");

    echo "=== BLAST START ===\n";
    echo "Target : " . count($numbers) . " numbers\n";
    echo "Limit  : {$limit}/menit\n";
    echo "Cooldown: {$cooldown}s\n\n";

    blast_process($numbers, $message, $limit, $cooldown, $apiUrl, $token, $logsDir);
    exit;
}

// ============ HTTP MODE ============
header('Content-Type: application/json');

// Verify token
$reqToken = $_SERVER['HTTP_TOKEN'] ?? $_SERVER['HTTP_TOKEN'] ?? '';
if ($reqToken !== $token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['numbers']) || empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'numbers and message required']);
    exit;
}

$numbers = $input['numbers'];
$message = $input['message'];
$limit   = (int)($input['limit'] ?? 10);
$cooldown = (int)($input['cooldown'] ?? 3);

// Clean numbers
$cleaned = [];
foreach ($numbers as $n) {
    $n = preg_replace('/[^0-9]/', '', $n);
    if (str_starts_with($n, '0')) $n = '62' . substr($n, 1);
    elseif (str_starts_with($n, '+62')) $n = '62' . substr($n, 3);
    if (strlen($n) >= 10) $cleaned[$n] = true;
}
$numbers = array_values(array_unique(array_keys($cleaned)));

if (empty($numbers)) {
    echo json_encode(['success' => false, 'error' => 'No valid numbers']);
    exit;
}

// Send response immediately, flush, then process
echo json_encode([
    'success' => true,
    'total'   => count($numbers),
    'message' => 'Blast started. Check logs/ for progress.',
]) . "\n";
flush();

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

blast_process($numbers, $message, $limit, $cooldown, $apiUrl, $token, $logsDir);

// ============ PROCESSOR ============
function blast_process($numbers, $message, $limit, $cooldown, $apiUrl, $token, $logsDir) {
    $timestamp = date('Ymd-His');
    $logFile = $logsDir . '/blast-' . $timestamp . '.log';
    $fh = fopen($logFile, 'a');
    
    fwrite($fh, "=== BLAST START === " . date('Y-m-d H:i:s') . "\n");
    fwrite($fh, "Total: " . count($numbers) . " | Limit: {$limit}/min | Cooldown: {$cooldown}s\n\n");
    
    $success = 0;
    $failed  = 0;
    $windowSent = 0;
    $windowStart = time();

    foreach ($numbers as $i => $phone) {
        // Rate limit check
        $now = time();
        if ($now - $windowStart >= 60) {
            $windowSent = 0;
            $windowStart = $now;
        }
        if ($windowSent >= $limit) {
            $wait = max(1, 60 - ($now - $windowStart));
            $line = "[rate limit] tunggu {$wait}dtk...\n";
            fwrite($fh, $line);
            if (php_sapi_name() === 'cli') echo $line;
            sleep($wait);
            $windowSent = 0;
            $windowStart = time();
        }

        // Send
        $result = send_message($phone, $message, $apiUrl, $token);
        $windowSent++;

        $time = date('H:i:s');
        if ($result['success']) {
            $success++;
            $line = "[{$time}] #" . ($i+1) . " {$phone} ✓ TERKIRIM\n";
        } else {
            $failed++;
            $err = $result['error'] ?: 'HTTP ' . $result['http_code'];
            $line = "[{$time}] #" . ($i+1) . " {$phone} ✗ GAGAL: {$err}\n";
        }

        fwrite($fh, $line);
        if (php_sapi_name() === 'cli') echo $line;

        // Cooldown
        if ($cooldown > 0 && $i < count($numbers) - 1) {
            sleep($cooldown);
        }
    }

    $summary = "\n=== SELESAI === " . date('Y-m-d H:i:s') . " | Terkirim: {$success} | Gagal: {$failed}\n";
    fwrite($fh, $summary);
    if (php_sapi_name() === 'cli') echo $summary;
    
    fclose($fh);
}

function send_message($phone, $message, $apiUrl, $token) {
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

    if ($error) {
        return ['success' => false, 'error' => $error, 'http_code' => 0];
    }
    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'http_code' => $httpCode, 'response' => $response];
    }
    return ['success' => false, 'error' => "HTTP {$httpCode}", 'http_code' => $httpCode, 'response' => $response];
}
