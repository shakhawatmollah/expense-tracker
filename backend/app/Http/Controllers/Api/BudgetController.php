<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BudgetController extends Controller
{
    protected BudgetService $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    /**
     * Display a listing of budgets.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['period', 'category_id', 'is_active', 'status', 'search']);
        $perPage = $request->get('per_page', 15);

        $budgets = $this->budgetService->getAllBudgets(
            $request->user()->id,
            $filters,
            $perPage
        );

        return response()->json([
            'success' => true,
            'data' => BudgetResource::collection($budgets),
            'meta' => [
                'current_page' => $budgets->currentPage(),
                'last_page' => $budgets->lastPage(),
                'per_page' => $budgets->perPage(),
                'total' => $budgets->total(),
            ],
        ]);
    }

    /**
     * Store a newly created budget.
     */
    public function store(BudgetRequest $request): JsonResponse
    {
        try {
            $budget = $this->budgetService->createBudget(
                $request->validated(),
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Budget created successfully',
                'data' => new BudgetResource($budget),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified budget.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $budget = $this->budgetService->getBudgetById((int)$id, $request->user()->id);

        if (!$budget) {
            return response()->json([
                'success' => false,
                'message' => 'Budget not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new BudgetResource($budget),
        ]);
    }

    /**
     * Update the specified budget.
     */
    public function update(BudgetRequest $request, string $id): JsonResponse
    {
        try {
            $updated = $this->budgetService->updateBudget(
                (int)$id,
                $request->validated(),
                $request->user()->id
            );

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Budget not found',
                ], 404);
            }

            $budget = $this->budgetService->getBudgetById((int)$id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Budget updated successfully',
                'data' => new BudgetResource($budget),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove the specified budget.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $deleted = $this->budgetService->deleteBudget((int)$id, $request->user()->id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Budget not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Budget deleted successfully',
        ]);
    }

    /**
     * Get current period budgets.
     */
    public function current(Request $request): JsonResponse
    {
        $budgets = $this->budgetService->getCurrentBudgets($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => BudgetResource::collection($budgets),
            'count' => $budgets->count(),
        ]);
    }

    /**
     * Get budget summary with insights.
     */
    public function summary(Request $request): JsonResponse
    {
        $summary = $this->budgetService->getBudgetSummary($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get budget alerts (over budget and near threshold).
     */
    public function alerts(Request $request): JsonResponse
    {
        $alerts = $this->budgetService->getBudgetAlerts($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $alerts,
            'count' => count($alerts),
        ]);
    }

    /**
     * Get budgets by period.
     */
    public function byPeriod(Request $request): JsonResponse
    {
        $period = $request->get('period');

        if (!$period || !in_array($period, Budget::PERIODS)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing period parameter',
            ], 400);
        }

        $budgets = $this->budgetService->getBudgetsByPeriod(
            $request->user()->id,
            $period
        );

        return response()->json([
            'success' => true,
            'data' => BudgetResource::collection($budgets),
            'period' => $period,
            'count' => $budgets->count(),
        ]);
    }

    /**
     * Search budgets with filters.
     */
    public function search(Request $request): JsonResponse
    {
        $filters = $request->only([
            'period', 'category_id', 'status', 'amount_min', 'amount_max', 'name'
        ]);

        $budgets = $this->budgetService->searchBudgets($request->user()->id, $filters);

        return response()->json([
            'success' => true,
            'data' => BudgetResource::collection($budgets),
            'filters' => $filters,
            'count' => $budgets->count(),
        ]);
    }

    /**
     * Get budgets for a specific category.
     */
    public function byCategory(Request $request, string $categoryId): JsonResponse
    {
        try {
            $budgets = $this->budgetService->getBudgetsForCategory($request->user()->id, (int)$categoryId);

            return response()->json([
                'success' => true,
                'data' => BudgetResource::collection($budgets),
                'category_id' => $categoryId,
                'count' => $budgets->count(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid category',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Recalculate budget spending (useful after expense changes)
     */
    public function recalculate(Request $request): JsonResponse
    {
        $budgetId = $request->get('budget_id');
        
        // For now, we'll just touch the budgets to refresh any cached values
        // since spent_amount is calculated dynamically
        if ($budgetId) {
            $budget = $this->budgetService->getBudgetById($budgetId, $request->user()->id);
            if ($budget) {
                $budget->touch();
                $count = 1;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Budget not found',
                ], 404);
            }
        } else {
            // Get all budgets and touch them
            $budgets = $this->budgetService->getCurrentBudgets($request->user()->id);
            $count = 0;
            foreach ($budgets as $budget) {
                $budget->touch();
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Recalculated spending for {$count} budget(s)",
            'updated_count' => $count,
        ]);
    }

    /**
     * Get budget analytics.
     */
    public function analytics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'monthly');

        $analytics = $this->budgetService->getBudgetAnalytics(
            $request->user()->id,
            $period
        );

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Duplicate budget to a new period.
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'period' => 'sometimes|in:' . implode(',', Budget::PERIODS),
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
        ]);

        try {
            $duplicatedBudget = $this->budgetService->duplicateBudget(
                (int)$id,
                $request->user()->id,
                $request->only(['name', 'period', 'start_date', 'end_date'])
            );

            if (!$duplicatedBudget) {
                return response()->json([
                    'success' => false,
                    'message' => 'Budget not found or could not be duplicated',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Budget duplicated successfully',
                'data' => new BudgetResource($duplicatedBudget),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Create default budgets for all categories.
     */
    public function createDefaults(Request $request): JsonResponse
    {
        $budgets = $this->budgetService->createDefaultBudgets($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => count($budgets) . ' default budgets created',
            'data' => BudgetResource::collection($budgets),
            'count' => count($budgets),
        ], 201);
    }

    /**
     * Get available budget periods.
     */
    public function periods(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Budget::getPeriods(),
        ]);
    }
}