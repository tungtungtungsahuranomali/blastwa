<?php
/**
 * API: Manage contact lists (JSON storage)
 * 
 * GET    ?list=name     — get contacts from a list
 * POST   ?save=name     — save contacts (body: { numbers: [...] })
 * GET    ?lists          — list all saved contact lists
 * DELETE ?list=name     — delete a contact list
 */

header('Content-Type: application/json');

$storageDir = __DIR__ . '/../lists';
if (!is_dir($storageDir)) mkdir($storageDir, 0777, true);

$action = $_GET['action'] ?? '';

// GET ?lists — list all saved lists
if (isset($_GET['lists'])) {
    $files = glob($storageDir . '/*.json');
    $lists = [];
    foreach ($files as $f) {
        $name = basename($f, '.json');
        $data = json_decode(file_get_contents($f), true);
        $lists[] = [
            'name'  => $name,
            'count' => is_array($data) ? count($data) : 0,
        ];
    }
    echo json_encode(['success' => true, 'lists' => $lists]);
    exit;
}

// GET ?list=name — load a specific list
if (isset($_GET['list'])) {
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['list']);
    $path = $storageDir . '/' . $name . '.json';
    if (!file_exists($path)) {
        echo json_encode(['success' => false, 'error' => 'List not found']);
        exit;
    }
    $data = json_decode(file_get_contents($path), true);
    echo json_encode(['success' => true, 'name' => $name, 'numbers' => $data]);
    exit;
}

// DELETE ?list=name — delete a list
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['list'])) {
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['list']);
    $path = $storageDir . '/' . $name . '.json';
    if (file_exists($path)) unlink($path);
    echo json_encode(['success' => true]);
    exit;
}

// POST ?save=name — save a list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['save'])) {
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['save']);
    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Invalid name']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $numbers = $input['numbers'] ?? [];
    
    if (!is_array($numbers)) {
        echo json_encode(['success' => false, 'error' => 'Numbers must be an array']);
        exit;
    }
    
    // Clean & deduplicate
    $cleaned = [];
    foreach ($numbers as $n) {
        $n = preg_replace('/[^0-9]/', '', $n);
        if (!empty($n) && strlen($n) >= 10) {
            $cleaned[$n] = true;
        }
    }
    $sorted = array_keys($cleaned);
    sort($sorted);
    
    file_put_contents($storageDir . '/' . $name . '.json', json_encode($sorted, JSON_PRETTY_PRINT));
    
    echo json_encode(['success' => true, 'name' => $name, 'count' => count($sorted)]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
