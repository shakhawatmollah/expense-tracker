<?php

// Test the analytics API endpoints
echo "🔍 Testing Analytics API Endpoints...\n\n";

// Test login first to get a token
$loginData = [
    'email' => 'admin@example.com',
    'password' => 'password123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "🔐 Login Response (HTTP {$loginHttpCode}):\n";
if ($loginHttpCode === 200) {
    $loginData = json_decode($loginResponse, true);
    if (isset($loginData['data']['token'])) {
        $token = $loginData['data']['token'];
        echo "✅ Login successful, token obtained\n\n";
        
        // Test financial health endpoint
        echo "📊 Testing Financial Health endpoint...\n";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/analytics/financial-health?period=monthly');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ]);
        
        $healthResponse = curl_exec($ch);
        $healthHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Financial Health Response (HTTP {$healthHttpCode}):\n";
        if ($healthHttpCode === 200) {
            $healthData = json_decode($healthResponse, true);
            echo "✅ Financial Health API working!\n";
            echo "Overall Score: " . ($healthData['data']['current']['overall_score'] ?? 'N/A') . "\n";
            echo "Budget Adherence: " . ($healthData['data']['current']['budget_adherence_score'] ?? 'N/A') . "\n";
            echo "Savings Rate: " . ($healthData['data']['current']['savings_rate_score'] ?? 'N/A') . "\n";
        } else {
            echo "❌ Financial Health API Error:\n";
            echo $healthResponse . "\n";
        }
        
        echo "\n📈 Testing Analytics Dashboard endpoint...\n";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/analytics/dashboard?period=monthly');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ]);
        
        $dashboardResponse = curl_exec($ch);
        $dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Dashboard Response (HTTP {$dashboardHttpCode}):\n";
        if ($dashboardHttpCode === 200) {
            echo "✅ Analytics Dashboard API working!\n";
        } else {
            echo "❌ Dashboard API Error:\n";
            echo $dashboardResponse . "\n";
        }
        
    } else {
        echo "❌ Login failed - no token in response\n";
        echo $loginResponse . "\n";
    }
} else {
    echo "❌ Login failed (HTTP {$loginHttpCode})\n";
    echo $loginResponse . "\n";
}