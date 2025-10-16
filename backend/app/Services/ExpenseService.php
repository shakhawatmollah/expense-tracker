<?php

namespace App\Services;

use App\Models\Expense;
use App\Repositories\ExpenseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository
    ) {}

    /**
     * Get all expenses for a specific user
     */
    public function getUserExpenses(int $userId, array $filters = []): Collection
    {
        return $this->expenseRepository->getUserExpenses($userId, $filters);
    }

    /**
     * Create a new expense
     */
    public function create(array $data): Expense
    {
        return $this->expenseRepository->create($data);
    }

    /**
     * Find an expense for a specific user
     */
    public function findForUser(int $expenseId, int $userId): Expense
    {
        $expense = $this->expenseRepository->findForUser($expenseId, $userId);
        
        if (!$expense) {
            throw new \Exception('Expense not found or access denied');
        }

        return $expense;
    }

    /**
     * Update an expense
     */
    public function update(int $expenseId, int $userId, array $data): Expense
    {
        $expense = $this->findForUser($expenseId, $userId);
        
        return $this->expenseRepository->update($expense, $data);
    }

    /**
     * Delete an expense
     */
    public function delete(int $expenseId, int $userId): bool
    {
        $expense = $this->findForUser($expenseId, $userId);
        
        return $this->expenseRepository->delete($expense);
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