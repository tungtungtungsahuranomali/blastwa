<?php
/**
 * API: Queue Manager for Blast Jobs (cron-based background processing)
 * 
 * POST ?create         — create a new blast job
 *   body: { name, message, numbers: [...], config: { limitPerMin, cooldown } }
 * 
 * GET  ?status=jobId   — get job progress & status
 * GET  ?list           — list all jobs
 * POST ?cancel=jobId   — cancel a running job
 * POST ?delete=jobId   — delete a job record
 */

header('Content-Type: application/json');

$storageDir = __DIR__ . '/../blasts';
if (!is_dir($storageDir)) mkdir($storageDir, 0777, true);

// GET ?list
if (isset($_GET['list'])) {
    $files = glob($storageDir . '/job_*.json');
    $jobs = [];
    foreach ($files as $f) {
        $data = json_decode(file_get_contents($f), true);
        if (!$data) continue;
        $jobs[] = [
            'id'        => $data['id'] ?? basename($f, '.json'),
            'name'      => $data['name'] ?? 'Unknown',
            'status'    => $data['status'] ?? 'unknown',
            'created'   => $data['created'] ?? '',
            'total'     => $data['total'] ?? 0,
            'sent'      => $data['sent'] ?? 0,
            'failed'    => $data['failed'] ?? 0,
            'progress'  => $data['total'] > 0 ? round(($data['sent'] + $data['failed']) / $data['total'] * 100) : 0,
        ];
    }
    // Sort newest first
    usort($jobs, fn($a, $b) => strcmp($b['created'], $a['created']));
    echo json_encode(['success' => true, 'jobs' => $jobs]);
    exit;
}

// GET ?status=id
if (isset($_GET['status'])) {
    $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['status']);
    $path = $storageDir . '/job_' . $id . '.json';
    if (!file_exists($path)) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit;
    }
    $data = json_decode(file_get_contents($path), true);
    echo json_encode([
        'success'  => true,
        'id'       => $data['id'] ?? $id,
        'name'     => $data['name'] ?? '',
        'status'   => $data['status'] ?? 'unknown',
        'message'  => $data['message'] ?? '',
        'config'   => $data['config'] ?? [],
        'total'    => $data['total'] ?? 0,
        'sent'     => $data['sent'] ?? 0,
        'failed'   => $data['failed'] ?? 0,
        'progress' => $data['total'] > 0 ? round(($data['sent'] + $data['failed']) / $data['total'] * 100) : 0,
        'results'  => $data['results'] ?? [],
    ]);
    exit;
}

// POST ?create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['create'])) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['numbers']) || empty($input['message'])) {
        echo json_encode(['success' => false, 'error' => 'Numbers and message required']);
        exit;
    }

    $id = date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 6);

    // Clean & deduplicate numbers
    $numbers = [];
    foreach ($input['numbers'] as $n) {
        $n = preg_replace('/[^0-9]/', '', $n);
        if (!empty($n) && strlen($n) >= 10) {
            $numbers[$n] = true;
        }
    }
    $numbers = array_keys($numbers);

    $job = [
        'id'        => $id,
        'name'      => $input['name'] ?? 'Blob ' . date('Y-m-d H:i'),
        'status'    => 'pending',   // pending | running | completed | cancelled
        'created'   => date('Y-m-d H:i:s'),
        'started'   => null,
        'completed' => null,
        'message'   => $input['message'],
        'config'    => $input['config'] ?? ['limitPerMin' => 10, 'cooldown' => 3],
        'total'     => count($numbers),
        'sent'      => 0,
        'failed'    => 0,
        'index'     => 0,          // next number index to process
        'window_sent'    => 0,     // sent in current minute window
        'window_start'   => time(),
        'numbers'   => $numbers,   // full list
        'results'   => [],         // [{phone, status, response, time}]
    ];

    file_put_contents($storageDir . '/job_' . $id . '.json', json_encode($job, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'id'      => $id,
        'total'   => count($numbers),
        'status'  => 'pending',
    ]);
    exit;
}

// POST ?cancel=id
if (isset($_GET['cancel'])) {
    $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['cancel']);
    $path = $storageDir . '/job_' . $id . '.json';
    if (!file_exists($path)) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit;
    }
    $data = json_decode(file_get_contents($path), true);
    if ($data['status'] === 'running' || $data['status'] === 'pending') {
        $data['status'] = 'cancelled';
        $data['completed'] = date('Y-m-d H:i:s');
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
    echo json_encode(['success' => true, 'status' => 'cancelled']);
    exit;
}

// POST ?delete=id
if (isset($_GET['delete'])) {
    $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['delete']);
    $path = $storageDir . '/job_' . $id . '.json';
    if (file_exists($path)) unlink($path);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
