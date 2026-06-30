<?php
/**
 * CLI: Create & manage blast jobs for cron processing
 * 
 * Usage:
 *   # Create a new blast job
 *   php job.php create -f numbers.txt -m "Halo, promo!" --limit 10 --cooldown 3
 *   
 *   # Create with inline numbers
 *   php job.php create -n "628117774884,6281234567890" -m "Halo test"
 *   
 *   # Process next pending message (for cron)
 *   php job.php work
 *   
 *   # Process all remaining messages (for cron, one at a time with cooldown)
 *   php job.php run
 *   
 *   # List all jobs
 *   php job.php list
 *   
 *   # View job status
 *   php job.php status <job-id>
 *   
 *   # Cancel a job
 *   php job.php cancel <job-id>
 */

if (php_sapi_name() !== 'cli') {
    die("CLI only.\n");
}

$action = $argv[1] ?? 'help';

switch ($action) {
    case 'create':
        cmd_create();
        break;
    case 'work':
        cmd_work();
        break;
    case 'run':
        cmd_run();
        break;
    case 'list':
        cmd_list();
        break;
    case 'status':
        cmd_status();
        break;
    case 'cancel':
        cmd_cancel();
        break;
    default:
        echo "Usage:\n";
        echo "  php job.php create -f <file> -m <message> [--limit N] [--cooldown N] [--name NAME]\n";
        echo "  php job.php create -n \"num1,num2\" -m <message>\n";
        echo "  php job.php work              # process 1 message (for cron)\n";
        echo "  php job.php run               # process all remaining (blocking)\n";
        echo "  php job.php list              # list all jobs\n";
        echo "  php job.php status <job-id>   # view job details\n";
        echo "  php job.php cancel <job-id>   # cancel a job\n";
        break;
}

// ===== COMMANDS =====

function cmd_create() {
    $opts = getopt('f:m:n:', ['limit:', 'cooldown:', 'name:']);
    
    $message = $opts['m'] ?? '';
    $file    = $opts['f'] ?? '';
    $numbers = $opts['n'] ?? '';
    
    if (empty($message)) die("ERROR: -m <message> required\n");
    
    // Load numbers
    if ($file) {
        if (!file_exists($file)) die("ERROR: File '$file' not found\n");
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $numbers = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || str_starts_with($line, '//')) continue;
            // Split by / or comma
            foreach (preg_split('/[\s,\/]+/', $line) as $part) {
                $part = trim(preg_replace('/[^0-9\+]/', '', $part));
                if (!empty($part)) $numbers[] = $part;
            }
        }
    } elseif ($numbers) {
        $numbers = explode(',', $numbers);
    } else {
        die("ERROR: Use -f <file> or -n \"num1,num2\"\n");
    }
    
    // Clean numbers
    $cleaned = [];
    foreach ($numbers as $n) {
        $n = preg_replace('/[^0-9]/', '', $n);
        if (strlen($n) >= 10) $cleaned[$n] = true;
    }
    $numbers = array_keys($cleaned);
    
    if (empty($numbers)) die("ERROR: No valid numbers\n");
    
    $limit    = $opts['limit'] ?? 10;
    $cooldown = $opts['cooldown'] ?? 3;
    $name     = $opts['name'] ?? 'CLI Blast ' . date('Y-m-d H:i');
    
    // Call API
    $payload = json_encode([
        'name'    => $name,
        'numbers' => $numbers,
        'message' => $message,
        'config'  => ['limitPerMin' => (int)$limit, 'cooldown' => (int)$cooldown],
    ]);
    
    $url = "http://localhost/blastwa/api/queue.php?create"; // adjust URL if needed
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http >= 200 && $http < 300) {
        $data = json_decode($res, true);
        echo "✅ Job created: {$data['id']}\n";
        echo "   Total: {$data['total']} numbers\n";
        echo "   Status: {$data['status']}\n";
        echo "\nNow run cron or:\n";
        echo "  php job.php run                    # process all\n";
        echo "  php job.php work                   # process 1 message\n";
    } else {
        echo "❌ Error: HTTP $http\n$res\n";
    }
}

function cmd_work() {
    // Process one message using the worker
    $output = shell_exec('php ' . __DIR__ . '/api/cron.php 2>&1');
    echo $output;
}

