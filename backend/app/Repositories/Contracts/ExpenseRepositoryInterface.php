<?php

namespace App\Repositories\Contracts;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ExpenseRepositoryInterface
{
    /**
     * Create a new expense
     */
    public function create(array $data): Expense;

    /**
     * Find expense by ID
     */
    public function find(int $id): ?Expense;

    /**
     * Find expense by ID for specific user
     */
    public function findForUser(int $id, int $userId): ?Expense;

    /**
     * Get all expenses for a user with filters
     */
    public function getByUser(int $userId, array $filters = []): Collection|LengthAwarePaginator;

    /**
     * Get expenses by date range
     */
    public function getByDateRange(int $userId, string $startDate, string $endDate): Collection;

    /**
     * Search expenses
     */
    public function search(int $userId, string $query): Collection;

    /**
     * Update expense
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete expense
     */
    public function delete(int $id): bool;

    /**
     * Get monthly total for user
     */
    public function getMonthlyTotal(int $userId, int $year, int $month): float;

    /**
     * Get yearly total for user
     */
    public function getYearlyTotal(int $userId, int $year): float;

    /**
     * Get category breakdown for user
     */
    public function getCategoryBreakdown(int $userId, ?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Get recent expenses for user
     */
    public function getRecent(int $userId, int $limit = 10): Collection;

    /**
     * Get monthly trend data
     */
    public function getMonthlyTrend(int $userId, int $months = 12): Collection;
}