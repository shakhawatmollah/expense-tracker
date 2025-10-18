<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'amount',
        'period',
        'start_date',
        'end_date',
        'is_active',
        'alert_thresholds',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'alert_thresholds' => 'array',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Budget periods enumeration
     */
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_YEARLY = 'yearly';
    const PERIOD_CUSTOM = 'custom';

    const PERIODS = [
        self::PERIOD_WEEKLY,
        self::PERIOD_MONTHLY,
        self::PERIOD_YEARLY,
        self::PERIOD_CUSTOM,
    ];

    /**
     * Default alert thresholds
     */
    const DEFAULT_ALERT_THRESHOLDS = [
        'warning' => 80, // 80% of budget
        'danger' => 100, // 100% of budget
    ];

    /**
     * Get available periods
     */
    public static function getPeriods(): array
    {
        return [
            self::PERIOD_WEEKLY => 'Weekly',
            self::PERIOD_MONTHLY => 'Monthly',
            self::PERIOD_YEARLY => 'Yearly',
            self::PERIOD_CUSTOM => 'Custom',
        ];
    }

    /**
     * Relationship: Budget belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Budget belongs to a category (optional)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship: Get expenses related to this budget
     */
    public function expenses()
    {
        $query = Expense::where('user_id', $this->user_id)
                       ->whereBetween('date', [$this->start_date, $this->end_date]);

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        return $query;
    }

    /**
     * Get total spent amount for this budget period
     */
    public function getSpentAmountAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Get remaining amount for this budget
     */
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->amount - $this->spent_amount);
    }

    /**
     * Get percentage of budget used
     */
    public function getUsagePercentageAttribute()
    {
        if ($this->amount <= 0) {
            return 0;
        }
        return min(100, ($this->spent_amount / $this->amount) * 100);
    }

    /**
     * Check if budget is over limit
     */
    public function getIsOverBudgetAttribute()
    {
        return $this->spent_amount > $this->amount;
    }

    /**
     * Get alert status based on thresholds
     */
    public function getAlertStatusAttribute()
    {
        $percentage = $this->usage_percentage;
        $thresholds = $this->alert_thresholds ?: self::DEFAULT_ALERT_THRESHOLDS;

        if ($percentage >= ($thresholds['danger'] ?? 100)) {
            return 'danger';
        } elseif ($percentage >= ($thresholds['warning'] ?? 80)) {
            return 'warning';
        }

        return 'safe';
    }

    /**
     * Check if budget period is current
     */
    public function getIsCurrentAttribute()
    {
        $now = Carbon::now();
        return $this->is_active && 
               $this->start_date <= $now && 
               $this->end_date >= $now;
    }

    /**
     * Get days remaining in budget period
     */
    public function getDaysRemainingAttribute()
    {
        $now = Carbon::now();
        if ($this->end_date < $now) {
            return 0;
        }
        return $now->diffInDays($this->end_date);
    }

    /**
     * Calculate automatic date ranges based on period
     */
    public static function calculateDateRange($period, $startDate = null)
    {
        $start = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth();

        switch ($period) {
            case self::PERIOD_WEEKLY:
                $end = $start->copy()->endOfWeek();
                break;
            case self::PERIOD_MONTHLY:
                $end = $start->copy()->endOfMonth();
                break;
            case self::PERIOD_YEARLY:
                $end = $start->copy()->endOfYear();
                break;
            default: // custom
                $end = $start->copy()->addMonth(); // Default to 1 month for custom
                break;
        }

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }

    /**
     * Create budget with automatic date calculation
     */
    public static function createWithPeriod($data)
    {
        if ($data['period'] !== self::PERIOD_CUSTOM) {
            $dateRange = self::calculateDateRange($data['period'], $data['start_date'] ?? null);
            $data['start_date'] = $dateRange['start_date'];
            $data['end_date'] = $dateRange['end_date'];
        }

        // Set default alert thresholds if not provided
        if (!isset($data['alert_thresholds'])) {
            $data['alert_thresholds'] = self::DEFAULT_ALERT_THRESHOLDS;
        }

        return self::create($data);
    }

    /**
     * Get budget summary with analytics
     */
    public function getSummary()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'spent_amount' => $this->spent_amount,
            'remaining_amount' => $this->remaining_amount,
            'usage_percentage' => round($this->usage_percentage, 2),
            'alert_status' => $this->alert_status,
            'is_over_budget' => $this->is_over_budget,
            'is_current' => $this->is_current,
            'days_remaining' => $this->days_remaining,
            'period' => $this->period,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'category' => $this->category ? $this->category->only(['id', 'name', 'color']) : null,
            'alert_thresholds' => $this->alert_thresholds,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Scope: Active budgets only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Budgets for specific period
     */
    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope: Current budgets (active and within date range)
     */
    public function scopeCurrent($query)
    {
        $now = Carbon::now();
        return $query->active()
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }

    /**
     * Scope: Budgets by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Budgets for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Over budget
     */
    public function scopeOverBudget($query)
    {
        return $query->whereRaw('(SELECT COALESCE(SUM(expenses.amount), 0) FROM expenses WHERE expenses.user_id = budgets.user_id AND (budgets.category_id IS NULL OR expenses.category_id = budgets.category_id) AND expenses.date BETWEEN budgets.start_date AND budgets.end_date AND expenses.deleted_at IS NULL) > budgets.amount');
    }

    /**
     * Validation rules for budget creation/update
     */
    public static function validationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'period' => 'required|in:' . implode(',', self::PERIODS),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'alert_thresholds' => 'nullable|array',
            'alert_thresholds.warning' => 'nullable|numeric|min:0|max:100',
            'alert_thresholds.danger' => 'nullable|numeric|min:0|max:200',
        ];

        return $rules;
    }
}
