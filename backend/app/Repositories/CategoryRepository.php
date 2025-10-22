<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function find(int $id): ?Category
    {
        return Category::with(['expenses' => function ($query) {
            $query->select('id', 'category_id', 'amount', 'date');
        }])->find($id);
    }

    public function findForUser(int $id, int $userId): ?Category
    {
        return Category::with(['expenses' => function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->select('id', 'category_id', 'description', 'amount', 'date');
        }])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function getUserCategories(int $userId): Collection
    {
        return Category::withCount(['expenses' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    public function getByUser(int $userId): Collection
    {
        return Category::withCount(['expenses' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->where('user_id', $userId)
            ->get();
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    public function updateById(int $id, array $data): bool
    {
        $category = Category::find($id);
        if (! $category) {
            return false;
        }

        return $category->update($data);
    }

    public function deleteById(int $id): bool
    {
        $category = Category::find($id);
        if (! $category) {
            return false;
        }

        return $category->delete();
    }

    public function hasExpenses(int $id): bool
    {
        $category = Category::find($id);
        if (! $category) {
            return false;
        }

        return $category->expenses()->exists();
    }

    public function getCategoriesWithExpenseCounts(int $userId): Collection
    {
        return Category::where('user_id', $userId)
            ->withCount(['expenses' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->withSum(['expenses as total_amount' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }], 'amount')
            ->orderBy('expenses_count', 'desc')
            ->get();
    }
}
