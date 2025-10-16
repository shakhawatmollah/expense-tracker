<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        foreach ($users->take(4) as $user) { // Limit to first 4 users for demo
            $categories = Category::where('user_id', $user->id)->get();
            
            if ($categories->isEmpty()) {
                $this->command->info("No categories found for user {$user->email}. Please run CategorySeeder first.");
                continue;
            }

            // Generate expenses for the last 6 months
            for ($monthsBack = 5; $monthsBack >= 0; $monthsBack--) {
                $startDate = Carbon::now()->subMonths($monthsBack)->startOfMonth();
                $endDate = Carbon::now()->subMonths($monthsBack)->endOfMonth();
                
                // Generate 15-30 random expenses per month
                $expenseCount = rand(15, 30);
                
                for ($i = 0; $i < $expenseCount; $i++) {
                    $category = $categories->random();
                    $randomDate = Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));
                    
                    $expenses = $this->getExpensesByCategory($category->name);
                    $randomExpense = $expenses[array_rand($expenses)];
                    
                    Expense::create([
                        'user_id' => $user->id,
                        'category_id' => $category->id,
                        'description' => $randomExpense['description'],
                        'amount' => $this->randomAmount($randomExpense['min'], $randomExpense['max']),
                        'date' => $randomDate,
                        'created_at' => $randomDate,
                        'updated_at' => $randomDate,
                    ]);
                }
            }
        }
    }

    /**
     * Get realistic expense data by category
     */
    private function getExpensesByCategory(string $categoryName): array
    {
        $expenses = [
            'Food & Dining' => [
                ['description' => 'Lunch at McDonald\'s', 'min' => 8, 'max' => 15],
                ['description' => 'Dinner at Italian Restaurant', 'min' => 25, 'max' => 60],
                ['description' => 'Grocery Shopping', 'min' => 45, 'max' => 120],
                ['description' => 'Coffee at Starbucks', 'min' => 4, 'max' => 8],
                ['description' => 'Pizza Delivery', 'min' => 15, 'max' => 30],
                ['description' => 'Fast Food Breakfast', 'min' => 6, 'max' => 12],
                ['description' => 'Weekly Groceries', 'min' => 80, 'max' => 150],
                ['description' => 'Restaurant Date Night', 'min' => 40, 'max' => 100],
            ],
            'Transportation' => [
                ['description' => 'Gas Fill-up', 'min' => 30, 'max' => 60],
                ['description' => 'Uber Ride', 'min' => 8, 'max' => 25],
                ['description' => 'Public Transit Pass', 'min' => 15, 'max' => 30],
                ['description' => 'Parking Fee', 'min' => 5, 'max' => 20],
                ['description' => 'Car Maintenance', 'min' => 50, 'max' => 200],
                ['description' => 'Taxi Fare', 'min' => 10, 'max' => 35],
                ['description' => 'Metro Card Refill', 'min' => 20, 'max' => 40],
            ],
            'Shopping' => [
                ['description' => 'Amazon Purchase', 'min' => 15, 'max' => 80],
                ['description' => 'Clothing Shopping', 'min' => 25, 'max' => 150],
                ['description' => 'Electronics Store', 'min' => 50, 'max' => 300],
                ['description' => 'Home Improvement Store', 'min' => 20, 'max' => 100],
                ['description' => 'Pharmacy Items', 'min' => 10, 'max' => 40],
                ['description' => 'Gift Purchase', 'min' => 15, 'max' => 75],
            ],
            'Entertainment' => [
                ['description' => 'Movie Tickets', 'min' => 12, 'max' => 25],
                ['description' => 'Netflix Subscription', 'min' => 10, 'max' => 20],
                ['description' => 'Concert Tickets', 'min' => 30, 'max' => 120],
                ['description' => 'Video Game Purchase', 'min' => 20, 'max' => 60],
                ['description' => 'Streaming Service', 'min' => 8, 'max' => 15],
                ['description' => 'Bowling Night', 'min' => 15, 'max' => 40],
            ],
            'Bills & Utilities' => [
                ['description' => 'Electric Bill', 'min' => 60, 'max' => 150],
                ['description' => 'Internet Bill', 'min' => 40, 'max' => 80],
                ['description' => 'Phone Bill', 'min' => 30, 'max' => 70],
                ['description' => 'Water Bill', 'min' => 25, 'max' => 60],
                ['description' => 'Rent Payment', 'min' => 800, 'max' => 1500],
                ['description' => 'Insurance Premium', 'min' => 100, 'max' => 250],
            ],
            'Healthcare' => [
                ['description' => 'Doctor Visit', 'min' => 50, 'max' => 200],
                ['description' => 'Pharmacy Prescription', 'min' => 10, 'max' => 50],
                ['description' => 'Dental Cleaning', 'min' => 80, 'max' => 150],
                ['description' => 'Eye Exam', 'min' => 100, 'max' => 200],
                ['description' => 'Health Insurance Co-pay', 'min' => 20, 'max' => 50],
            ],
            'Education' => [
                ['description' => 'Online Course', 'min' => 30, 'max' => 200],
                ['description' => 'Textbook Purchase', 'min' => 40, 'max' => 150],
                ['description' => 'Workshop Fee', 'min' => 50, 'max' => 300],
                ['description' => 'Certification Exam', 'min' => 100, 'max' => 250],
            ],
            'Travel' => [
                ['description' => 'Flight Booking', 'min' => 200, 'max' => 800],
                ['description' => 'Hotel Stay', 'min' => 80, 'max' => 300],
                ['description' => 'Car Rental', 'min' => 50, 'max' => 150],
                ['description' => 'Travel Insurance', 'min' => 20, 'max' => 80],
                ['description' => 'Vacation Expenses', 'min' => 100, 'max' => 500],
            ],
            'Personal Care' => [
                ['description' => 'Haircut', 'min' => 20, 'max' => 60],
                ['description' => 'Gym Membership', 'min' => 30, 'max' => 80],
                ['description' => 'Spa Treatment', 'min' => 50, 'max' => 150],
                ['description' => 'Cosmetics', 'min' => 15, 'max' => 50],
                ['description' => 'Personal Trainer', 'min' => 40, 'max' => 100],
            ],
            'Other' => [
                ['description' => 'Miscellaneous Expense', 'min' => 10, 'max' => 50],
                ['description' => 'Charity Donation', 'min' => 20, 'max' => 100],
                ['description' => 'Bank Fee', 'min' => 5, 'max' => 25],
                ['description' => 'ATM Fee', 'min' => 2, 'max' => 5],
                ['description' => 'Emergency Expense', 'min' => 30, 'max' => 200],
            ],
        ];

        return $expenses[$categoryName] ?? $expenses['Other'];
    }

    /**
     * Generate random amount with some variation
     */
    private function randomAmount(float $min, float $max): float
    {
        return round(rand($min * 100, $max * 100) / 100, 2);
    }
}
