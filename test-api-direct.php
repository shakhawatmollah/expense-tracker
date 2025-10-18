<?php
// Direct test of budget API endpoints
require_once 'backend/vendor/autoload.php';

// Create a simple PHP server test
$baseUrl = 'http://127.0.0.1:8000';

// Test login first
$loginData = json_encode([
    'email' => 'admin@example.com',
    'password' => 'admin123'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'content' => $loginData
    ]
]);

echo "Testing login endpoint...\n";
$loginUrl = $baseUrl . '/api/auth/login';

// Check if server is running by testing a simple request
$testContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Accept: text/html\r\n",
        'timeout' => 5
    ]
]);

echo "Checking if server is running on $baseUrl...\n";
$testResponse = @file_get_contents($baseUrl, false, $testContext);
if ($testResponse === false) {
    echo "❌ Server is not running on $baseUrl\n";
    echo "Please start the Laravel server first:\n";
    echo "cd backend && php artisan serve\n";
    exit(1);
} else {
    echo "✅ Server is responding\n";
}

// Now test login
echo "Testing login...\n";
$loginResponse = @file_get_contents($loginUrl, false, $context);
if ($loginResponse === false) {
    echo "❌ Login failed - server error\n";
    $error = error_get_last();
    echo "Error: " . $error['message'] . "\n";
    exit(1);
}

$loginData = json_decode($loginResponse, true);
if (!isset($loginData['access_token'])) {
    echo "❌ Login failed - no access token\n";
    echo "Response: $loginResponse\n";
    exit(1);
}

$token = $loginData['access_token'];
echo "✅ Login successful, token: " . substr($token, 0, 20) . "...\n";

// Test budget endpoints
$endpoints = [
    '/api/budgets',
    '/api/budgets/summary',
    '/api/budgets/alerts'
];

foreach ($endpoints as $endpoint) {
    echo "\nTesting $endpoint...\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\nAuthorization: Bearer $token\r\n"
        ]
    ]);
    
    $url = $baseUrl . $endpoint;
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Request failed\n";
        $error = error_get_last();
        echo "Error: " . $error['message'] . "\n";
    } else {
        $data = json_decode($response, true);
        if ($data === null) {
            echo "❌ Invalid JSON response\n";
            echo "Response: " . substr($response, 0, 200) . "...\n";
        } else {
            echo "✅ Success: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        }
    }
}