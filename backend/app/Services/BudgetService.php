<?php

namespace App\Services;

use App\Exceptions\BudgetDatabaseException;
use App\Exceptions\BudgetNotFoundException;
use App\Exceptions\BudgetValidationException;
use App\Models\Budget;
use App\Repositories\BudgetRepository;
use App\Repositories\CategoryRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $budget = $this->budgetRepository->findByIdForUser($id, $userId);

        if (! $budget) {
            throw new BudgetNotFoundException($id, ['user_id' => $userId]);
        }

        return $budget;
    }

    public function createBudget(array $data, int $userId): Budget
    {
        try {
            $data['user_id'] = $userId;

            // Validate category ownership if provided
            if (! empty($data['category_id'])) {
                $category = $this->categoryRepository->findForUser($data['category_id'], $userId);
                if (! $category) {
                    throw new BudgetValidationException(
                        'Invalid category',
                        ['category_id' => ['The selected category is invalid or does not belong to you.']],
                        ['user_id' => $userId, 'category_id' => $data['category_id']]
                    );
                }
            }

            // Validate date ranges for custom periods
            if ($data['period'] === Budget::PERIOD_CUSTOM) {
                if (empty($data['start_date']) || empty($data['end_date'])) {
                    throw new BudgetValidationException(
                        'Invalid custom period dates',
                        [
                            'start_date' => ['Start date is required for custom periods.'],
                            'end_date' => ['End date is required for custom periods.'],
                        ],
                        ['user_id' => $userId]
                    );
                }

                $startDate = Carbon::parse($data['start_date']);
                $endDate = Carbon::parse($data['end_date']);

                if ($endDate->lte($startDate)) {
                    throw new BudgetValidationException(
                        'Invalid date range',
                        ['end_date' => ['End date must be after start date.']],
                        ['user_id' => $userId]
                    );
                }
            }

            // Check for overlapping budgets with same category
            if (! empty($data['category_id'])) {
                $overlapping = $this->checkOverlappingBudgets($userId, $data);
                if ($overlapping) {
                    throw new BudgetValidationException(
                        'Overlapping budget exists',
                        ['period' => ['A budget for this category already exists in the selected period.']],
                        ['user_id' => $userId, 'category_id' => $data['category_id']]
                    );
                }
            }

            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($data, $userId) {
                $budget = $this->budgetRepository->create($data);

                Log::info('Budget created successfully', [
                    'budget_id' => $budget->id,
                    'user_id' => $userId,
                    'amount' => $data['amount'] ?? null,
                    'period' => $data['period'] ?? null,
                ]);

                return $budget;
            });

        } catch (BudgetValidationException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to create budget', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new BudgetDatabaseException(
                'budget creation',
                $e->getMessage(),
                ['user_id' => $userId]
            );
        }
    }

    public function updateBudget(int $id, array $data, int $userId): bool
    {
        try {
            $budget = $this->getBudgetById($id, $userId);

            // Validate category ownership if being updated
            if (! empty($data['category_id'])) {
                $category = $this->categoryRepository->findForUser($data['category_id'], $userId);
                if (! $category) {
                    throw new BudgetValidationException(
                        'Invalid category',
                        ['category_id' => ['The selected category is invalid or does not belong to you.']],
                        ['user_id' => $userId, 'budget_id' => $id]
                    );
                }
            }

            // Validate custom period dates
            if (isset($data['period']) && $data['period'] === Budget::PERIOD_CUSTOM) {
                if (empty($data['start_date']) || empty($data['end_date'])) {
                    throw new BudgetValidationException(
                        'Invalid custom period dates',
                        [
                            'start_date' => ['Start date is required for custom periods.'],
                            'end_date' => ['End date is required for custom periods.'],
                        ],
                        ['user_id' => $userId, 'budget_id' => $id]
                    );
                }
            }

            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($id, $userId, $data) {
                $updated = $this->budgetRepository->update($id, $userId, $data);

                if ($updated) {
                    Log::info('Budget updated successfully', [
                        'budget_id' => $id,
                        'user_id' => $userId,
                        'updated_fields' => array_keys($data),
                    ]);
                }

                return $updated;
            });

        } catch (BudgetNotFoundException | BudgetValidationException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to update budget', [
                'budget_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new BudgetDatabaseException(
                'budget update',
                $e->getMessage(),
                ['budget_id' => $id, 'user_id' => $userId]
            );
        }
    }

    public function deleteBudget(int $id, int $userId): bool
    {
        try {
            $budget = $this->getBudgetById($id, $userId);

            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($id, $userId) {
                $deleted = $this->budgetRepository->delete($id, $userId);

                if ($deleted) {
                    Log::info('Budget deleted successfully', [
                        'budget_id' => $id,
                        'user_id' => $userId,
                    ]);
                }

                return $deleted;
            });

        } catch (BudgetNotFoundException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to delete budget', [
                'budget_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new BudgetDatabaseException(
                'budget deletion',
                $e->getMessage(),
                ['budget_id' => $id, 'user_id' => $userId]
            );
        }
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

    /**
     * Calculate budget usage percentage
     */
    public function calculateUsagePercentage(Budget $budget, float $spentAmount): float
    {
        if ($budget->amount <= 0) {
            return 0.0;
        }

        return round(($spentAmount / $budget->amount) * 100, 2);
    }

    /**
     * Determine budget status based on usage percentage
     */
    public function getBudgetStatus(Budget $budget, float $spentAmount): string
    {
        $percentage = $this->calculateUsagePercentage($budget, $spentAmount);
        $thresholds = $budget->alert_thresholds ?? ['warning' => 75, 'danger' => 90];

        if ($percentage >= 100) {
            return 'exceeded';
        } elseif ($percentage >= ($thresholds['danger'] ?? 90)) {
            return 'danger';
        } elseif ($percentage >= ($thresholds['warning'] ?? 75)) {
            return 'warning';
        }

        return 'safe';
    }

    /**
     * Get remaining budget amount
     */
    public function getRemainingAmount(Budget $budget, float $spentAmount): float
    {
        return round($budget->amount - $spentAmount, 2);
    }

    /**
     * Check if budget needs alert notification
     */
    public function needsAlert(Budget $budget, float $spentAmount): bool
    {
        $thresholds = $budget->alert_thresholds ?? ['warning' => 75, 'danger' => 90];
        $percentage = $this->calculateUsagePercentage($budget, $spentAmount);

        // Alert if we've reached warning threshold or higher
        return $percentage >= ($thresholds['warning'] ?? 75);
    }
}