function cmd_run() {
    // Process all messages one by one (blocking, respects cooldown)
    $storageDir = __DIR__ . '/blasts';
    
    while (true) {
        // Find active job
        $files = glob($storageDir . '/job_*.json');
        $job = null;
        foreach ($files as $f) {
            $data = json_decode(file_get_contents($f), true);
            if ($data && ($data['status'] === 'pending' || $data['status'] === 'running')) {
                $job = $data;
                $jobPath = $f;
                break;
            }
        }
        
        if (!$job) {
            echo "No active jobs.\n";
            break;
        }
        
        if ($job['index'] >= $job['total']) {
            echo "Job {$job['id']} completed.\n";
            $job['status'] = 'completed';
            $job['completed'] = date('Y-m-d H:i:s');
            file_put_contents($jobPath, json_encode($job, JSON_PRETTY_PRINT));
            continue;
        }
        
        // Run worker
        $output = shell_exec('php ' . __DIR__ . '/api/cron.php 2>&1');
        echo $output;
        
        $cooldown = $job['config']['cooldown'] ?? 3;
        if ($cooldown > 0) sleep($cooldown);
    }
}

function cmd_list() {
    $output = shell_exec('php -r "
        \$d = json_decode(file_get_contents(\"php://stdin\"), true);
        foreach(\$d??[] as \$j) echo \$j[\"id\"].\" | \".\$j[\"name\"].\" | \".\$j[\"status\"].\" | \".\$j[\"sent\"].\"/\".\$j[\"total\"].\" | \".\$j[\"created\"].PHP_EOL;
    " <<< $(curl -s http://localhost/blastwa/api/queue.php?list 2>/dev/null) 2>/dev/null');
    
    // Fallback: read files directly
    $files = glob(__DIR__ . '/blasts/job_*.json');
    if (empty($files)) {
        echo "No jobs found.\n";
        return;
    }
    
    printf("%-25s | %-30s | %-12s | %s\n", "ID", "Name", "Status", "Progress");
    echo str_repeat('-', 90) . "\n";
    
    foreach ($files as $f) {
        $d = json_decode(file_get_contents($f), true);
        if (!$d) continue;
        printf("%-25s | %-30s | %-12s | %d/%d (%d%%)\n",
            substr($d['id'], 0, 25),
            substr($d['name'], 0, 28),
            $d['status'],
            $d['sent'] + $d['failed'],
            $d['total'],
            $d['total'] > 0 ? round(($d['sent']+$d['failed'])/$d['total']*100) : 0
        );
    }
}

function cmd_status() {
    $id = $argv[2] ?? '';
    if (empty($id)) die("Usage: php job.php status <job-id>\n");
    
    $path = __DIR__ . '/blasts/job_' . $id . '.json';
    if (!file_exists($path)) die("Job not found: $id\n");
    
    $d = json_decode(file_get_contents($path), true);
    if (!$d) die("Invalid job file\n");
    
    echo "ID:       {$d['id']}\n";
    echo "Name:     {$d['name']}\n";
    echo "Status:   {$d['status']}\n";
    echo "Created:  {$d['created']}\n";
    echo "Message:  {$d['message']}\n";
    echo "Config:   limit={$d['config']['limitPerMin']}/min, cooldown={$d['config']['cooldown']}s\n";
    echo "Total:    {$d['total']}\n";
    echo "Sent:     {$d['sent']}\n";
    echo "Failed:   {$d['failed']}\n";
    echo "Progress: " . ($d['total'] > 0 ? round(($d['sent']+$d['failed'])/$d['total']*100) : 0) . "%\n";
    
    if (!empty($d['results'])) {
        echo "\nLatest results:\n";
        $latest = array_slice($d['results'], -10);
        foreach ($latest as $r) {
            $icon = $r['status'] === 'ok' ? '✓' : '✗';
            echo "  $icon {$r['phone']} — {$r['response']}\n";
        }
        if (count($d['results']) > 10) echo "  ...and " . (count($d['results']) - 10) . " more\n";
    }
}

function cmd_cancel() {
    $id = $argv[2] ?? '';
    if (empty($id)) die("Usage: php job.php cancel <job-id>\n");
    
    $path = __DIR__ . '/blasts/job_' . $id . '.json';
    if (!file_exists($path)) die("Job not found: $id\n");
    
    $d = json_decode(file_get_contents($path), true);
    $d['status'] = 'cancelled';
    $d['completed'] = date('Y-m-d H:i:s');
    file_put_contents($path, json_encode($d, JSON_PRETTY_PRINT));
    
    echo "✅ Job $id cancelled.\n";
}
