<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Get dashboard statistics
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        $data = $this->dashboardService->getDashboardOverview($userId);

        return response()->json(['data' => $data]);
    }

    /**
     * Get monthly summary
     */
    public function monthlySummary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $period = $request->get('period', 'current_month');

        $summary = $this->dashboardService->getCategorySpendingAnalysis($userId, $period);

        return response()->json(['data' => $summary]);
    }

    /**
     * Get yearly summary
     */
    public function yearlySummary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $summary = $this->dashboardService->getSpendingStatistics($userId);

        return response()->json(['data' => $summary]);
    }

    /**
     * Get expense trends
     */
    public function trends(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $months = $request->get('months', 6);

        $trends = $this->dashboardService->getMonthlySpendingTrends($userId, $months);

        return response()->json(['data' => $trends]);
    }

    /**
     * Get daily spending for current month
     */
    public function dailySpending(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $dailySpending = $this->dashboardService->getDailySpendingCurrentMonth($userId);

        return response()->json(['data' => $dailySpending]);
    }
}