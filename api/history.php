<?php
/**
 * API: Blast history logging
 * 
 * GET    ?list         — list all blast sessions
 * GET    ?view=id      — view a specific blast session details
 * POST   ?save         — save a blast session (body: { name, message, config, results: [{phone, status, response}] })
 * DELETE ?delete=id    — delete a blast session
 */

header('Content-Type: application/json');

$storageDir = __DIR__ . '/../blasts';
if (!is_dir($storageDir)) mkdir($storageDir, 0777, true);

// GET ?list
if (isset($_GET['list'])) {
    $files = glob($storageDir . '/*.json');
    $sessions = [];
    foreach ($files as $f) {
        $data = json_decode(file_get_contents($f), true);
        if (!$data) continue;
        $sessions[] = [
            'id'        => basename($f, '.json'),
            'name'      => $data['name'] ?? 'Unknown',
            'date'      => $data['date'] ?? '',
            'total'     => $data['total'] ?? 0,
            'success'   => $data['success'] ?? 0,
            'failed'    => $data['failed'] ?? 0,
            'config'    => $data['config'] ?? [],
        ];
    }
    // Sort newest first
    usort($sessions, fn($a, $b) => strcmp($b['id'], $a['id']));
    echo json_encode(['success' => true, 'sessions' => $sessions]);
    exit;
}

// GET ?view=id
if (isset($_GET['view'])) {
    $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['view']);
    $path = $storageDir . '/' . $id . '.json';
    if (!file_exists($path)) {
        echo json_encode(['success' => false, 'error' => 'Session not found']);
        exit;
    }
    echo file_get_contents($path);
    exit;
}

// POST ?save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['save'])) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['results'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $id = date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 6);
    
    $session = [
        'id'      => $id,
        'name'    => $input['name'] ?? 'Blast ' . date('Y-m-d H:i'),
        'date'    => date('Y-m-d H:i:s'),
        'message' => $input['message'] ?? '',
        'config'  => $input['config'] ?? [],
        'total'   => count($input['results']),
        'success' => count(array_filter($input['results'], fn($r) => $r['status'] === 'ok')),
        'failed'  => count(array_filter($input['results'], fn($r) => $r['status'] === 'fail')),
        'results' => $input['results'],
    ];

    file_put_contents($storageDir . '/' . $id . '.json', json_encode($session, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

// DELETE ?delete=id
if (isset($_GET['delete'])) {
    $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['delete']);
    $path = $storageDir . '/' . $id . '.json';
    if (file_exists($path)) unlink($path);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
