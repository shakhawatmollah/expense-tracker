<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ExportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes (unversioned) with rate limiting
Route::prefix('auth')->group(function () {
    // Strict rate limiting for authentication endpoints
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// API Version 1 routes
Route::prefix('v1')->group(function () {
    // Protected routes with rate limiting
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Debug route for testing category counts
    Route::get('debug/categories', function (Request $request) {
        $categories = App\Models\Category::where('user_id', $request->user()->id)
            ->withCount('expenses')
            ->get();
        
        return response()->json([
            'categories' => $categories,
            'count' => $categories->count()
        ]);
    });
    
    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('monthly-summary', [DashboardController::class, 'monthlySummary']);
        Route::get('yearly-summary', [DashboardController::class, 'yearlySummary']);
        Route::get('trends', [DashboardController::class, 'trends']);
        Route::get('daily-spending', [DashboardController::class, 'dailySpending']);
    });
    
    // Category routes
    Route::apiResource('categories', CategoryController::class);
    
    // Expense routes
    Route::apiResource('expenses', ExpenseController::class);
    Route::get('expenses/search', [ExpenseController::class, 'search']);
    Route::get('expenses/date-range', [ExpenseController::class, 'getByDateRange']);
    
    // Budget routes - Custom routes MUST come before resource routes
    Route::prefix('budgets')->group(function () {
        Route::get('/current', [BudgetController::class, 'current']);
        Route::get('/summary', [BudgetController::class, 'summary']);
        Route::get('/alerts', [BudgetController::class, 'alerts']);
        Route::get('/analytics', [BudgetController::class, 'analytics']);
        Route::get('/periods', [BudgetController::class, 'periods']);
        Route::get('/by-period', [BudgetController::class, 'byPeriod']);
        Route::get('/search', [BudgetController::class, 'search']);
        Route::get('/category/{categoryId}', [BudgetController::class, 'byCategory']);
        Route::post('/recalculate', [BudgetController::class, 'recalculate']);
        Route::post('/create-defaults', [BudgetController::class, 'createDefaults']);
        Route::post('/{id}/duplicate', [BudgetController::class, 'duplicate']);
    });
    Route::apiResource('budgets', BudgetController::class);
    
    // Analytics routes
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('/patterns', [AnalyticsController::class, 'patterns']);
        Route::get('/financial-health', [AnalyticsController::class, 'financialHealth']);
        Route::get('/insights', [AnalyticsController::class, 'insights']);
        Route::get('/forecasts', [AnalyticsController::class, 'forecasts']);
        Route::get('/recommendations', [AnalyticsController::class, 'recommendations']);
        Route::get('/trends', [AnalyticsController::class, 'trends']);
        Route::post('/refresh', [AnalyticsController::class, 'refresh']);
    });
    
    // Export routes with stricter rate limiting
    Route::prefix('export')->middleware('throttle:10,60')->group(function () {
        Route::get('/expenses', [ExportController::class, 'exportExpenses']);
        Route::get('/categories', [ExportController::class, 'exportCategories']);
        Route::get('/budgets', [ExportController::class, 'exportBudgets']);
        Route::get('/financial-report', [ExportController::class, 'exportFinancialReport']);
        Route::get('/history', [ExportController::class, 'history']);
    });
});
});