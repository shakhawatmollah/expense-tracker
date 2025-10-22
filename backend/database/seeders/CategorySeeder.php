<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users to create default categories for each
        $users = User::all();

        $defaultCategories = [
            ['name' => 'Food & Dining', 'color' => '#EF4444', 'description' => 'Restaurants, groceries, and food delivery'],
            ['name' => 'Transportation', 'color' => '#3B82F6', 'description' => 'Gas, public transit, taxi, car maintenance'],
            ['name' => 'Shopping', 'color' => '#10B981', 'description' => 'Clothing, electronics, and general shopping'],
            ['name' => 'Entertainment', 'color' => '#8B5CF6', 'description' => 'Movies, games, subscriptions, and leisure'],
            ['name' => 'Bills & Utilities', 'color' => '#F59E0B', 'description' => 'Electricity, water, internet, phone bills'],
            ['name' => 'Healthcare', 'color' => '#EC4899', 'description' => 'Medical expenses, pharmacy, insurance'],
            ['name' => 'Education', 'color' => '#6366F1', 'description' => 'Books, courses, tuition fees'],
            ['name' => 'Travel', 'color' => '#14B8A6', 'description' => 'Flights, hotels, vacation expenses'],
            ['name' => 'Personal Care', 'color' => '#F97316', 'description' => 'Haircuts, cosmetics, gym membership'],
            ['name' => 'Other', 'color' => '#6B7280', 'description' => 'Miscellaneous expenses'],
        ];

        foreach ($users as $user) {
            foreach ($defaultCategories as $categoryData) {
                Category::firstOrCreate(
                    [
                        'name' => $categoryData['name'],
                        'user_id' => $user->id,
                    ],
                    [
                        'color' => $categoryData['color'],
                        'description' => $categoryData['description'],
                    ]
                );
            }
        }
    }
}
