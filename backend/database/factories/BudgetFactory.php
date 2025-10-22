<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $period = $this->faker->randomElement(['monthly', 'weekly', 'quarterly', 'yearly']);
        $startDate = $this->faker->dateTimeBetween('-2 months', 'now');

        // Calculate end date based on period
        $endDate = match($period) {
            'weekly' => (clone $startDate)->modify('+1 week'),
            'monthly' => (clone $startDate)->modify('+1 month'),
            'quarterly' => (clone $startDate)->modify('+3 months'),
            'yearly' => (clone $startDate)->modify('+1 year'),
        };

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'name' => $this->faker->words(3, true) . ' Budget',
            'amount' => $this->faker->randomFloat(2, 100, 2000),
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
            'alert_thresholds' => json_encode([
                'warning' => $this->faker->numberBetween(50, 70),
                'danger' => $this->faker->numberBetween(80, 90),
            ]),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the budget belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the budget belongs to a specific category.
     */
    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Indicate that the budget is monthly.
     */
    public function monthly(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = now()->startOfMonth();

            return [
                'period' => 'monthly',
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->endOfMonth(),
            ];
        });
    }

    /**
     * Indicate that the budget is weekly.
     */
    public function weekly(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = now()->startOfWeek();

            return [
                'period' => 'weekly',
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->endOfWeek(),
            ];
        });
    }

    /**
     * Indicate that the budget is yearly.
     */
    public function yearly(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = now()->startOfYear();

            return [
                'period' => 'yearly',
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->endOfYear(),
            ];
        });
    }

    /**
     * Indicate that the budget has a specific amount.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Indicate that alerts are enabled.
     */
    public function withAlerts(int $warningThreshold = 70, int $dangerThreshold = 90): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_thresholds' => json_encode([
                'warning' => $warningThreshold,
                'danger' => $dangerThreshold,
            ]),
        ]);
    }
}
