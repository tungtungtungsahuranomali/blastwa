<?php
/**
 * API: Send single WhatsApp message
 * Endpoint for AJAX calls from dashboard
 */

header('Content-Type: application/json');

$apiUrl = "http://202.8.28.198:3000/chat/send/text";
$token  = "takeoff";

$phone   = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Phone and message required']);
    exit;
}

// Clean phone number
$phone = preg_replace('/[^0-9]/', '', $phone);

$payload = json_encode([
    "Phone" => $phone,
    "Body"  => $message
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
    echo json_encode(['success' => false, 'error' => $error]);
} elseif ($httpCode >= 200 && $httpCode < 300) {
    $decoded = json_decode($response, true);
    echo json_encode([
        'success'   => true,
        'http_code' => $httpCode,
        'response'  => $decoded ?: $response,
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'http_code' => $httpCode,
        'response'  => $response,
        'error'     => "HTTP $httpCode",
    ]);
}
