<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\DashboardController;

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

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
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
});