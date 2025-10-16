<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'user_id' => $this->user_id,
            'expenses_count' => $this->when(
                $this->relationLoaded('expenses'),
                $this->expenses_count ?? $this->expenses?->count()
            ),
            'total_amount' => $this->when(
                isset($this->total_amount),
                number_format($this->total_amount, 2)
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}