<?php

// Simple PHP test for budget routes
echo "=== Testing Budget API Routes ===\n";

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

echo "1. Testing login...\n";
$loginResponse = file_get_contents('http://127.0.0.1:8000/api/auth/login', false, $context);

if ($loginResponse === false) {
    echo "❌ Login failed\n";
    exit(1);
}

$loginData = json_decode($loginResponse, true);
if (!isset($loginData['token'])) {
    echo "❌ No token received\n";
    echo "Response: $loginResponse\n";
    exit(1);
}

echo "✅ Login successful\n";
$token = $loginData['token'];

// Test budget API
echo "\n2. Testing budget API...\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\nAuthorization: Bearer $token\r\n"
    ]
]);

$budgetResponse = file_get_contents('http://127.0.0.1:8000/api/budgets', false, $context);

if ($budgetResponse === false) {
    echo "❌ Budget API failed\n";
    exit(1);
}

$budgetData = json_decode($budgetResponse, true);
echo "✅ Budget API successful\n";
echo "Budgets found: " . count($budgetData['data']) . "\n";
echo "Total records: " . $budgetData['meta']['total'] . "\n";

// Test budget summary
echo "\n3. Testing budget summary...\n";
$summaryResponse = file_get_contents('http://127.0.0.1:8000/api/budgets/summary', false, $context);

if ($summaryResponse === false) {
    echo "❌ Budget Summary API failed\n";
} else {
    echo "✅ Budget Summary API successful\n";
}

// Test budget alerts
echo "\n4. Testing budget alerts...\n";
$alertsResponse = file_get_contents('http://127.0.0.1:8000/api/budgets/alerts', false, $context);

if ($alertsResponse === false) {
    echo "❌ Budget Alerts API failed\n";
} else {
    echo "✅ Budget Alerts API successful\n";
}

// Test analytics recommendations
echo "\n5. Testing analytics recommendations...\n";
$recommendationsResponse = file_get_contents('http://127.0.0.1:8000/api/analytics/recommendations', false, $context);

if ($recommendationsResponse === false) {
    echo "❌ Analytics Recommendations API failed\n";
} else {
    echo "✅ Analytics Recommendations API successful\n";
}

echo "\n=== Test Complete ===\n";