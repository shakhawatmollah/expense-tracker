<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates the complete budgets table with all required columns
     * for the Budget Management System frontend integration.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            
            // Foreign key relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            
            // Budget details
            $table->string('name'); // Budget name (e.g., "Monthly Food Budget", "Vacation Fund")
            $table->decimal('amount', 10, 2); // Budget amount
            $table->enum('period', ['weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            
            // Date range for budget (replaces year/month/week approach)
            $table->date('start_date'); // Budget start date
            $table->date('end_date'); // Budget end date
            
            // Status and configuration
            $table->boolean('is_active')->default(true);
            
            // Alert thresholds as JSON (to match Budget model expectations)
            $table->json('alert_thresholds')->nullable(); // JSON column for alert thresholds
            
            // Spending tracking (calculated fields, not stored)
            // Note: spent_amount and remaining_amount are calculated dynamically in the model
            
            // Additional information
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'is_active'], 'budgets_user_active_index');
            $table->index(['user_id', 'period'], 'budgets_user_period_index');
            $table->index(['user_id', 'category_id'], 'budgets_user_category_index');
            $table->index(['start_date', 'end_date'], 'budgets_date_range_index');
            $table->index(['is_active', 'start_date', 'end_date'], 'budgets_active_date_index');
            
            // Unique constraint to prevent overlapping budgets for same category and period
            $table->unique(['user_id', 'category_id', 'start_date', 'end_date'], 'unique_budget_period');

            // Soft deletes for budget records
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
