<?php
// Direct test of individual components

// Load all dependencies
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Expense;
use App\Models\Budget;
use App\Models\FinancialHealthScore;
use App\Services\AnalyticsService;
use Carbon\Carbon;

// Test 1: Test database connection
echo "🔗 Testing database connection...\n";
try {
    // Bootstrap Laravel app
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "✅ Laravel bootstrapped successfully\n";
    
    // Test database connection
    $result = DB::select('SELECT 1 as test');
    echo "✅ Database connection successful\n";
    
    // Test User model
    $user = User::find(3);
    if ($user) {
        echo "✅ User found: {$user->email}\n";
    } else {
        echo "❌ User not found\n";
        exit(1);
    }
    
    // Test Expense model query
    $expenses = Expense::where('user_id', $user->id)->count();
    echo "✅ Found {$expenses} expenses for user\n";
    
    // Test Budget model query
    $budgets = Budget::where('user_id', $user->id)->count();
    echo "✅ Found {$budgets} budgets for user\n";
    
    // Test FinancialHealthScore model
    $healthScores = FinancialHealthScore::where('user_id', $user->id)->count();
    echo "✅ Found {$healthScores} existing health scores for user\n";
    
    // Test individual calculation steps
    $startDate = Carbon::now()->startOfMonth();
    echo "✅ Start date calculated: {$startDate}\n";
    
    $totalExpenses = Expense::where('user_id', $user->id)
        ->where('date', '>=', $startDate)
        ->sum('amount');
    echo "✅ Total expenses: {$totalExpenses}\n";
    
    $budgetList = Budget::where('user_id', $user->id)
        ->where('start_date', '>=', $startDate)
        ->get();
    echo "✅ Budget query successful, found: {$budgetList->count()} budgets\n";
    
    $totalBudget = $budgetList->sum('amount');
    echo "✅ Total budget: {$totalBudget}\n";
    
    // Test AnalyticsService instantiation
    $service = new AnalyticsService();
    echo "✅ AnalyticsService instantiated\n";
    
    // Try the actual calculation
    echo "📊 Attempting financial health calculation...\n";
    $result = $service->calculateFinancialHealth($user, 'monthly');
    echo "✅ SUCCESS! Financial health calculated\n";
    echo "Overall score: {$result['overall_score']}\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    if ($e->getPrevious()) {
        echo "Previous: " . $e->getPrevious()->getMessage() . "\n";
    }
}