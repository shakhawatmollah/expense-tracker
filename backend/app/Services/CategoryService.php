<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepositoryInterface;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\CategoryValidationException;
use App\Exceptions\CategoryDatabaseException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($data) {
                $category = $this->categoryRepository->create($data);
                
                Log::info('Category created successfully', [
                    'category_id' => $category->id,
                    'user_id' => $data['user_id'] ?? null,
                    'name' => $data['name'] ?? null
                ]);
                
                return $category;
            });
            
        } catch (\Exception $e) {
            Log::error('Failed to create category', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            throw new CategoryDatabaseException(
                'category creation',
                $e->getMessage(),
                ['user_id' => $data['user_id'] ?? null]
            );
        }
    }

    /**
     * Find a category for a specific user
     */
    public function findForUser(int $categoryId, int $userId): Category
    {
        try {
            $category = $this->categoryRepository->findForUser($categoryId, $userId);
            
            if (!$category) {
                throw new CategoryNotFoundException($categoryId, [
                    'user_id' => $userId
                ]);
            }

            return $category;
            
        } catch (CategoryNotFoundException $e) {
            throw $e;
            
        } catch (\Exception $e) {
            Log::error('Failed to find category', [
                'category_id' => $categoryId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw new CategoryDatabaseException(
                'category retrieval',
                $e->getMessage(),
                ['category_id' => $categoryId, 'user_id' => $userId]
            );
        }
    }

    /**
     * Update a category
     */
    public function update(int $categoryId, int $userId, array $data): Category
    {
        try {
            $category = $this->findForUser($categoryId, $userId);
            
            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($category, $data, $categoryId, $userId) {
                $updatedCategory = $this->categoryRepository->update($category, $data);
                
                Log::info('Category updated successfully', [
                    'category_id' => $categoryId,
                    'user_id' => $userId,
                    'updated_fields' => array_keys($data)
                ]);
                
                return $updatedCategory;
            });
            
        } catch (CategoryNotFoundException $e) {
            throw $e;
            
        } catch (\Exception $e) {
            Log::error('Failed to update category', [
                'category_id' => $categoryId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw new CategoryDatabaseException(
                'category update',
                $e->getMessage(),
                ['category_id' => $categoryId, 'user_id' => $userId]
            );
        }
    }

    /**
     * Delete a category
     */
    public function delete(int $categoryId, int $userId): bool
    {
        try {
            $category = $this->findForUser($categoryId, $userId);
            
            // Check if category has expenses
            if ($category->expenses()->count() > 0) {
                throw new CategoryValidationException(
                    'Cannot delete category with expenses',
                    ['category' => ['Cannot delete category that has expenses. Please reassign or delete the expenses first.']],
                    ['category_id' => $categoryId, 'user_id' => $userId, 'expense_count' => $category->expenses()->count()]
                );
            }
            
            // Use transaction to ensure atomicity
            return DB::transaction(function () use ($category, $categoryId, $userId) {
                $deleted = $this->categoryRepository->delete($category);
                
                if ($deleted) {
                    Log::info('Category deleted successfully', [
                        'category_id' => $categoryId,
                        'user_id' => $userId
                    ]);
                }
                
                return $deleted;
            });
            
        } catch (CategoryNotFoundException | CategoryValidationException $e) {
            throw $e;
            
        } catch (\Exception $e) {
            Log::error('Failed to delete category', [
                'category_id' => $categoryId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw new CategoryDatabaseException(
                'category deletion',
                $e->getMessage(),
                ['category_id' => $categoryId, 'user_id' => $userId]
            );
        }
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
        try {
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

            // Use transaction to create all default categories atomically
            return DB::transaction(function () use ($defaultCategories, $userId) {
                $categories = collect();
                
                foreach ($defaultCategories as $categoryData) {
                    $categories->push($this->categoryRepository->create($categoryData));
                }
                
                Log::info('Default categories created successfully', [
                    'user_id' => $userId,
                    'count' => $categories->count()
                ]);
                
                return $categories;
            });
            
        } catch (\Exception $e) {
            Log::error('Failed to create default categories', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw new CategoryDatabaseException(
                'default categories creation',
                $e->getMessage(),
                ['user_id' => $userId]
            );
        }
    }

    /**
     * Check if user has any categories
     */
    public function userHasCategories(int $userId): bool
    {
        return $this->categoryRepository->getUserCategories($userId)->count() > 0;
    }
}