<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;

class ExportService
{
    /**
     * Export expenses to CSV format
     */
    public function exportExpenses(
        int $userId,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $categoryId = null,
        string $format = 'csv'
    ): string {
        $query = Expense::where('user_id', $userId)
            ->with(['category']);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $expenses = $query->orderBy('date', 'desc')->get();

        if ($format === 'csv') {
            return $this->generateExpensesCsv($expenses, $userId);
        }

        // Add support for other formats here (xlsx, pdf)
        return $this->generateExpensesCsv($expenses, $userId);
    }

    /**
     * Export categories to CSV format
     */
    public function exportCategories(int $userId, string $format = 'csv'): string
    {
        $categories = Category::where('user_id', $userId)
            ->withCount('expenses')
            ->withSum('expenses as total_amount', 'amount')
            ->get();

        if ($format === 'csv') {
            return $this->generateCategoriesCsv($categories, $userId);
        }

        return $this->generateCategoriesCsv($categories, $userId);
    }

    /**
     * Export budgets to CSV format
     */
    public function exportBudgets(
        int $userId,
        ?string $period = null,
        string $format = 'csv'
    ): string {
        $query = Budget::where('user_id', $userId)
            ->with('category');

        if ($period) {
            $query->where('period', $period);
        }

        $budgets = $query->get();

        if ($format === 'csv') {
            return $this->generateBudgetsCsv($budgets, $userId);
        }

        return $this->generateBudgetsCsv($budgets, $userId);
    }

    /**
     * Export comprehensive financial report
     */
    public function exportFinancialReport(
        int $userId,
        ?string $startDate = null,
        ?string $endDate = null,
        string $format = 'pdf',
        bool $includeCharts = true
    ): string {
        $startDate ??= Carbon::now()->startOfMonth()->toDateString();
        $endDate ??= Carbon::now()->endOfMonth()->toDateString();

        $data = [
            'expenses' => Expense::where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->with('category')
                ->get(),
            'categories' => Category::where('user_id', $userId)->get(),
            'budgets' => Budget::where('user_id', $userId)
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate)
                ->get(),
            'summary' => $this->calculateSummary($userId, $startDate, $endDate),
        ];

        if ($format === 'csv') {
            return $this->generateFinancialReportCsv($data, $userId, $startDate, $endDate);
        }

