<?php
/**
 * API: Manage message templates (.md files)
 * 
 * GET    ?list         — list all templates
 * GET    ?load=name    — load a template
 * POST   ?save=name    — save/create template (body: { content: "..." })
 * DELETE ?delete=name  — delete a template
 */

header('Content-Type: application/json');

$storageDir = __DIR__ . '/../templates';
if (!is_dir($storageDir)) mkdir($storageDir, 0777, true);

// GET ?list
if (isset($_GET['list'])) {
    $files = glob($storageDir . '/*.md');
    $templates = [];
    foreach ($files as $f) {
        $name = basename($f, '.md');
        $templates[] = [
            'name' => $name,
            'size' => filesize($f),
            'modified' => date('Y-m-d H:i', filemtime($f)),
        ];
    }
    echo json_encode(['success' => true, 'templates' => $templates]);
    exit;
}

// GET ?load=name
if (isset($_GET['load'])) {
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['load']);
    $path = $storageDir . '/' . $name . '.md';
    if (!file_exists($path)) {
        echo json_encode(['success' => false, 'error' => 'Template not found']);
        exit;
    }
    $content = file_get_contents($path);
    echo json_encode(['success' => true, 'name' => $name, 'content' => $content]);
    exit;
}

// POST ?save=name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['save'])) {
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['save']);
    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Invalid name']);
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $content = $input['content'] ?? '';
    file_put_contents($storageDir . '/' . $name . '.md', $content);
    echo json_encode(['success' => true, 'name' => $name]);
    exit;
}

// DELETE ?delete=name
if (isset($_GET['delete'])) {
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['delete']);
    $path = $storageDir . '/' . $name . '.md';
    if (file_exists($path)) unlink($path);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
