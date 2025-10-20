<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add performance indexes to expenses table
        Schema::table('expenses', function (Blueprint $table) {
            // Compound index for common queries
            $table->index(['user_id', 'date', 'category_id'], 'idx_expenses_user_date_category');
            $table->index(['user_id', 'amount'], 'idx_expenses_user_amount');
            $table->index(['user_id', 'date', 'amount'], 'idx_expenses_user_date_amount');
            
            // Index for date range queries
            $table->index(['date', 'user_id'], 'idx_expenses_date_user');
        });

        // Add performance indexes to budgets table  
        Schema::table('budgets', function (Blueprint $table) {
            // Index for active budgets lookup
            $table->index(['user_id', 'is_active', 'start_date', 'end_date'], 'idx_budgets_user_active_dates');
            $table->index(['category_id', 'user_id', 'is_active'], 'idx_budgets_category_user_active');
        });

        // Add performance indexes to categories table
        Schema::table('categories', function (Blueprint $table) {
            // Index for user categories lookup
            $table->index(['user_id', 'created_at'], 'idx_categories_user_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('idx_expenses_user_date_category');
            $table->dropIndex('idx_expenses_user_amount');
            $table->dropIndex('idx_expenses_user_date_amount');
            $table->dropIndex('idx_expenses_date_user');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('idx_budgets_user_active_dates');
            $table->dropIndex('idx_budgets_category_user_active');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_user_created');
        });
    }
};
