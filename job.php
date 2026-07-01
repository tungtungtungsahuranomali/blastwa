<?php
/**
 * CLI helper untuk blast
 * 
 * Contoh:
 *   php job.php blast -f contacts.json -m "Halo" --limit 10 --cooldown 3
 *   php job.php blast -f listnomordiblast.txt -m "Promo!"
 *   php job.php blast -n "628117774884,6281234567890" -m "Test"
 *   php job.php log              # lihat log terbaru
 *   php job.php logs             # daftar semua log
 */

if (php_sapi_name() !== 'cli') die("CLI only.\n");

$action = $argv[1] ?? 'help';

switch ($action) {
    case 'blast':
        cmd_blast();
        break;
    case 'log':
        cmd_log();
        break;
    case 'logs':
        cmd_logs();
        break;
    default:
        echo "Usage:\n";
        echo "  php job.php blast -f <file> -m \"pesan\" [--limit N] [--cooldown N]\n";
        echo "  php job.php blast -n \"num1,num2\" -m \"pesan\"\n";
        echo "  php job.php log              — lihat log terbaru\n";
        echo "  php job.php logs             — daftar semua log\n";
        break;
}

function cmd_blast() {
    passthru('php ' . __DIR__ . '/api/blast.php ' . implode(' ', array_slice($argv, 2)));
}

function cmd_log() {
    $logsDir = __DIR__ . '/logs';
    $files = glob($logsDir . '/blast-*.log');
    if (empty($files)) { echo "Belum ada log.\n"; return; }
    rsort($files);
    echo file_get_contents($files[0]);
}

function cmd_logs() {
    $logsDir = __DIR__ . '/logs';
    $files = glob($logsDir . '/blast-*.log');
    if (empty($files)) { echo "Belum ada log.\n"; return; }
    rsort($files);
    foreach ($files as $f) {
        $size = filesize($f);
        $time = date('Y-m-d H:i:s', filemtime($f));
        printf("%-35s %s (%s)\n", basename($f), $time, $size > 1024 ? round($size/1024,1).'KB' : $size.'B');
    }
}
