<?php

// Simple test script to validate budget API endpoints
// Run this with: php test-budget-endpoints.php

$baseUrl = 'http://127.0.0.1:8000/api';

echo "Testing Budget API Endpoints\n";
echo "============================\n";

// Test login first
echo "1. Testing login...\n";
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

$loginResponse = file_get_contents("$baseUrl/auth/login", false, $loginContext);
if ($loginResponse === false) {
    echo "❌ Login failed\n";
    exit(1);
}

$loginJson = json_decode($loginResponse, true);
if (!isset($loginJson['token'])) {
    echo "❌ No token in login response\n";
    exit(1);
}

$token = $loginJson['token'];
echo "✅ Login successful, token: " . substr($token, 0, 20) . "...\n";

// Test budget alerts endpoint
echo "\n2. Testing /api/budgets/alerts...\n";
$alertsContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\n"
    ]
]);

$alertsResponse = file_get_contents("$baseUrl/budgets/alerts", false, $alertsContext);
if ($alertsResponse === false) {
    echo "❌ Alerts endpoint failed\n";
    echo "HTTP Error: " . error_get_last()['message'] . "\n";
} else {
    echo "✅ Alerts endpoint successful\n";
    echo "Response: $alertsResponse\n";
}

// Test budget summary endpoint
echo "\n3. Testing /api/budgets/summary...\n";
$summaryContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $token\r\nAccept: application/json\r\n"
    ]
]);

$summaryResponse = file_get_contents("$baseUrl/budgets/summary", false, $summaryContext);
if ($summaryResponse === false) {
    echo "❌ Summary endpoint failed\n";
    echo "HTTP Error: " . error_get_last()['message'] . "\n";
} else {
    echo "✅ Summary endpoint successful\n";
    echo "Response: $summaryResponse\n";
}

echo "\nTest completed.\n";