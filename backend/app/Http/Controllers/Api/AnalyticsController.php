<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Models\UserInsight;
use App\Models\SpendingPattern;
use App\Models\FinancialHealthScore;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    private AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get comprehensive analytics dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 'monthly');

        try {
            $analytics = $this->analyticsService->generateUserAnalytics($user, $period);
            
            return response()->json([
                'success' => true,
                'data' => $analytics,
                'period' => $period
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get spending patterns
     */
    public function patterns(Request $request): JsonResponse
    {
        $user = Auth::user();
        $type = $request->get('type');
        
        $query = SpendingPattern::where('user_id', $user->id)->active();
        
        if ($type) {
            $query->ofType($type);
        }
        
        $patterns = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $patterns,
            'total' => $patterns->count()
        ]);
    }

    /**
     * Get financial health score
     */
    public function financialHealth(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 'monthly');
        
        try {
            $healthData = $this->analyticsService->calculateFinancialHealth($user, $period);
            
            // Get historical scores (last 12 months)
            $historicalScores = FinancialHealthScore::where('user_id', $user->id)
                ->orderBy('score_date', 'desc')
                ->limit(12)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'current' => $healthData,
                    'history' => $historicalScores
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate financial health',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user insights
     */
    public function insights(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 'monthly');
        $type = $request->get('type');
        
        $query = UserInsight::where('user_id', $user->id);
        
        if ($period) {
            $query->forPeriod($period);
        }
        
        if ($type) {
            $query->ofType($type);
        }
        
        $insights = $query->latest()->get();
        
        return response()->json([
            'success' => true,
            'data' => $insights,
            'total' => $insights->count()
        ]);
    }

    /**
     * Get spending forecasts
     */
    public function forecasts(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 'monthly');
        
        try {
            $forecasts = $this->analyticsService->generateForecasts($user, $period);
            
            return response()->json([
                'success' => true,
                'data' => $forecasts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate forecasts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommendations
     */
    public function recommendations(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 'monthly');
        
        try {
            $recommendations = $this->analyticsService->generateRecommendations($user, $period);
            
            return response()->json([
                'success' => true,
                'data' => $recommendations,
                'total' => count($recommendations)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh analytics (force regeneration)
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 'monthly');
        
        try {
            // Clear cache for this user
            $cacheKey = "user_analytics_{$user->id}_{$period}";
            \App\Models\AnalyticsCache::where('user_id', $user->id)
                ->where('cache_key', $cacheKey)
                ->delete();
            
            // Generate fresh analytics
            $analytics = $this->analyticsService->generateUserAnalytics($user, $period);
            
            return response()->json([
                'success' => true,
                'message' => 'Analytics refreshed successfully',
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get spending trends
     */
    public function trends(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 'monthly');
        $category_id = $request->get('category_id');
        
        try {
            // This would be expanded to get detailed trend analysis
            $trends = [
                'period' => $period,
                'message' => 'Trends endpoint - to be implemented with detailed trend analysis'
            ];
            
            return response()->json([
                'success' => true,
                'data' => $trends
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get trends',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
