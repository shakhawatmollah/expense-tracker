<?php

namespace App\Services;

use App\Repositories\ExpenseRepositoryInterface;
use App\Repositories\CategoryRepositoryInterface;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository,
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Get dashboard overview data for user
     */
    public function getDashboardOverview(int $userId): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Current month expenses
        $currentMonthExpenses = $this->expenseRepository->getByDateRange(
            $userId, 
            $startOfMonth->format('Y-m-d'), 
            $endOfMonth->format('Y-m-d')
        );

        // Last month expenses
        $lastMonthExpenses = $this->expenseRepository->getByDateRange(
            $userId, 
            $startOfLastMonth->format('Y-m-d'), 
            $endOfLastMonth->format('Y-m-d')
        );

        // Calculate totals
        $currentMonthTotal = $currentMonthExpenses->sum('amount');
        $lastMonthTotal = $lastMonthExpenses->sum('amount');

        // Calculate percentage change
        $percentageChange = 0;
        if ($lastMonthTotal > 0) {
            $percentageChange = (($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100;
        }

        // Get recent expenses
        $recentExpenses = $this->expenseRepository->getRecentExpenses($userId, 5);

        // Get expenses by category for current month
        $expensesByCategory = $this->expenseRepository->getExpensesByCategory($userId, [
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date' => $endOfMonth->format('Y-m-d')
        ]);

        return [
            'current_month' => [
                'total' => $currentMonthTotal,
                'count' => $currentMonthExpenses->count(),
                'month' => $now->format('F Y'),
            ],
            'last_month' => [
                'total' => $lastMonthTotal,
                'count' => $lastMonthExpenses->count(),
                'month' => $startOfLastMonth->format('F Y'),
            ],
            'percentage_change' => round($percentageChange, 2),
            'recent_expenses' => $recentExpenses,
            'expenses_by_category' => $expensesByCategory,
            'total_categories' => $this->categoryRepository->getUserCategories($userId)->count(),
        ];
    }

    /**
     * Get monthly spending trends
     */
    public function getMonthlySpendingTrends(int $userId, int $months = 6): array
    {
        $trends = [];
        $now = Carbon::now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $monthlyExpenses = $this->expenseRepository->getByDateRange(
                $userId,
                $startOfMonth->format('Y-m-d'),
                $endOfMonth->format('Y-m-d')
            );

            $trends[] = [
                'month' => $month->format('Y-m-d'), // Changed from 'M Y' to 'Y-m-d' for better parsing
                'total' => $monthlyExpenses->sum('amount'),
                'count' => $monthlyExpenses->count(),
                'date' => $month->format('Y-m-d'),
                'label' => $month->format('M Y'), // Added separate label for display
            ];
        }

        return $trends;
    }

    /**
     * Get category spending analysis
     */
    public function getCategorySpendingAnalysis(int $userId, ?string $period = 'current_month'): array
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $endDate = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_3_months':
                $startDate = $now->copy()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_6_months':
                $startDate = $now->copy()->subMonths(6)->startOfMonth()->format('Y-m-d');
                $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'current_year':
                $startDate = $now->copy()->startOfYear()->format('Y-m-d');
                $endDate = $now->copy()->endOfYear()->format('Y-m-d');
                break;
            default: // current_month
                $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        }

        return $this->expenseRepository->getExpensesByCategory($userId, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Get daily spending for current month
     */
    public function getDailySpendingCurrentMonth(int $userId): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $expenses = $this->expenseRepository->getByDateRange(
            $userId,
            $startOfMonth->format('Y-m-d'),
            $endOfMonth->format('Y-m-d')
        );

        // Group by date
        $dailySpending = [];
        $current = $startOfMonth->copy();

        while ($current->lte($endOfMonth)) {
            $dateStr = $current->format('Y-m-d');
            $dayExpenses = $expenses->filter(function ($expense) use ($dateStr) {
                return Carbon::parse($expense->date)->format('Y-m-d') === $dateStr;
            });

            $dailySpending[] = [
                'date' => $dateStr,
                'day' => $current->format('j'),
                'total' => $dayExpenses->sum('amount'),
                'count' => $dayExpenses->count(),
            ];

            $current->addDay();
        }

        return $dailySpending;
    }

    /**
     * Get spending statistics
     */
    public function getSpendingStatistics(int $userId): array
    {
        $now = Carbon::now();
        
        // All time stats
        $allExpenses = $this->expenseRepository->getUserExpenses($userId);
        $totalAllTime = $allExpenses->sum('amount');
        $averageExpense = $allExpenses->count() > 0 ? $totalAllTime / $allExpenses->count() : 0;

        // This year stats
        $yearStart = $now->copy()->startOfYear();
        $yearExpenses = $this->expenseRepository->getByDateRange(
            $userId,
            $yearStart->format('Y-m-d'),
            $now->format('Y-m-d')
        );

        return [
            'all_time' => [
                'total' => $totalAllTime,
                'count' => $allExpenses->count(),
                'average' => round($averageExpense, 2),
            ],
            'this_year' => [
                'total' => $yearExpenses->sum('amount'),
                'count' => $yearExpenses->count(),
                'average' => $yearExpenses->count() > 0 ? round($yearExpenses->sum('amount') / $yearExpenses->count(), 2) : 0,
            ],
            'highest_expense' => $allExpenses->sortByDesc('amount')->first(),
            'most_used_category' => $this->getMostUsedCategory($userId),
        ];
    }

    /**
     * Get most used category
     */
    private function getMostUsedCategory(int $userId): ?array
    {
        $categoriesWithCounts = $this->categoryRepository->getCategoriesWithExpenseCounts($userId);
        
        if ($categoriesWithCounts->isEmpty()) {
            return null;
        }

        $mostUsed = $categoriesWithCounts->sortByDesc('expenses_count')->first();
        
        return [
            'id' => $mostUsed->id,
            'name' => $mostUsed->name,
            'color' => $mostUsed->color,
            'count' => $mostUsed->expenses_count,
        ];
    }
}