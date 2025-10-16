<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories for a specific user
     */
    public function getUserCategories(int $userId): Collection;

    /**
     * Create a new category
     */
    public function create(array $data): Category;

    /**
     * Find a category for a specific user
     */
    public function findForUser(int $categoryId, int $userId): ?Category;

    /**
     * Update a category
     */
    public function update(Category $category, array $data): Category;

    /**
     * Delete a category
     */
    public function delete(Category $category): bool;

    /**
     * Get categories with expense counts
     */
    public function getCategoriesWithExpenseCounts(int $userId): Collection;
}