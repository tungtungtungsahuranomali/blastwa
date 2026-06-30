<?php
/**
 * Clean phone numbers from listnomordiblast.txt
 * Output: contacts.json (deduplicated, normalized to 62xx format)
 */

$input = __DIR__ . '/listnomordiblast.txt';
$output = __DIR__ . '/contacts.json';

$raw = file_get_contents($input);

// Extract all number-like patterns (including with dashes, spaces, +)
preg_match_all('/\+?\d[\d\s\-\(\)\.\/]+/s', $raw, $matches);

$all = [];
foreach ($matches[0] as $chunk) {
    // Split on /, whitespace runs that aren't part of number grouping
    $parts = preg_split('/[\s]*\/[\s]*|[\s]{2,}/', $chunk);
    foreach ($parts as $part) {
        $part = trim($part);
        // Remove any non-digit characters except leading +
        $part = preg_replace('/[^0-9\+]/', '', $part);
        if (empty($part)) continue;
        $all[] = $part;
    }
}

// Normalize
$normalized = [];
foreach ($all as $num) {
    // +62... → 62...
    if (str_starts_with($num, '+62')) {
        $num = '62' . substr($num, 3);
    }
    // 0xxx → 62xxx
    elseif (str_starts_with($num, '0')) {
        $num = '62' . substr($num, 1);
    }
    // 628xx or 63xx (Philippine) → keep as is
    // If too short (< 10 digits), skip
    // Remove duplicates
    if (strlen($num) >= 10 && strlen($num) <= 15) {
        $normalized[$num] = true;
    }
}

$numbers = array_keys($normalized);
sort($numbers);

file_put_contents($output, json_encode($numbers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Total: " . count($numbers) . " numbers cleaned\n";
echo "Saved to: contacts.json\n";