        // For now, fall back to CSV
        return $this->generateFinancialReportCsv($data, $userId, $startDate, $endDate);
    }

    /**
     * Get export history for user
     */
    public function getExportHistory(int $userId): array
    {
        // This would typically come from a database table tracking exports
        // For now, return empty array
        return [];
    }

    /**
     * Generate expenses CSV file
     */
    private function generateExpensesCsv($expenses, int $userId): string
    {
        $filename = 'expenses_' . $userId . '_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        // Create exports directory if it doesn't exist
        if (! file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0o755, true);
        }

        $file = fopen($filepath, 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header
        fputcsv($file, [
            'Date',
            'Description',
            'Amount',
            'Category',
            'Notes',
            'Created At',
        ]);

        // Write data
        foreach ($expenses as $expense) {
            fputcsv($file, [
                $expense->date->format('Y-m-d'),
                $expense->description,
                number_format($expense->amount, 2, '.', ''),
                $expense->category->name ?? 'Uncategorized',
                $expense->notes ?? '',
                $expense->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        // Add summary row
        fputcsv($file, []);
        fputcsv($file, ['Total', '', number_format($expenses->sum('amount'), 2, '.', ''), '', '', '']);

        fclose($file);

        return $filepath;
    }

    /**
     * Generate categories CSV file
     */
    private function generateCategoriesCsv($categories, int $userId): string
    {
        $filename = 'categories_' . $userId . '_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (! file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0o755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header
        fputcsv($file, [
            'Name',
            'Description',
            'Color',
            'Expense Count',
            'Total Amount',
            'Created At',
        ]);

        // Write data
        foreach ($categories as $category) {
            fputcsv($file, [
                $category->name,
                $category->description ?? '',
                $category->color,
                $category->expenses_count ?? 0,
                number_format($category->total_amount ?? 0, 2, '.', ''),
                $category->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($file);

        return $filepath;
    }

    /**
     * Generate budgets CSV file
     */
    private function generateBudgetsCsv($budgets, int $userId): string
    {
        $filename = 'budgets_' . $userId . '_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (! file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0o755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header
        fputcsv($file, [
            'Name',
            'Category',
            'Amount',
            'Period',
            'Start Date',
            'End Date',
            'Spent',
            'Remaining',
            'Status',
            'Created At',
        ]);

        // Write data
        foreach ($budgets as $budget) {
            $spent = $budget->spent_amount;
            $remaining = $budget->remaining_amount;
            $status = $spent > $budget->amount ? 'Over Budget' : 'Within Budget';

            fputcsv($file, [
                $budget->name,
                $budget->category->name ?? 'All Categories',
                number_format($budget->amount, 2, '.', ''),
                $budget->period,
                $budget->start_date->format('Y-m-d'),
                $budget->end_date->format('Y-m-d'),
                number_format($spent, 2, '.', ''),
                number_format($remaining, 2, '.', ''),
                $status,
                $budget->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($file);

        return $filepath;
    }

    /**
     * Generate financial report CSV
     */
    private function generateFinancialReportCsv($data, int $userId, string $startDate, string $endDate): string
    {
        $filename = 'financial_report_' . $userId . '_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (! file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0o755, true);
        }

        $file = fopen($filepath, 'w');
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write report header
        fputcsv($file, ['Financial Report']);
        fputcsv($file, ['Period: ' . $startDate . ' to ' . $endDate]);
        fputcsv($file, ['Generated: ' . Carbon::now()->format('Y-m-d H:i:s')]);
        fputcsv($file, []);

        // Write summary
        fputcsv($file, ['SUMMARY']);
        fputcsv($file, ['Total Expenses', number_format($data['summary']['total_expenses'], 2, '.', '')]);
        fputcsv($file, ['Total Income', number_format($data['summary']['total_income'], 2, '.', '')]);
        fputcsv($file, ['Net', number_format($data['summary']['net'], 2, '.', '')]);
        fputcsv($file, ['Average Daily Spending', number_format($data['summary']['avg_daily'], 2, '.', '')]);
        fputcsv($file, []);

        // Write expenses by category
        fputcsv($file, ['EXPENSES BY CATEGORY']);
        fputcsv($file, ['Category', 'Amount', 'Percentage']);
        foreach ($data['summary']['by_category'] as $cat) {
            fputcsv($file, [
                $cat['name'],
                number_format($cat['amount'], 2, '.', ''),
                number_format($cat['percentage'], 1, '.', '') . '%',
            ]);
        }
        fputcsv($file, []);

        // Write detailed expenses
        fputcsv($file, ['DETAILED EXPENSES']);
        fputcsv($file, ['Date', 'Description', 'Amount', 'Category']);
        foreach ($data['expenses'] as $expense) {
            fputcsv($file, [
                $expense->date->format('Y-m-d'),
                $expense->description,
                number_format($expense->amount, 2, '.', ''),
                $expense->category->name ?? 'Uncategorized',
            ]);
        }

        fclose($file);

        return $filepath;
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary(int $userId, string $startDate, string $endDate): array
    {
        $expenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('category')
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $totalIncome = 0; // Would come from income tracking if implemented

        $byCategory = [];
        foreach ($expenses->groupBy('category_id') as $categoryId => $categoryExpenses) {
            $amount = $categoryExpenses->sum('amount');
            $byCategory[] = [
                'name' => $categoryExpenses->first()->category->name ?? 'Uncategorized',
                'amount' => $amount,
                'percentage' => $totalExpenses > 0 ? ($amount / $totalExpenses * 100) : 0,
            ];
        }

        // Sort by amount descending
        usort($byCategory, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

        return [
            'total_expenses' => $totalExpenses,
            'total_income' => $totalIncome,
            'net' => $totalIncome - $totalExpenses,
            'avg_daily' => $days > 0 ? $totalExpenses / $days : 0,
            'by_category' => $byCategory,
        ];
    }
}
