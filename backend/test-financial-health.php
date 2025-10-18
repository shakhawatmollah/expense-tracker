<?php
// Simple test for financial health calculation
require_once 'vendor/autoload.php';

// Test the financial health calculation directly
use App\Services\AnalyticsService;
use App\Models\User;

try {
    // Get a user (assuming user ID 3 exists based on previous output)
    $user = User::find(3);
    if (!$user) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    echo "✅ User found: {$user->email}\n";
    
    // Create analytics service
    $analyticsService = new AnalyticsService();
    
    echo "📊 Testing financial health calculation...\n";
    
    // Try to calculate financial health
    $result = $analyticsService->calculateFinancialHealth($user, 'monthly');
    
    echo "✅ Financial health calculation successful!\n";
    echo "Overall Score: {$result['overall_score']}\n";
    echo "Budget Adherence: {$result['budget_adherence_score']}\n";
    echo "Spending Consistency: {$result['spending_consistency_score']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}