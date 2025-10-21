<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        App\Providers\RepositoryServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Enable CORS for API routes
        $middleware->api([
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\SanitizeInput::class,
        ]);
        
        // Register custom middleware aliases
        $middleware->alias([
            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'sanitize' => \App\Http\Middleware\SanitizeInput::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
