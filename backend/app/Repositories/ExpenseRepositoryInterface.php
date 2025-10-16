<?php

namespace App\Repositories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseRepositoryInterface
{
    /**
     * Get all expenses for a specific user
     */
    public function getUserExpenses(int $userId, array $filters = []): Collection;

    /**
     * Create a new expense
     */
    public function create(array $data): Expense;

    /**
     * Find an expense for a specific user
     */
    public function findForUser(int $expenseId, int $userId): ?Expense;

    /**
     * Update an expense
     */
    public function update(Expense $expense, array $data): Expense;

    /**
     * Delete an expense
     */
    public function delete(Expense $expense): bool;

    /**
     * Get expenses by date range
     */
    public function getByDateRange(int $userId, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Search expenses by query
     */
    public function search(int $userId, string $query): Collection;

    /**
     * Get total expenses for user
     */
    public function getTotalExpenses(int $userId, array $filters = []): float;

    /**
     * Get expenses grouped by category
     */
    public function getExpensesByCategory(int $userId, array $filters = []): array;

    /**
     * Get monthly expense summary
     */
    public function getMonthlyExpenseSummary(int $userId, int $year, int $month): array;

    /**
     * Get recent expenses
     */
    public function getRecentExpenses(int $userId, int $limit = 10): Collection;
}