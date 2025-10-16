<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Get all categories for a specific user
     */
    public function getUserCategories(int $userId): Collection
    {
        return $this->categoryRepository->getUserCategories($userId);
    }

    /**
     * Create a new category
     */
    public function create(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    /**
     * Find a category for a specific user
     */
    public function findForUser(int $categoryId, int $userId): Category
    {
        $category = $this->categoryRepository->findForUser($categoryId, $userId);
        
        if (!$category) {
            throw new \Exception('Category not found or access denied');
        }

        return $category;
    }

    /**
     * Update a category
     */
    public function update(int $categoryId, int $userId, array $data): Category
    {
        $category = $this->findForUser($categoryId, $userId);
        
        return $this->categoryRepository->update($category, $data);
    }

    /**
     * Delete a category
     */
    public function delete(int $categoryId, int $userId): bool
    {
        $category = $this->findForUser($categoryId, $userId);
        
        // Check if category has expenses
        if ($category->expenses()->count() > 0) {
            throw new \Exception('Cannot delete category that has expenses. Please reassign or delete the expenses first.');
        }
        
        return $this->categoryRepository->delete($category);
    }

    /**
     * Get categories with expense counts
     */
    public function getCategoriesWithExpenseCounts(int $userId): Collection
    {
        return $this->categoryRepository->getCategoriesWithExpenseCounts($userId);
    }

    /**
     * Get default categories for user
     */
    public function createDefaultCategories(int $userId): Collection
    {
        $defaultCategories = [
            ['name' => 'Food & Dining', 'color' => '#EF4444', 'user_id' => $userId],
            ['name' => 'Transportation', 'color' => '#3B82F6', 'user_id' => $userId],
            ['name' => 'Shopping', 'color' => '#10B981', 'user_id' => $userId],
            ['name' => 'Entertainment', 'color' => '#8B5CF6', 'user_id' => $userId],
            ['name' => 'Bills & Utilities', 'color' => '#F59E0B', 'user_id' => $userId],
            ['name' => 'Healthcare', 'color' => '#EC4899', 'user_id' => $userId],
            ['name' => 'Education', 'color' => '#6366F1', 'user_id' => $userId],
            ['name' => 'Travel', 'color' => '#14B8A6', 'user_id' => $userId],
            ['name' => 'Other', 'color' => '#6B7280', 'user_id' => $userId],
        ];

        $categories = collect();
        foreach ($defaultCategories as $categoryData) {
            $categories->push($this->create($categoryData));
        }

        return $categories;
    }

    /**
     * Check if user has any categories
     */
    public function userHasCategories(int $userId): bool
    {
        return $this->categoryRepository->getUserCategories($userId)->count() > 0;
    }
}