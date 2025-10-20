<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'notes' => $this->notes,
            
            // Amount - provide both backward compatibility and enhanced format
            'amount' => (float) $this->amount,
            'amount_formatted' => [
                'raw' => (float) $this->amount,
                'formatted' => '$' . number_format($this->amount, 2),
            ],
            
            // Date information
            'date' => $this->date?->format('Y-m-d'),
            'date_formatted' => $this->date?->format('M j, Y'),
            'date_relative' => $this->date?->diffForHumans(),
            
            // Category relationship
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'color' => $this->category->color ?? '#6B7280',
                    'icon' => $this->category->icon ?? 'fas fa-tag',
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // UI helpers
            'display' => [
                'summary' => $this->description . ' - $' . number_format($this->amount, 2),
                'category_badge_class' => 'bg-' . ($this->category?->color ?? 'gray') . '-100 text-' . ($this->category?->color ?? 'gray') . '-800',
            ],
        ];
    }
}