<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'insight_type',
        'title',
        'description',
        'data',
        'confidence_score',
        'is_read',
        'generated_at'
    ];

    protected $casts = [
        'data' => 'array',
        'confidence_score' => 'decimal:2',
        'is_read' => 'boolean',
        'generated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the insight
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread insights
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for specific insight type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('insight_type', $type);
    }

    /**
     * Scope for high confidence insights
     */
    public function scopeHighConfidence($query, float $threshold = 70.0)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    /**
     * Scope for filtering insights by period
     */
    public function scopeForPeriod($query, string $period)
    {
        $now = now();
        
        switch ($period) {
            case 'daily':
                return $query->whereDate('generated_at', $now->toDateString());
            case 'weekly':
                return $query->whereBetween('generated_at', [
                    $now->startOfWeek(),
                    $now->endOfWeek()
                ]);
            case 'monthly':
                return $query->whereMonth('generated_at', $now->month)
                           ->whereYear('generated_at', $now->year);
            case 'quarterly':
                return $query->whereBetween('generated_at', [
                    $now->startOfQuarter(),
                    $now->endOfQuarter()
                ]);
            case 'yearly':
                return $query->whereYear('generated_at', $now->year);
            default:
                return $query; // No filtering for unknown periods
        }
    }

    /**
     * Mark insight as read
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }
}