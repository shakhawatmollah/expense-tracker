<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            ['name' => 'Food & Dining', 'color' => '#FF6B6B'],
            ['name' => 'Transportation', 'color' => '#4ECDC4'],
            ['name' => 'Shopping', 'color' => '#45B7D1'],
            ['name' => 'Entertainment', 'color' => '#96CEB4'],
            ['name' => 'Bills & Utilities', 'color' => '#FFEAA7'],
            ['name' => 'Healthcare', 'color' => '#DFE6E9'],
            ['name' => 'Education', 'color' => '#74B9FF'],
            ['name' => 'Travel', 'color' => '#A29BFE'],
            ['name' => 'Other', 'color' => '#B2BEC3'],
        ];

        $category = $this->faker->randomElement($categories);

        return [
            'user_id' => User::factory(),
            'name' => $category['name'],
            'color' => $category['color'],
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the category belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
