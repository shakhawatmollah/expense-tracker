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
        // User insights table for storing generated insights
        Schema::create('user_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('insight_type', [
                'spending_pattern', 'forecast', 'health_score',
                'budget_alert', 'trend_analysis', 'recommendation',
            ]);
            $table->string('title');
            $table->text('description');
            $table->json('data'); // Store insight data as JSON
            $table->decimal('confidence_score', 5, 2)->nullable(); // 0-100 confidence level
            $table->boolean('is_read')->default(false);
            $table->timestamp('generated_at');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'insight_type']);
            $table->index(['user_id', 'is_read']);
            $table->index(['generated_at']);
        });

        // Spending patterns table for detected patterns
        Schema::create('spending_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('pattern_type', [
                'daily_recurring', 'weekly_recurring', 'monthly_recurring',
                'seasonal', 'category_spike', 'anomaly',
            ]);
            $table->string('pattern_name');
            $table->text('description');
            $table->json('pattern_data'); // Store pattern details
            $table->decimal('frequency', 8, 2); // How often pattern occurs
            $table->decimal('confidence_score', 5, 2); // Pattern reliability
            $table->decimal('impact_amount', 10, 2)->nullable(); // Financial impact
            $table->date('first_detected');
            $table->date('last_detected');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'pattern_type']);
            $table->index(['user_id', 'is_active']);
            $table->index(['confidence_score']);
        });

        // Financial health scores history
        Schema::create('financial_health_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('overall_score', 5, 2); // 0-100 overall health score
            $table->decimal('budget_adherence_score', 5, 2); // Budget discipline
            $table->decimal('spending_consistency_score', 5, 2); // Spending stability
            $table->decimal('savings_rate_score', 5, 2); // Savings performance
            $table->decimal('category_balance_score', 5, 2); // Category diversification
            $table->json('score_breakdown'); // Detailed scoring data
            $table->json('recommendations'); // Improvement suggestions
            $table->date('score_date');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'score_date']);
            $table->index(['overall_score']);
        });

        // Analytics cache for expensive calculations
        Schema::create('analytics_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('cache_key');
            $table->json('cached_data');
            $table->timestamp('expires_at');
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'cache_key']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_cache');
        Schema::dropIfExists('financial_health_scores');
        Schema::dropIfExists('spending_patterns');
        Schema::dropIfExists('user_insights');
    }
};
