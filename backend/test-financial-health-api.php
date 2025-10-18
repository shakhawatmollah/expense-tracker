<?php
// Test script to identify the exact issue
$testUrl = 'http://127.0.0.1:8000/api/analytics/financial-health';

// Test login first
$loginData = json_encode([
    'email' => 'admin@example.com',
    'password' => 'admin123'
]);

$loginContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'content' => $loginData
    ]
]);

echo "🔐 Testing login...\n";
$loginResponse = @file_get_contents('http://127.0.0.1:8000/api/auth/login', false, $loginContext);

if ($loginResponse === false) {
    echo "❌ Login failed - server not responding\n";
    echo "Make sure the Laravel server is running: php artisan serve\n";
    exit(1);
}

$loginData = json_decode($loginResponse, true);
if (!isset($loginData['access_token'])) {
    echo "❌ Login failed - no access token\n";
    echo "Response: $loginResponse\n";
    exit(1);
}

$token = $loginData['access_token'];
echo "✅ Login successful\n";

// Test financial health endpoint
echo "📊 Testing financial health endpoint...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\nAuthorization: Bearer $token\r\n"
    ]
]);

$response = @file_get_contents($testUrl, false, $context);

if ($response === false) {
    echo "❌ Financial health request failed\n";
    $error = error_get_last();
    echo "Error: " . $error['message'] . "\n";
} else {
    $data = json_decode($response, true);
    if ($data === null) {
        echo "❌ Invalid JSON response\n";
        echo "Response: " . substr($response, 0, 500) . "...\n";
    } else {
        if ($data['success']) {
            echo "✅ Financial health calculation successful!\n";
            echo "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ Financial health calculation failed\n";
            echo "Error: " . ($data['error'] ?? $data['message']) . "\n";
        }
    }
}