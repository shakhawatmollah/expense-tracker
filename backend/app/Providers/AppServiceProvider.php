<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prevent lazy loading in all environments except production
        // This helps catch N+1 query issues during development
        Model::preventLazyLoading(!app()->isProduction());
        
        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
        
        // Prevent accessing missing attributes
        Model::preventAccessingMissingAttributes(!app()->isProduction());
    }
}
