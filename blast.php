<?php
/**
 * WhatsApp Blast via API
 * 
 * API: http://202.8.28.198:3000/chat/send/text
 * Token: takeoff
 */

$apiUrl  = "http://202.8.28.198:3000/chat/send/text";
$token   = "takeoff";
$delay   = 2; // delay in seconds between messages

// ============ FUNCTIONS ============

function sendMessage($phone, $body, $apiUrl, $token) {
    $payload = json_encode([
        "Phone" => $phone,
        "Body"  => $body
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

    return [
        "http_code" => $httpCode,
        "response"  => $response,
        "error"     => $error,
    ];
}

function loadNumbers($file) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $numbers = [];
    foreach ($lines as $line) {
        $line = trim($line);
        // skip comments
        if (str_starts_with($line, '#') || str_starts_with($line, '//')) continue;
        // clean number: remove non-digit chars
        $num = preg_replace('/[^0-9]/', '', $line);
        if (!empty($num)) $numbers[] = $num;
    }
    return $numbers;
}

// ============ CLI / WEB HANDLER ============

$isCli = (php_sapi_name() === 'cli');

if ($isCli) {
    // CLI Mode
    $shortopts = "f:m:d:";
    $longopts  = ["file:", "message:", "delay:"];
    $options   = getopt($shortopts, $longopts);

    $file    = $options['f'] ?? $options['file'] ?? 'numbers.txt';
    $message = $options['m'] ?? $options['message'] ?? '';
    $delay   = $options['d'] ?? $options['delay'] ?? $delay;

    if (empty($message)) {
        echo "Usage: php blast.php -f numbers.txt -m \"Your message\" [-d 2]\n";
        exit(1);
    }

    if (!file_exists($file)) {
        echo "ERROR: File '$file' not found.\n";
        exit(1);
    }

    $numbers = loadNumbers($file);
    if (empty($numbers)) {
        echo "No valid numbers found in '$file'.\n";
        exit(1);
    }

    echo "=== BLAST START ===\n";
    echo "Target : " . count($numbers) . " numbers\n";
    echo "Message: $message\n";
    echo "Delay  : {$delay}s\n\n";

    $success = 0;
    $failed  = 0;

    foreach ($numbers as $i => $phone) {
        $result = sendMessage($phone, $message, $apiUrl, $token);

        $status = ($result['http_code'] >= 200 && $result['http_code'] < 300) ? "✓" : "✗";
        if ($result['error']) {
            echo "[$status] " . ($i+1) . ". $phone => ERROR: {$result['error']}\n";
            $failed++;
        } else {
            echo "[$status] " . ($i+1) . ". $phone => HTTP {$result['http_code']}: {$result['response']}\n";
            if ($status === "✓") $success++;
            else $failed++;
        }

        if ($i < count($numbers) - 1) sleep($delay);
    }

    echo "\n=== DONE: $success sent, $failed failed ===\n";

} else {
    // Web Mode
    echo "<!DOCTYPE html><html><head><title>WA Blast</title>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
    echo "<style>
        body { font-family: Arial; max-width: 700px; margin: 20px auto; padding: 0 15px; }
        input, textarea, button { width: 100%; padding: 10px; margin: 5px 0 15px; box-sizing: border-box; }
        textarea { min-height: 200px; font-family: monospace; }
        button { background: #25D366; color: white; border: none; cursor: pointer; font-size: 16px; }
        button:hover { opacity: 0.9; }
        .result { background: #f5f5f5; padding: 10px; border-radius: 5px; white-space: pre-wrap; font-size: 13px; }
        h2 { color: #128C7E; }
        label { font-weight: bold; }
        .info { background: #e8f5e9; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style></head><body>";

    echo "<h2>WhatsApp Blast</h2>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawNumbers = $_POST['numbers'] ?? '';
        $message    = $_POST['message'] ?? '';

        $numbers = [];
        foreach (explode("\n", $rawNumbers) as $line) {
            $line = trim($line);
            $num = preg_replace('/[^0-9]/', '', $line);
            if (!empty($num)) $numbers[] = $num;
        }

        echo "<div class='result'>";
        echo "Sending to " . count($numbers) . " numbers...\n\n";

        $success = 0;
        $failed  = 0;
        foreach ($numbers as $i => $phone) {
            $result = sendMessage($phone, $message, $apiUrl, $token);
            $status = ($result['http_code'] >= 200 && $result['http_code'] < 300) ? "✓" : "✗";
            if ($result['error']) {
                echo "[$status] {$phone} => ERROR: {$result['error']}\n";
                $failed++;
            } else {
                echo "[$status] {$phone} => HTTP {$result['http_code']}\n";
                if ($status === "✓") $success++;
                else $failed++;
            }
            if ($i < count($numbers) - 1) sleep($delay);
        }

        echo "\nDONE: $success sent, $failed failed\n";
        echo "</div>";
        echo "<p><a href=''>Back</a></p>";

    } else {
        echo "<div class='info'>Masukkan nomor dan pesan, lalu klik Send.</div>";
        echo "<form method='post'>";
        echo "<label>Nomor Telepon (satu per baris, dengan/tanpa format):</label>";
        echo "<textarea name='numbers' placeholder='6281234567890&#10;6289876543210'>628117774884</textarea>";
        echo "<label>Pesan:</label>";
        echo "<textarea name='message' placeholder='Tulis pesan...' style='min-height:100px'>Halo</textarea>";
        echo "<button type='submit'>Kirim Blast</button>";
        echo "</form>";
    }

    echo "</body></html>";
}
