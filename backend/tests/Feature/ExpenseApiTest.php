<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpenseApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for testing
        $this->user = User::factory()->create();
        
        // Create categories
        $this->category = Category::factory()->create();
    }

    public function test_can_list_expenses()
    {
        Sanctum::actingAs($this->user);
        
        // Create some expenses
        Expense::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id
        ]);
        
        $response = $this->getJson('/api/v1/expenses');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'description',
                            'amount',
                            'date',
                            'category',
                            'created_at'
                        ]
                    ],
                    'meta'
                ]);
    }

    public function test_can_create_expense()
    {
        Sanctum::actingAs($this->user);
        
        $expenseData = [
            'description' => 'Test Expense',
            'amount' => 100.50,
            'date' => '2025-10-21',
            'category_id' => $this->category->id
        ];
        
        $response = $this->postJson('/api/v1/expenses', $expenseData);
        
        $response->assertStatus(201)
                ->assertJsonFragment([
                    'description' => 'Test Expense',
                    'amount' => 100.50
                ]);
        
        $this->assertDatabaseHas('expenses', [
            'description' => 'Test Expense',
            'amount' => 100.50,
            'user_id' => $this->user->id
        ]);
    }

    public function test_can_update_expense()
    {
        Sanctum::actingAs($this->user);
        
        $expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id
        ]);
        
        $updateData = [
            'description' => 'Updated Expense',
            'amount' => 200.75
        ];
        
        $response = $this->putJson("/api/v1/expenses/{$expense->id}", $updateData);
        
        $response->assertStatus(200)
                ->assertJsonFragment([
                    'description' => 'Updated Expense',
                    'amount' => 200.75
                ]);
    }

    public function test_can_delete_expense()
    {
        Sanctum::actingAs($this->user);
        
        $expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id
        ]);
        
        $response = $this->deleteJson("/api/v1/expenses/{$expense->id}");
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_cannot_access_other_users_expenses()
    {
        $otherUser = User::factory()->create();
        $expense = Expense::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $this->category->id
        ]);
        
        Sanctum::actingAs($this->user);
        
        $response = $this->getJson("/api/v1/expenses/{$expense->id}");
        $response->assertStatus(404);
    }

    public function test_expense_validation_rules()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/v1/expenses', []);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['description', 'amount', 'date', 'category_id']);
    }
}