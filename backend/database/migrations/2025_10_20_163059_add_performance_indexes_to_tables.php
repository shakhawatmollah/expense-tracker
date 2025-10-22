<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
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
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        // Helper function to check if index exists
        $indexExists = function ($table, $indexName) use ($connection, $database) {
            $result = $connection->select(
                'SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $table, $indexName]
            );

            return $result[0]->count > 0;
        };

        Schema::table('expenses', function (Blueprint $table) use ($indexExists) {
            if ($indexExists('expenses', 'idx_expenses_user_date_category')) {
                $table->dropIndex('idx_expenses_user_date_category');
            }
            if ($indexExists('expenses', 'idx_expenses_user_amount')) {
                $table->dropIndex('idx_expenses_user_amount');
            }
            if ($indexExists('expenses', 'idx_expenses_user_date_amount')) {
                $table->dropIndex('idx_expenses_user_date_amount');
            }
            if ($indexExists('expenses', 'idx_expenses_date_user')) {
                $table->dropIndex('idx_expenses_date_user');
            }
        });

        Schema::table('budgets', function (Blueprint $table) use ($indexExists) {
            // Drop the simple index first
            if ($indexExists('budgets', 'idx_budgets_user_active_dates')) {
                $table->dropIndex('idx_budgets_user_active_dates');
            }

            // For idx_budgets_category_user_active, we need to temporarily drop
            // the foreign key constraint, drop the index, then recreate the constraint
            if ($indexExists('budgets', 'idx_budgets_category_user_active')) {
                // Drop the foreign key constraint first
                $table->dropForeign(['category_id']);

                // Now we can drop the index
                $table->dropIndex('idx_budgets_category_user_active');

                // Recreate the foreign key constraint
                $table->foreign('category_id')
                    ->references('id')
                    ->on('categories')
                    ->onDelete('cascade');
            }
        });

        Schema::table('categories', function (Blueprint $table) use ($indexExists) {
            if ($indexExists('categories', 'idx_categories_user_created')) {
                $table->dropIndex('idx_categories_user_created');
            }
        });
    }
};
