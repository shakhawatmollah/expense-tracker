<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpendingPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pattern_type',
        'pattern_name',
        'description',
        'pattern_data',
        'frequency',
        'confidence_score',
        'impact_amount',
        'first_detected',
        'last_detected',
        'is_active'
    ];

    protected $casts = [
        'pattern_data' => 'array',
        'frequency' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'impact_amount' => 'decimal:2',
        'first_detected' => 'date',
        'last_detected' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user that owns the pattern
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active patterns
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific pattern type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('pattern_type', $type);
    }

    /**
     * Scope for high confidence patterns
     */
    public function scopeHighConfidence($query, float $threshold = 80.0)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    /**
     * Check if pattern is recurring type
     */
    public function isRecurring(): bool
    {
        return in_array($this->pattern_type, [
            'daily_recurring',
            'weekly_recurring', 
            'monthly_recurring'
        ]);
    }

    /**
     * Get pattern frequency description
     */
    public function getFrequencyDescription(): string
    {
        return match($this->pattern_type) {
            'daily_recurring' => "Every {$this->frequency} days",
            'weekly_recurring' => "Every {$this->frequency} weeks", 
            'monthly_recurring' => "Every {$this->frequency} months",
            'seasonal' => "Seasonal pattern",
            'category_spike' => "Category spending spike",
            'anomaly' => "Unusual spending pattern",
            default => "Unknown pattern"
        };
    }
}