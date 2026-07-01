<?php
/**
 * Blast Log Viewer — lihat progress & riwayat pengiriman
 * 
 * GET  /api/blast-log.php              — daftar semua file log
 * GET  /api/blast-log.php?file=xxx.log — isi file log tertentu
 * GET  /api/blast-log.php?last         — log terbaru
 * GET  /api/blast-log.php?tail=10      — 10 baris terakhir dari log terbaru
 */

header('Content-Type: application/json');

$logsDir = __DIR__ . '/../logs';

// GET ?list or no params — list all logs
if (!isset($_GET['file']) && !isset($_GET['last']) && !isset($_GET['tail'])) {
    $files = glob($logsDir . '/blast-*.log');
    $logs = [];
    foreach ($files as $f) {
        $logs[] = [
            'file'     => basename($f),
            'size'     => filesize($f),
            'modified' => date('Y-m-d H:i:s', filemtime($f)),
        ];
    }
    // Sort newest first
    usort($logs, fn($a, $b) => strcmp($b['file'], $a['file']));
    echo json_encode(['success' => true, 'logs' => $logs]);
    exit;
}

// GET ?last — get latest log
if (isset($_GET['last'])) {
    $files = glob($logsDir . '/blast-*.log');
    if (empty($files)) {
        echo json_encode(['success' => false, 'error' => 'No logs found']);
        exit;
    }
    rsort($files);
    $file = $files[0];
    $content = file_get_contents($file);
    echo json_encode([
        'success' => true,
        'file'    => basename($file),
        'content' => $content,
    ]);
    exit;
}

// GET ?tail=N — last N lines from latest log
if (isset($_GET['tail'])) {
    $lines = (int)($_GET['tail'] ?? 10);
    $files = glob($logsDir . '/blast-*.log');
    if (empty($files)) {
        echo json_encode(['success' => false, 'error' => 'No logs found']);
        exit;
    }
    rsort($files);
    $file = $files[0];
    $content = file_get_contents($file);
    $parts = explode("\n", trim($content));
    $tail = array_slice($parts, -$lines);
    echo json_encode([
        'success' => true,
        'file'    => basename($file),
        'lines'   => $tail,
    ]);
    exit;
}

// GET ?file=xxx.log — view specific log
$fileName = basename($_GET['file']); // prevent path traversal
$path = $logsDir . '/' . $fileName;
if (!file_exists($path)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'File not found']);
    exit;
}

$content = file_get_contents($path);
echo json_encode([
    'success' => true,
    'file'    => $fileName,
    'content' => $content,
]);
