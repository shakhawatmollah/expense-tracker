<?php

namespace App\Services;

use App\Models\AnalyticsCache;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\FinancialHealthScore;
use App\Models\SpendingPattern;
use App\Models\User;
use App\Models\UserInsight;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Generate comprehensive analytics for a user
     */
    public function generateUserAnalytics(User $user, string $period = 'monthly'): array
    {
        $cacheKey = "user_analytics_{$user->id}_{$period}";
        $cached = AnalyticsCache::getCache($user->id, $cacheKey);

        if ($cached) {
            return $cached->cached_data;
        }

        $analytics = [
            'spending_patterns' => $this->detectSpendingPatterns($user, $period),
            'financial_health' => $this->calculateFinancialHealth($user, $period),
            'insights' => $this->generateInsights($user, $period),
            'forecasts' => $this->generateForecasts($user, $period),
            'recommendations' => $this->generateRecommendations($user, $period),
        ];

        // Cache the results
        AnalyticsCache::setCache(
            $user->id,
            $cacheKey,
            $analytics,
            60 // 1 hour cache
        );

        return $analytics;
    }

    /**
     * Detect spending patterns for a user
     */
    public function detectSpendingPatterns(User $user, string $period = 'monthly'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $expenses = Expense::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->with('category')
            ->get();

        $patterns = [];

        // Detect recurring expenses
        $patterns['recurring'] = $this->detectRecurringExpenses($expenses);

        // Detect seasonal patterns
        $patterns['seasonal'] = $this->detectSeasonalPatterns($expenses);

        // Detect category spikes
        $patterns['category_spikes'] = $this->detectCategorySpikes($expenses);

        // Detect anomalies
        $patterns['anomalies'] = $this->detectAnomalies($expenses);

        // Store patterns in database
        $this->storeSpendingPatterns($user, $patterns);

        return $patterns;
    }

    /**
     * Calculate financial health score
     */
    public function calculateFinancialHealth(User $user, string $period = 'monthly'): array
    {
        try {
            $startDate = $this->getPeriodStartDate($period);

            $expenses = Expense::where('user_id', $user->id)
                ->where('date', '>=', $startDate)
                ->sum('amount');

            $budgets = Budget::where('user_id', $user->id)
                ->where('start_date', '>=', $startDate)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching data: ' . $e->getMessage());
        }

        $totalBudget = $budgets->sum('amount');
        $budgetAdherence = $totalBudget > 0 ? (($totalBudget - $expenses) / $totalBudget) * 100 : 0;

        // Calculate component scores
        $spendingConsistencyScore = $this->calculateExpenseControlScore($expenses, $totalBudget);
        $budgetAdherenceScore = max(0, min(100, $budgetAdherence));
        $savingsRateScore = $this->calculateSavingsRateScore($user, $expenses, $period);
        $categoryBalanceScore = $this->calculateCategoryBalanceScore($user, $expenses);

        // Calculate overall score (weighted average)
        $overallScore = (
            $spendingConsistencyScore * 0.3 +
            $budgetAdherenceScore * 0.3 +
            $savingsRateScore * 0.25 +
            $categoryBalanceScore * 0.15
        );

        $healthData = [
            'overall_score' => round($overallScore, 2),
            'spending_consistency_score' => round($spendingConsistencyScore, 2),
            'budget_adherence_score' => round($budgetAdherenceScore, 2),
            'savings_rate_score' => round($savingsRateScore, 2),
            'category_balance_score' => round($categoryBalanceScore, 2),
            'score_breakdown' => [
                'total_expenses' => $expenses,
                'total_budget' => $totalBudget,
                'budget_remaining' => $totalBudget - $expenses,
                'period' => $period,
            ],
        ];

        // Store in database
        try {
            FinancialHealthScore::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'score_date' => now()->startOfMonth(),
                ],
                array_merge($healthData, [
                    'recommendations' => $this->generateHealthRecommendations($healthData),
                ])
            );
        } catch (\Exception $e) {
            throw new \Exception('Error saving financial health score: ' . $e->getMessage());
        }

        return $healthData;
    }

    /**
     * Generate insights for a user
     */
    public function generateInsights(User $user, string $period = 'monthly'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $insights = [];

        // Top spending categories
        $topCategories = Expense::where('expenses.user_id', $user->id)
            ->where('expenses.date', '>=', $startDate)
            ->leftJoin('categories', 'expenses.category_id', '=', 'categories.id')
            ->select(DB::raw('COALESCE(categories.name, "Uncategorized") as name'), DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $insights['top_categories'] = $topCategories;

        // Spending trends
        $insights['trends'] = $this->calculateSpendingTrends($user, $period);

        // Budget performance
        $insights['budget_performance'] = $this->analyzeBudgetPerformance($user, $period);

        // Store insights
        $this->storeUserInsights($user, $insights, $period);

        return $insights;
    }

    /**
     * Generate spending forecasts
     */
    public function generateForecasts(User $user, string $period = 'monthly'): array
    {
        $historicalData = $this->getHistoricalSpendingData($user, 12); // 12 months

        return [
            'next_month_forecast' => $this->forecastNextMonth($historicalData),
            'category_forecasts' => $this->forecastByCategory($user, $historicalData),
            'trend_analysis' => $this->analyzeTrends($historicalData),
        ];
    }

    /**
     * Generate recommendations based on analysis
     */
    public function generateRecommendations(User $user, string $period = 'monthly'): array
    {
        $patterns = SpendingPattern::where('user_id', $user->id)
            ->active()
            ->highConfidence()
            ->get();

        $recommendations = [];

        foreach ($patterns as $pattern) {
            switch ($pattern->pattern_type) {
                case 'category_spike':
                    $recommendations[] = [
                        'type' => 'cost_reduction',
                        'category' => $pattern->pattern_data['category'] ?? 'Unknown',
                        'message' => "Consider reducing spending in {$pattern->pattern_name}",
                        'potential_savings' => $pattern->impact_amount * 0.3,
                    ];

                    break;

                case 'monthly_recurring':
                    $recommendations[] = [
                        'type' => 'budget_optimization',
                        'message' => "Set up automatic budget for recurring {$pattern->pattern_name}",
                        'suggested_amount' => $pattern->impact_amount * 1.1,
                    ];

                    break;
            }
        }

        return $recommendations;
    }

    // Private helper methods

    private function getPeriodStartDate(string $period): Carbon
    {
        return match($period) {
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'quarterly' => now()->startOfQuarter(),
            'yearly' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
    }

    private function detectRecurringExpenses(Collection $expenses): array
    {
        // Group expenses by amount and description similarity
        $grouped = $expenses->groupBy(function ($expense) {
            return round($expense->amount, 0) . '_' . substr($expense->description, 0, 10);
        });

        $recurring = [];
        foreach ($grouped as $group) {
            if ($group->count() >= 3) { // At least 3 occurrences
                $dates = $group->pluck('date')->sort();
                $intervals = [];

                for ($i = 1; $i < $dates->count(); $i++) {
                    $intervals[] = $dates[$i]->diffInDays($dates[$i - 1]);
                }

                $avgInterval = array_sum($intervals) / count($intervals);

                if ($avgInterval >= 25 && $avgInterval <= 35) { // Monthly
                    $recurring[] = [
                        'type' => 'monthly_recurring',
                        'description' => $group->first()->description,
                        'amount' => $group->first()->amount,
                        'frequency' => 'monthly',
                        'confidence' => 85 + (min($group->count(), 10) * 1.5),
                    ];
                }
            }
        }

        return $recurring;
    }

    private function detectSeasonalPatterns(Collection $expenses): array
    {
        // Analyze expenses by month to detect seasonal patterns
        $monthlyTotals = $expenses->groupBy(function ($expense) {
            return $expense->date->format('m');
        })->map->sum('amount');

        $seasonal = [];
        $average = $monthlyTotals->avg();

        foreach ($monthlyTotals as $month => $total) {
            if ($total > $average * 1.3) { // 30% above average
                $seasonal[] = [
                    'type' => 'seasonal_spike',
                    'month' => $month,
                    'amount' => $total,
                    'deviation' => (($total - $average) / $average) * 100,
                ];
            }
        }

        return $seasonal;
    }

    private function detectCategorySpikes(Collection $expenses): array
    {
        $categoryTotals = $expenses->groupBy('category_id')->map->sum('amount');
        $average = $categoryTotals->avg();

        $spikes = [];
        foreach ($categoryTotals as $categoryId => $total) {
            if ($total > $average * 1.5) { // 50% above average
                $category = Category::find($categoryId);
                $spikes[] = [
                    'type' => 'category_spike',
                    'category' => $category?->name ?? 'Unknown',
                    'amount' => $total,
                    'deviation' => (($total - $average) / $average) * 100,
                ];
            }
        }

        return $spikes;
    }

    private function detectAnomalies(Collection $expenses): array
    {
        $amounts = $expenses->pluck('amount');
        $mean = $amounts->avg();
        $stdDev = sqrt($amounts->map(fn ($x) => pow($x - $mean, 2))->avg());

        $anomalies = [];
        foreach ($expenses as $expense) {
            $zScore = abs(($expense->amount - $mean) / $stdDev);
            if ($zScore > 2.5) { // Significant outlier
                $anomalies[] = [
                    'type' => 'anomaly',
                    'expense_id' => $expense->id,
                    'amount' => $expense->amount,
                    'z_score' => $zScore,
                    'date' => $expense->date,
                ];
            }
        }

        return $anomalies;
    }

    private function storeSpendingPatterns(User $user, array $patterns): void
    {
        foreach ($patterns as $type => $typePatterns) {
            foreach ($typePatterns as $pattern) {
                // Determine frequency based on pattern type
                $frequency = match($pattern['type']) {
                    'daily_recurring' => 1.0,
                    'weekly_recurring' => 7.0,
                    'monthly_recurring' => 30.0,
                    'seasonal' => 365.0,
                    'category_spike' => $pattern['frequency'] ?? 0.0,
                    'anomaly' => $pattern['frequency'] ?? 0.0,
                    default => 0.0
                };

                SpendingPattern::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'pattern_type' => $pattern['type'],
                        'pattern_name' => $pattern['description'] ?? $type,
                    ],
                    [
                        'description' => $pattern['description'] ?? "Pattern detected in {$type}",
                        'pattern_data' => $pattern,
                        'frequency' => $frequency,
                        'confidence_score' => $pattern['confidence'] ?? 75,
                        'impact_amount' => $pattern['amount'] ?? 0,
                        'first_detected' => $pattern['first_detected'] ?? now(),
                        'last_detected' => now(),
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function storeUserInsights(User $user, array $insights, string $period): void
    {
        UserInsight::updateOrCreate(
            [
                'user_id' => $user->id,
                'insight_type' => 'trend_analysis',
            ],
            [
                'title' => 'Monthly Analytics for ' . now()->format('F Y'),
                'description' => $this->generateInsightSummary($insights),
                'data' => $insights,
                'confidence_score' => 85.0,
                'generated_at' => now(),
            ]
        );
    }

    private function calculateExpenseControlScore(float $expenses, float $budget): float
    {
        if ($budget <= 0) {
            return 50;
        } // Neutral score if no budget

        $ratio = $expenses / $budget;

        return match(true) {
            $ratio <= 0.8 => 100,
            $ratio <= 0.9 => 90,
            $ratio <= 1.0 => 80,
            $ratio <= 1.1 => 60,
            $ratio <= 1.2 => 40,
            default => 20
        };
    }

    private function calculateSavingsRateScore(User $user, float $expenses, string $period): float
    {
        // This would need income data - for now return a placeholder
        return 75; // Placeholder - would calculate based on income vs expenses
    }

    private function calculateCategoryBalanceScore(User $user, float $expenses): float
    {
        // This would need debt data - for now return a placeholder
        return 80; // Placeholder - would calculate based on debt payments
    }

    private function generateHealthRecommendations(array $healthData): array
    {
        $recommendations = [];

        if ($healthData['budget_adherence_score'] < 70) {
            $recommendations[] = 'Consider reviewing and adjusting your budget allocation';
        }

        if ($healthData['spending_consistency_score'] < 60) {
            $recommendations[] = 'Track daily expenses more closely to improve spending control';
        }

        return $recommendations;
    }

    private function calculateSpendingTrends(User $user, string $period): array
    {
        // Calculate month-over-month trends
        $currentMonth = Expense::where('user_id', $user->id)
            ->whereMonth('date', now()->month)
            ->sum('amount');

        $lastMonth = Expense::where('user_id', $user->id)
            ->whereMonth('date', now()->subMonth()->month)
            ->sum('amount');

        $trend = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return [
            'current_month' => $currentMonth,
            'last_month' => $lastMonth,
            'trend_percentage' => round($trend, 2),
            'trend_direction' => $trend > 0 ? 'increasing' : 'decreasing',
        ];
    }

    private function analyzeBudgetPerformance(User $user, string $period): array
    {
        $budgets = Budget::where('user_id', $user->id)
            ->with('category')
            ->get();

        $performance = [];
        foreach ($budgets as $budget) {
            $spent = Expense::where('user_id', $user->id)
                ->where('category_id', $budget->category_id)
                ->whereBetween('date', [$budget->start_date, $budget->end_date])
                ->sum('amount');

            $performance[] = [
                'category' => $budget->category?->name ?? 'Unknown',
                'budgeted' => $budget->amount,
                'spent' => $spent,
                'remaining' => $budget->amount - $spent,
                'utilization' => round(($spent / $budget->amount) * 100, 2),
            ];
        }

        return $performance;
    }

    private function getHistoricalSpendingData(User $user, int $months): Collection
    {
        return Expense::where('user_id', $user->id)
            ->where('date', '>=', now()->subMonths($months))
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    private function forecastNextMonth(Collection $historicalData): float
    {
        if ($historicalData->count() < 3) {
            return $historicalData->avg('total') ?? 0;
        }

        // Simple linear trend forecast
        $recent = $historicalData->take(-3);

        return $recent->avg('total') * 1.02; // Slight growth assumption
    }

    private function forecastByCategory(User $user, Collection $historicalData): array
    {
        // Placeholder - would implement category-specific forecasting
        return [];
    }

    private function analyzeTrends(Collection $historicalData): array
    {
        if ($historicalData->count() < 2) {
            return ['trend' => 'insufficient_data'];
        }

        $first = $historicalData->first()->total;
        $last = $historicalData->last()->total;
        $trend = (($last - $first) / $first) * 100;

        return [
            'overall_trend' => $trend > 5 ? 'increasing' : ($trend < -5 ? 'decreasing' : 'stable'),
            'trend_percentage' => round($trend, 2),
        ];
    }

    private function generateInsightSummary(array $insights): string
    {
        $topCategory = $insights['top_categories'][0] ?? null;
        $summary = 'Financial analysis complete. ';

        if ($topCategory && isset($topCategory->name)) {
            $summary .= 'Top spending category: ' . $topCategory->name . ' ($' . $topCategory->total . '). ';
        }

        return $summary;
    }

    private function generateActionItems(array $insights): array
    {
        $actions = [];

        if (isset($insights['trends']['trend_direction']) && $insights['trends']['trend_direction'] === 'increasing') {
            $actions[] = 'Review recent spending increases and identify cost reduction opportunities';
        }

        return $actions;
    }
}
