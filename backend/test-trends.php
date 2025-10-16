<?php

// Simple test script to verify trends data
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use App\Services\DashboardService;
use App\Repositories\ExpenseRepository;
use App\Repositories\CategoryRepository;

// Get first user
$user = User::first();

if (!$user) {
    echo "No users found\n";
    exit;
}

echo "Testing trends for user: {$user->email}\n";

// Create service
$expenseRepo = new ExpenseRepository();
$categoryRepo = new CategoryRepository();
$dashboardService = new DashboardService($expenseRepo, $categoryRepo);

// Get trends
$trends = $dashboardService->getMonthlySpendingTrends($user->id, 6);

echo "Trends data:\n";
foreach ($trends as $trend) {
    echo sprintf(
        "Month: %s | Total: $%.2f | Count: %d | Date: %s\n",
        $trend['label'] ?? $trend['month'],
        $trend['total'],
        $trend['count'],
        $trend['date']
    );
}