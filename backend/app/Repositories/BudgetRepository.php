<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BudgetRepository
{
    protected Budget $model;

    public function __construct(Budget $model)
    {
        $this->model = $model;
    }

    /**
     * Find budget by ID for a specific user
     */
    public function findByIdForUser(int $id, int $userId): ?Budget
    {
        return $this->model->with(['category', 'user'])
                         ->where('id', $id)
                         ->where('user_id', $userId)
                         ->first();
    }

    /**
     * Get all budgets for a user with pagination
     */
    public function getAllForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['category', 'user'])
                           ->where('user_id', $userId);

        // Apply filters
        if (!empty($filters['period'])) {
            $query->where('period', $filters['period']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'current':
                    $query->current();
                    break;
                case 'over_budget':
                    $query->overBudget();
                    break;
                case 'active':
                    $query->active();
                    break;
            }
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get current active budgets for a user
     */
    public function getCurrentForUser(int $userId): Collection
    {
        return $this->model->with(['category', 'user'])
                         ->where('user_id', $userId)
                         ->current()
                         ->get();
    }

    /**
     * Get budgets by period for a user
     */
    public function getByPeriodForUser(int $userId, string $period): Collection
    {
        return $this->model->with(['category', 'user'])
                         ->where('user_id', $userId)
                         ->forPeriod($period)
                         ->orderBy('start_date', 'desc')
                         ->get();
    }

    /**
     * Get budgets by category for a user
     */
    public function getByCategoryForUser(int $userId, int $categoryId): Collection
    {
        return $this->model->with(['category', 'user'])
                         ->where('user_id', $userId)
                         ->byCategory($categoryId)
                         ->orderBy('start_date', 'desc')
                         ->get();
    }

    /**
     * Create a new budget
     */
    public function create(array $data): Budget
    {
        // Set default alert thresholds if not provided
        if (!isset($data['alert_thresholds'])) {
            $data['alert_thresholds'] = Budget::DEFAULT_ALERT_THRESHOLDS;
        }

        // Calculate dates for non-custom periods
        if ($data['period'] !== Budget::PERIOD_CUSTOM) {
            $dateRange = Budget::calculateDateRange($data['period'], $data['start_date'] ?? null);
            $data['start_date'] = $dateRange['start_date'];
            $data['end_date'] = $dateRange['end_date'];
        }

        return $this->model->create($data);
    }

    /**
     * Update a budget
     */
    public function update(int $id, int $userId, array $data): bool
    {
        $budget = $this->findByIdForUser($id, $userId);
        
        if (!$budget) {
            return false;
        }

        // Recalculate dates if period changed and not custom
        if (isset($data['period']) && $data['period'] !== Budget::PERIOD_CUSTOM) {
            $dateRange = Budget::calculateDateRange($data['period'], $data['start_date'] ?? $budget->start_date);
            $data['start_date'] = $dateRange['start_date'];
            $data['end_date'] = $dateRange['end_date'];
        }

        return $budget->update($data);
    }

    /**
     * Delete a budget
     */
    public function delete(int $id, int $userId): bool
    {
        $budget = $this->findByIdForUser($id, $userId);
        
        if (!$budget) {
            return false;
        }

        return $budget->delete();
    }

    /**
     * Get budget summary with analytics
     */
    public function getSummary(int $userId): array
    {
        // Simple stub implementation to prevent 404 errors
        $currentBudgets = $this->getCurrentForUser($userId);
        
        return [
            'total_budgets' => $currentBudgets->count(),
            'total_budgeted' => $currentBudgets->sum('amount'),
            'total_spent' => 0, // Simplified for now
            'total_remaining' => $currentBudgets->sum('amount'),
            'overall_usage_percentage' => 0,
            'over_budget_count' => 0,
            'alerts_count' => 0,
            'budgets' => [],
        ];
    }

    /**
     * Get budget alerts for a user
     */
    public function getAlerts(int $userId): array
    {
        // Simple stub implementation to prevent 404 errors
        return [];
    }

    /**
     * Duplicate a budget
     */
    public function duplicate(int $id, int $userId, array $overrides = []): ?Budget
    {
        $originalBudget = $this->findByIdForUser($id, $userId);
        
        if (!$originalBudget) {
            return null;
        }

        $data = $originalBudget->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);

        // Apply overrides
        $data = array_merge($data, $overrides);

        // Set new name if not overridden
        if (!isset($overrides['name'])) {
            $data['name'] = $originalBudget->name . ' (Copy)';
        }

        // Recalculate dates for new period
        if ($data['period'] !== Budget::PERIOD_CUSTOM) {
            $dateRange = Budget::calculateDateRange($data['period']);
            $data['start_date'] = $dateRange['start_date'];
            $data['end_date'] = $dateRange['end_date'];
        }

        return $this->create($data);
    }

    /**
     * Get budget analytics data
     */
    public function getAnalytics(int $userId, string $period = 'monthly'): array
    {
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfYear();

        if ($period === 'monthly') {
            $startDate = Carbon::now()->startOfYear();
        } else {
            $startDate = Carbon::now()->subYears(2);
        }

        $budgets = $this->model->with(['category', 'user'])
                             ->where('user_id', $userId)
                             ->whereBetween('start_date', [$startDate, $endDate])
                             ->get();

        $analytics = [
            'budget_trends' => [],
            'category_breakdown' => [],
        ];

        // Group budgets by period
        $groupedBudgets = $budgets->groupBy(function ($budget) use ($period) {
            if ($period === 'monthly') {
                return $budget->start_date->format('Y-m');
            }
            return $budget->start_date->format('Y');
        });

        foreach ($groupedBudgets as $periodKey => $periodBudgets) {
            $totalBudget = $periodBudgets->sum('amount');
            $totalSpent = 0;

            foreach ($periodBudgets as $budget) {
                $totalSpent += $budget->spent_amount;
            }

            $analytics['budget_trends'][] = [
                'period' => $periodKey,
                'budgeted' => $totalBudget,
                'spent' => $totalSpent,
                'saved' => max(0, $totalBudget - $totalSpent),
                'usage_percentage' => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 2) : 0,
            ];
        }

        // Category breakdown
        $categoryBudgets = $budgets->whereNotNull('category_id')->groupBy('category_id');
        foreach ($categoryBudgets as $categoryId => $categoryBudgetList) {
            $category = $categoryBudgetList->first()->category;
            $totalBudget = $categoryBudgetList->sum('amount');
            $totalSpent = 0;

            foreach ($categoryBudgetList as $budget) {
                $totalSpent += $budget->spent_amount;
            }

            $analytics['category_breakdown'][] = [
                'category_id' => $categoryId,
                'category_name' => $category->name,
                'category_color' => $category->color,
                'budgeted' => $totalBudget,
                'spent' => $totalSpent,
                'usage_percentage' => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 2) : 0,
            ];
        }

        return $analytics;
    }

    /**
     * Search budgets with filters
     */
    public function searchBudgets(int $userId, array $filters): Collection
    {
        $query = $this->model->with(['category', 'user'])
                           ->where('user_id', $userId);

        if (!empty($filters['period'])) {
            $query->forPeriod($filters['period']);
        }

        if (!empty($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'over_budget':
                    $query->overBudget();
                    break;
                case 'current':
                    $query->current();
                    break;
                case 'active':
                    $query->active();
                    break;
            }
        }

        if (!empty($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (!empty($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', '%' . $filters['name'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create default budgets for common categories
     */
    public function createDefaults(int $userId, array $categories = []): array
    {
        $defaultBudgets = [
            ['name' => 'Monthly Food Budget', 'amount' => 500, 'period' => Budget::PERIOD_MONTHLY],
            ['name' => 'Monthly Transport Budget', 'amount' => 200, 'period' => Budget::PERIOD_MONTHLY],
            ['name' => 'Monthly Entertainment Budget', 'amount' => 150, 'period' => Budget::PERIOD_MONTHLY],
            ['name' => 'Yearly Vacation Budget', 'amount' => 2000, 'period' => Budget::PERIOD_YEARLY],
        ];

        $createdBudgets = [];

        foreach ($defaultBudgets as $budgetData) {
            $budgetData['user_id'] = $userId;
            
            // Try to match with user's categories
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    if (stripos($budgetData['name'], $category['name']) !== false) {
                        $budgetData['category_id'] = $category['id'];
                        break;
                    }
                }
            }

            $createdBudgets[] = $this->create($budgetData);
        }

        return $createdBudgets;
    }

    /**
     * Generate alert message for budget
     */
    private function generateAlertMessage(Budget $budget): string
    {
        $percentage = $budget->usage_percentage;
        $categoryName = $budget->category?->name ?? 'All Categories';
        
        if ($budget->is_over_budget) {
            $overAmount = $budget->spent_amount - $budget->amount;
            return "Budget '{$budget->name}' for {$categoryName} has exceeded by $" . number_format($overAmount, 2);
        }
        
        if ($percentage >= 80) {
            return "Budget '{$budget->name}' for {$categoryName} is at {$percentage}% usage";
        }
        
        return "Budget '{$budget->name}' for {$categoryName} needs attention ({$percentage}% used)";
    }
}