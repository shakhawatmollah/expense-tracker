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
            'expenses_count' => $this->expenses_count ?? 0,
            'total_amount' => $this->when(
                $this->relationLoaded('expenses') || array_key_exists('total_amount', $this->getAttributes()),
                fn() => number_format($this->total_amount ?? 0, 2)
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}