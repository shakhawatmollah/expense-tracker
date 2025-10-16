<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Repositories\ExpenseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function find(int $id): ?Expense
    {
        return Expense::find($id);
    }

    public function findForUser(int $id, int $userId): ?Expense
    {
        return Expense::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function getUserExpenses(int $userId, array $filters = []): Collection
    {
        $query = Expense::where('user_id', $userId)
            ->with(['category']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        $query->orderBy('date', 'desc');

        return $query->get();
    }

    public function getByUser(int $userId, array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Expense::where('user_id', $userId)
            ->with(['category']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        $query->orderBy('date', 'desc');

        if (!empty($filters['per_page'])) {
            return $query->paginate($filters['per_page']);
        }

        return $query->get();
    }

    public function getByDateRange(int $userId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Expense::where('user_id', $userId)->with(['category']);
        
        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }
        
        return $query->orderBy('date', 'desc')->get();
    }

    public function search(int $userId, string $query): Collection
    {
        return Expense::where('user_id', $userId)
            ->where(function ($q) use ($query) {
                $q->where('description', 'like', '%' . $query . '%')
                  ->orWhereHas('category', function ($categoryQuery) use ($query) {
                      $categoryQuery->where('name', 'like', '%' . $query . '%');
                  });
            })
            ->with(['category'])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);
        return $expense->fresh();
    }

    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }

    public function updateById(int $id, array $data): bool
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return false;
        }
        return $expense->update($data);
    }

    public function deleteById(int $id): bool
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return false;
        }
        return $expense->delete();
    }

    public function getMonthlyTotal(int $userId, int $year, int $month): float
    {
        return Expense::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');
    }

    public function getYearlyTotal(int $userId, int $year): float
    {
        return Expense::where('user_id', $userId)
            ->whereYear('date', $year)
            ->sum('amount');
    }

    public function getCategoryBreakdown(int $userId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Expense::where('user_id', $userId)
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select('categories.name', 'categories.color', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('categories.id', 'categories.name', 'categories.color');

        if ($startDate) {
            $query->whereDate('expenses.date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('expenses.date', '<=', $endDate);
        }

        return $query->orderBy('total', 'desc')->get();
    }

    public function getRecent(int $userId, int $limit = 10): Collection
    {
        return Expense::where('user_id', $userId)
            ->with(['category'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getMonthlyTrend(int $userId, int $months = 12): Collection
    {
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();
        
        return Expense::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->select(
                DB::raw('YEAR(date) as year'),
                DB::raw('MONTH(date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy(DB::raw('YEAR(date)'), DB::raw('MONTH(date)'))
            ->orderBy(DB::raw('YEAR(date)'))
            ->orderBy(DB::raw('MONTH(date)'))
            ->get();
    }

    public function getTotalExpenses(int $userId, array $filters = []): float
    {
        $query = Expense::where('user_id', $userId);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        return $query->sum('amount');
    }

    public function getExpensesByCategory(int $userId, array $filters = []): array
    {
        $query = Expense::where('user_id', $userId)
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                'categories.color',
                DB::raw('SUM(expenses.amount) as total'),
                DB::raw('COUNT(expenses.id) as count')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.color');

        if (!empty($filters['start_date'])) {
            $query->whereDate('expenses.date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('expenses.date', '<=', $filters['end_date']);
        }

        return $query->orderBy('total', 'desc')->get()->toArray();
    }

    public function getMonthlyExpenseSummary(int $userId, int $year, int $month): array
    {
        $expenses = Expense::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with(['category'])
            ->get();

        return [
            'total' => $expenses->sum('amount'),
            'count' => $expenses->count(),
            'by_category' => $expenses->groupBy('category.name')->map(function ($categoryExpenses) {
                return [
                    'total' => $categoryExpenses->sum('amount'),
                    'count' => $categoryExpenses->count()
                ];
            })->toArray()
        ];
    }

    public function getRecentExpenses(int $userId, int $limit = 10): Collection
    {
        return Expense::where('user_id', $userId)
            ->with(['category'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}