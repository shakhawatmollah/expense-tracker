<?php

namespace App\Services;

use App\Exceptions\ExpenseDatabaseException;
use App\Exceptions\ExpenseNotFoundException;
use App\Exceptions\ExpenseValidationException;
use App\Models\Expense;
use App\Repositories\ExpenseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository
    ) {
    }

    /**
     * Get all expenses for a specific user
     */
    public function getUserExpenses(int $userId, array $filters = []): Collection
    {
        return $this->expenseRepository->getUserExpenses($userId, $filters);
    }

    /**
     * Get paginated expenses for a specific user
     */
    public function getPaginatedUserExpenses(int $userId, array $filters = []): LengthAwarePaginator
    {
        return $this->expenseRepository->getPaginatedUserExpenses($userId, $filters);
    }

    /**
     * Create a new expense
     */
    public function create(array $data): Expense
    {
        try {
            // Validate amount is positive
            if (isset($data['amount']) && $data['amount'] <= 0) {
                throw new ExpenseValidationException(
                    'Amount must be greater than zero',
                    ['amount' => ['The amount must be a positive number']],
                    ['user_id' => $data['user_id'] ?? null]
                );
            }

            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($data) {
                $expense = $this->expenseRepository->create($data);

                Log::info('Expense created successfully', [
                    'expense_id' => $expense->id,
                    'user_id' => $data['user_id'] ?? null,
                    'amount' => $data['amount'] ?? null,
                ]);

                return $expense;
            });

        } catch (ExpenseValidationException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to create expense in service', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new ExpenseDatabaseException(
                'expense creation',
                $e->getMessage(),
                ['user_id' => $data['user_id'] ?? null]
            );
        }
    }

    /**
     * Find an expense for a specific user
     */
    public function findForUser(int $expenseId, int $userId): Expense
    {
        try {
            $expense = $this->expenseRepository->findForUser($expenseId, $userId);

            if (! $expense) {
                throw new ExpenseNotFoundException($expenseId, [
                    'user_id' => $userId,
                ]);
            }

            return $expense;

        } catch (ExpenseNotFoundException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to find expense', [
                'expense_id' => $expenseId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new ExpenseDatabaseException(
                'expense retrieval',
                $e->getMessage(),
                ['expense_id' => $expenseId, 'user_id' => $userId]
            );
        }
    }

    /**
     * Update an expense
     */
    public function update(int $expenseId, int $userId, array $data): Expense
    {
        try {
            $expense = $this->findForUser($expenseId, $userId);

            // Validate amount if provided
            if (isset($data['amount']) && $data['amount'] <= 0) {
                throw new ExpenseValidationException(
                    'Amount must be greater than zero',
                    ['amount' => ['The amount must be a positive number']],
                    ['expense_id' => $expenseId, 'user_id' => $userId]
                );
            }

            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($expense, $data, $expenseId, $userId) {
                $updatedExpense = $this->expenseRepository->update($expense, $data);

                Log::info('Expense updated successfully', [
                    'expense_id' => $expenseId,
                    'user_id' => $userId,
                    'updated_fields' => array_keys($data),
                ]);

                return $updatedExpense;
            });

        } catch (ExpenseNotFoundException | ExpenseValidationException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to update expense', [
                'expense_id' => $expenseId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new ExpenseDatabaseException(
                'expense update',
                $e->getMessage(),
                ['expense_id' => $expenseId, 'user_id' => $userId]
            );
        }
    }

    /**
     * Delete an expense
     */
    public function delete(int $expenseId, int $userId): bool
    {
        try {
            $expense = $this->findForUser($expenseId, $userId);

            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($expense, $expenseId, $userId) {
                $deleted = $this->expenseRepository->delete($expense);

                if ($deleted) {
                    Log::info('Expense deleted successfully', [
                        'expense_id' => $expenseId,
                        'user_id' => $userId,
                    ]);
                }

                return $deleted;
            });

        } catch (ExpenseNotFoundException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to delete expense', [
                'expense_id' => $expenseId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new ExpenseDatabaseException(
                'expense deletion',
                $e->getMessage(),
                ['expense_id' => $expenseId, 'user_id' => $userId]
            );
        }
    }

    /**
     * Get expenses by date range
     */
    public function getByDateRange(int $userId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        return $this->expenseRepository->getByDateRange($userId, $startDate, $endDate);
    }

    /**
     * Search expenses by query
     */
    public function search(int $userId, ?string $query = null): Collection
    {
        if (empty($query)) {
            return $this->getUserExpenses($userId);
        }

        return $this->expenseRepository->search($userId, $query);
    }

    /**
     * Get total expenses for user
     */
    public function getTotalExpenses(int $userId, array $filters = []): float
    {
        return $this->expenseRepository->getTotalExpenses($userId, $filters);
    }

    /**
     * Get expenses grouped by category
     */
    public function getExpensesByCategory(int $userId, array $filters = []): array
    {
        return $this->expenseRepository->getExpensesByCategory($userId, $filters);
    }

    /**
     * Get monthly expense summary
     */
    public function getMonthlyExpenseSummary(int $userId, int $year, int $month): array
    {
        return $this->expenseRepository->getMonthlyExpenseSummary($userId, $year, $month);
    }

    /**
     * Get recent expenses
     */
    public function getRecentExpenses(int $userId, int $limit = 10): Collection
    {
        return $this->expenseRepository->getRecentExpenses($userId, $limit);
    }
}
