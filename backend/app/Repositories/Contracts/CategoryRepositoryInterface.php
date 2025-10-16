<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Create a new category
     */
    public function create(array $data): Category;

    /**
     * Find category by ID
     */
    public function find(int $id): ?Category;

    /**
     * Find category by ID for specific user
     */
    public function findForUser(int $id, int $userId): ?Category;

    /**
     * Get all categories for a user
     */
    public function getByUser(int $userId): Collection;

    /**
     * Update category
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete category
     */
    public function delete(int $id): bool;

    /**
     * Check if category has expenses
     */
    public function hasExpenses(int $id): bool;
}