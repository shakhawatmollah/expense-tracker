<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'amount' => [
                'raw' => (float) $this->amount,
                'formatted' => '$' . number_format($this->amount, 2),
            ],

            // Calculated fields
            'spent_amount' => [
                'raw' => (float) $this->spent_amount,
                'formatted' => '$' . number_format($this->spent_amount, 2),
            ],
            'remaining_amount' => [
                'raw' => (float) $this->remaining_amount,
                'formatted' => '$' . number_format($this->remaining_amount, 2),
            ],
            'usage_percentage' => round($this->usage_percentage, 2),

            // Status and alerts
            'alert_status' => $this->alert_status,
            'is_over_budget' => $this->is_over_budget,
            'is_current' => $this->is_current,
            'is_active' => $this->is_active,

            // Period information
            'period' => [
                'type' => $this->period,
                'label' => $this->getPeriodLabel(),
                'start_date' => $this->start_date->toDateString(),
                'end_date' => $this->end_date->toDateString(),
                'days_remaining' => $this->days_remaining,
            ],

            // Alert configuration
            'alert_thresholds' => $this->alert_thresholds ?: [
                'warning' => 80,
                'danger' => 100,
            ],

            // Category relationship
            'category' => $this->whenLoaded('category', function () {
                return $this->category ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'color' => $this->category->color ?? '#6B7280',
                    'icon' => $this->category->icon ?? null,
                ] : null;
            }),

            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // UI helpers
            'progress' => [
                'percentage' => min(100, $this->usage_percentage),
                'status_color' => $this->getStatusColor(),
                'progress_bar_class' => $this->getProgressBarClass(),
            ],

            // Additional computed fields
            'daily_average' => $this->getDailyAverage(),
            'projected_total' => $this->getProjectedTotal(),
            'alerts' => $this->getAlerts(),
        ];
    }

    /**
     * Get a human-readable period label
     */
    private function getPeriodLabel(): string
    {
        switch ($this->period) {
            case 'weekly':
                return 'Weekly (' . $this->start_date->format('M j') . ' - ' . $this->end_date->format('M j, Y') . ')';
            case 'monthly':
                return 'Monthly (' . $this->start_date->format('M Y') . ')';
            case 'yearly':
                return 'Yearly (' . $this->start_date->format('Y') . ')';
            case 'custom':
                return 'Custom (' . $this->start_date->format('M j') . ' - ' . $this->end_date->format('M j, Y') . ')';
            default:
                return ucfirst($this->period);
        }
    }

    /**
     * Get status color for UI.
     */
    private function getStatusColor(): string
    {
        return match ($this->alert_status) {
            'danger' => '#EF4444',   // Red
            'warning' => '#F59E0B',  // Amber
            'safe' => '#10B981',     // Green
            default => '#6B7280',    // Gray
        };
    }

    /**
     * Get progress bar CSS class.
     */
    private function getProgressBarClass(): string
    {
        return match ($this->alert_status) {
            'danger' => 'bg-red-500',
            'warning' => 'bg-amber-500',
            'safe' => 'bg-green-500',
            default => 'bg-gray-500',
        };
    }

    /**
     * Calculate daily average spending
     */
    private function getDailyAverage(): float
    {
        $daysElapsed = max(1, $this->start_date->diffInDays(now()));

        return round($this->spent_amount / $daysElapsed, 2);
    }

    /**
     * Calculate projected total spending
     */
    private function getProjectedTotal(): float
    {
        $totalDays = $this->start_date->diffInDays($this->end_date);
        $daysElapsed = max(1, $this->start_date->diffInDays(now()));

        if ($daysElapsed >= $totalDays) {
            return (float) $this->spent_amount;
        }

        $dailyAverage = $this->getDailyAverage();

        return round($dailyAverage * $totalDays, 2);
    }

    /**
     * Get alerts for this budget.
     */
    private function getAlerts(): array
    {
        $alerts = [];

        if ($this->is_over_budget) {
            $overAmount = $this->spent_amount - $this->amount;
            $alerts[] = [
                'type' => 'over_budget',
                'message' => 'Over budget by $' . number_format($overAmount, 2),
                'severity' => 'high',
            ];
        } elseif ($this->alert_status === 'warning') {
            $alerts[] = [
                'type' => 'threshold_reached',
                'message' => "Reached {$this->usage_percentage}% of budget",
                'severity' => 'medium',
            ];
        }

        // Add time-based alerts for current budgets
        if ($this->is_current) {
            $daysLeft = $this->days_remaining;

            if ($daysLeft <= 7 && $daysLeft > 0 && $this->usage_percentage < 25) {
                $alerts[] = [
                    'type' => 'underutilized',
                    'message' => "Only {$this->usage_percentage}% used with {$daysLeft} days left",
                    'severity' => 'low',
                ];
            }
        }

        return $alerts;
    }
}
