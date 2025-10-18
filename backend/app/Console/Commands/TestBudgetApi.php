<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Budget;

class TestBudgetApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-budget-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test budget API and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Budget Database Check ===');
        
        // Check budget count
        $budgetCount = Budget::count();
        $this->info("Budget count: {$budgetCount}");
        
        if ($budgetCount > 0) {
            $budgets = Budget::with('user', 'category')->get();
            
            foreach ($budgets as $budget) {
                $this->info("ID: {$budget->id}");
                $this->info("Name: {$budget->name}");
                $this->info("Amount: {$budget->amount}");
                $this->info("Period: {$budget->period}");
                $this->info("User: {$budget->user->email}");
                $this->info("Category: " . ($budget->category ? $budget->category->name : 'None'));
                $this->info("Active: " . ($budget->is_active ? 'Yes' : 'No'));
                $this->info("Created: {$budget->created_at}");
                $this->info("---");
            }
        }
        
        // Show all users
        $this->info("\n=== All Users ===");
        $users = User::all();
        foreach ($users as $user) {
            $this->info("Email: {$user->email} (ID: {$user->id})");
        }
        
        // Test auth token creation
        $this->info("\n=== Auth Token Test ===");
        $user = User::first();
        if ($user) {
            $token = $user->createToken('test-token');
            $this->info("User: {$user->email}");
            $this->info("Token: {$token->plainTextToken}");
        } else {
            $this->error("No users found!");
        }
        
        return 0;
    }
}
