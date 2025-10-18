<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Expense;
use App\Repositories\BudgetRepository;
use App\Repositories\CategoryRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BudgetService
{
    protected BudgetRepository $budgetRepository;
    protected CategoryRepository $categoryRepository;

    public function __construct(
        BudgetRepository $budgetRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->budgetRepository = $budgetRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllBudgets(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->budgetRepository->getAllForUser($userId, $filters, $perPage);
    }

    public function getBudgetById(int $id, int $userId): ?Budget
    {
        return $this->budgetRepository->findByIdForUser($id, $userId);
    }

    public function createBudget(array $data, int $userId): Budget
    {
        $data['user_id'] = $userId;

        // Validate category ownership if provided
        if (!empty($data['category_id'])) {
            $category = $this->categoryRepository->findForUser($data['category_id'], $userId);
            if (!$category) {
                throw ValidationException::withMessages([
                    'category_id' => ['The selected category is invalid or does not belong to you.']
                ]);
            }
        }

        // Validate date ranges for custom periods
        if ($data['period'] === Budget::PERIOD_CUSTOM) {
            if (empty($data['start_date']) || empty($data['end_date'])) {
                throw ValidationException::withMessages([
                    'start_date' => ['Start date is required for custom periods.'],
                    'end_date' => ['End date is required for custom periods.']
                ]);
            }

            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);

            if ($endDate->lte($startDate)) {
                throw ValidationException::withMessages([
                    'end_date' => ['End date must be after start date.']
                ]);
            }
        }

        // Check for overlapping budgets with same category
        if (!empty($data['category_id'])) {
            $overlapping = $this->checkOverlappingBudgets($userId, $data);
            if ($overlapping) {
                throw ValidationException::withMessages([
                    'period' => ['A budget for this category already exists in the selected period.']
                ]);
            }
        }

        return $this->budgetRepository->create($data);
    }

    public function updateBudget(int $id, array $data, int $userId): bool
    {
        $budget = $this->getBudgetById($id, $userId);
        
        if (!$budget) {
            return false;
        }

        // Validate category ownership if being updated
        if (!empty($data['category_id'])) {
            $category = $this->categoryRepository->findForUser($data['category_id'], $userId);
            if (!$category) {
                throw ValidationException::withMessages([
                    'category_id' => ['The selected category is invalid or does not belong to you.']
                ]);
            }
        }

        // Validate custom period dates
        if (isset($data['period']) && $data['period'] === Budget::PERIOD_CUSTOM) {
            if (empty($data['start_date']) || empty($data['end_date'])) {
                throw ValidationException::withMessages([
                    'start_date' => ['Start date is required for custom periods.'],
                    'end_date' => ['End date is required for custom periods.']
                ]);
            }
        }

        return $this->budgetRepository->update($id, $userId, $data);
    }

    public function deleteBudget(int $id, int $userId): bool
    {
        return $this->budgetRepository->delete($id, $userId);
    }

    public function getCurrentBudgets(int $userId): Collection
    {
        return $this->budgetRepository->getCurrentForUser($userId);
    }

    public function getBudgetsByPeriod(int $userId, string $period): Collection
    {
        return $this->budgetRepository->getByPeriodForUser($userId, $period);
    }

    public function getBudgetSummary(int $userId): array
    {
        return $this->budgetRepository->getSummary($userId);
    }

    public function getBudgetAlerts(int $userId): array
    {
        return $this->budgetRepository->getAlerts($userId);
    }

    public function getBudgetAnalytics(int $userId, string $period = 'monthly'): array
    {
        return $this->budgetRepository->getAnalytics($userId, $period);
    }

    public function searchBudgets(int $userId, array $filters): Collection
    {
        return $this->budgetRepository->searchBudgets($userId, $filters);
    }

    public function getBudgetsForCategory(int $userId, int $categoryId): Collection
    {
        return $this->budgetRepository->getByCategoryForUser($userId, $categoryId);
    }

    public function duplicateBudget(int $budgetId, int $userId, array $overrides = []): ?Budget
    {
        return $this->budgetRepository->duplicate($budgetId, $userId, $overrides);
    }

    public function createDefaultBudgets(int $userId): array
    {
        // Get user's categories to match with defaults
        $categories = $this->categoryRepository->getUserCategories($userId)->toArray();
        
        return $this->budgetRepository->createDefaults($userId, $categories);
    }

    /**
     * Check for overlapping budgets
     */
    private function checkOverlappingBudgets(int $userId, array $budgetData): bool
    {
        if (empty($budgetData['category_id'])) {
            return false; // General budgets can overlap
        }

        $dateRange = Budget::calculateDateRange(
            $budgetData['period'], 
            $budgetData['start_date'] ?? null
        );

        $overlapping = Budget::where('user_id', $userId)
            ->where('category_id', $budgetData['category_id'])
            ->where('is_active', true)
            ->where(function ($query) use ($dateRange) {
                $query->whereBetween('start_date', [$dateRange['start_date'], $dateRange['end_date']])
                      ->orWhereBetween('end_date', [$dateRange['start_date'], $dateRange['end_date']])
                      ->orWhere(function ($q) use ($dateRange) {
                          $q->where('start_date', '<=', $dateRange['start_date'])
                            ->where('end_date', '>=', $dateRange['end_date']);
                      });
            })
            ->exists();

        return $overlapping;
    }
}