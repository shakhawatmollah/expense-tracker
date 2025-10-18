<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialHealthScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'overall_score',
        'budget_adherence_score',
        'spending_consistency_score',
        'savings_rate_score',
        'category_balance_score',
        'score_breakdown',
        'score_date',
        'recommendations'
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'budget_adherence_score' => 'decimal:2',
        'spending_consistency_score' => 'decimal:2',
        'savings_rate_score' => 'decimal:2',
        'category_balance_score' => 'decimal:2',
        'score_breakdown' => 'array',
        'score_date' => 'date',
        'recommendations' => 'array'
    ];

    /**
     * Get the user that owns the financial health score
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for current period scores
     */
    public function scopeCurrentPeriod($query, string $period = 'monthly')
    {
        return $query->where('score_period', $period)
                    ->where('score_date', '>=', now()->startOfMonth());
    }

    /**
     * Scope for scores within date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('score_date', [$startDate, $endDate]);
    }

    /**
     * Get health status based on overall score
     */
    public function getHealthStatus(): string
    {
        return match(true) {
            $this->overall_score >= 90 => 'Excellent',
            $this->overall_score >= 80 => 'Very Good',
            $this->overall_score >= 70 => 'Good',
            $this->overall_score >= 60 => 'Fair',
            $this->overall_score >= 50 => 'Poor',
            default => 'Critical'
        };
    }

    /**
     * Get color code for health status
     */
    public function getHealthColor(): string
    {
        return match($this->getHealthStatus()) {
            'Excellent' => '#22c55e',
            'Very Good' => '#84cc16',
            'Good' => '#eab308',
            'Fair' => '#f97316',
            'Poor' => '#ef4444',
            'Critical' => '#dc2626'
        };
    }

    /**
     * Get the strongest area
     */
    public function getStrongestArea(): string
    {
        $scores = [
            'expense_control' => $this->expense_control_score,
            'budget_adherence' => $this->budget_adherence_score,
            'savings_rate' => $this->savings_rate_score,
            'debt_ratio' => $this->debt_ratio_score
        ];

        return array_keys($scores, max($scores))[0];
    }

    /**
     * Get the weakest area
     */  
    public function getWeakestArea(): string
    {
        $scores = [
            'expense_control' => $this->expense_control_score,
            'budget_adherence' => $this->budget_adherence_score,
            'savings_rate' => $this->savings_rate_score,
            'debt_ratio' => $this->debt_ratio_score
        ];

        return array_keys($scores, min($scores))[0];
    }
}