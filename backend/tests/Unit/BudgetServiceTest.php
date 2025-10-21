<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BudgetService $budgetService;
    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->budgetService = app(BudgetService::class);
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_can_calculate_budget_usage_percentage()
    {
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 1000.00,
            'period' => 'monthly',
            'start_date' => '2025-10-01',
            'end_date' => '2025-10-31'
        ]);

        // Mock spending of $300
        $spentAmount = 300.00;
        
        $percentage = $this->budgetService->calculateUsagePercentage($budget, $spentAmount);
        
        $this->assertEquals(30.0, $percentage);
    }

    public function test_can_determine_budget_status()
    {
        $budget = Budget::factory()->create([
            'amount' => 1000.00,
            'alert_thresholds' => [
                'warning' => 75,
                'danger' => 90
            ]
        ]);

        // Test safe status (50% usage)
        $status = $this->budgetService->getBudgetStatus($budget, 500.00);
        $this->assertEquals('safe', $status);

        // Test warning status (80% usage)
        $status = $this->budgetService->getBudgetStatus($budget, 800.00);
        $this->assertEquals('warning', $status);

        // Test danger status (95% usage)
        $status = $this->budgetService->getBudgetStatus($budget, 950.00);
        $this->assertEquals('danger', $status);

        // Test exceeded status (110% usage)
        $status = $this->budgetService->getBudgetStatus($budget, 1100.00);
        $this->assertEquals('exceeded', $status);
    }

    public function test_can_get_remaining_budget()
    {
        $budget = Budget::factory()->create([
            'amount' => 1000.00
        ]);

        $remaining = $this->budgetService->getRemainingAmount($budget, 300.00);
        
        $this->assertEquals(700.00, $remaining);
    }

    public function test_can_check_if_budget_needs_alert()
    {
        $budget = Budget::factory()->create([
            'amount' => 1000.00,
            'alert_thresholds' => [
                'warning' => 75,
                'danger' => 90
            ]
        ]);

        // Should not need alert at 50%
        $needsAlert = $this->budgetService->needsAlert($budget, 500.00);
        $this->assertFalse($needsAlert);

        // Should need alert at 80% (above warning threshold)
        $needsAlert = $this->budgetService->needsAlert($budget, 800.00);
        $this->assertTrue($needsAlert);
    }
}