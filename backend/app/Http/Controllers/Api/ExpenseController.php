<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $expenses = $this->expenseService->getUserExpenses(
            $request->user()->id,
            $request->all()
        );

        return response()->json([
            'data' => ExpenseResource::collection($expenses)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        try {
            $expense = $this->expenseService->create(
                array_merge($request->validated(), ['user_id' => $request->user()->id])
            );

            return response()->json([
                'message' => 'Expense created successfully',
                'data' => new ExpenseResource($expense)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create expense',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $expense = $this->expenseService->findForUser($id, $request->user()->id);

            return response()->json([
                'data' => new ExpenseResource($expense)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Expense not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, string $id): JsonResponse
    {
        try {
            $expense = $this->expenseService->update($id, $request->user()->id, $request->validated());

            return response()->json([
                'message' => 'Expense updated successfully',
                'data' => new ExpenseResource($expense)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update expense',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->expenseService->delete($id, $request->user()->id);

            return response()->json([
                'message' => 'Expense deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete expense',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get expenses by date range
     */
    public function getByDateRange(Request $request): JsonResponse
    {
        $expenses = $this->expenseService->getByDateRange(
            $request->user()->id,
            $request->get('start_date'),
            $request->get('end_date')
        );

        return response()->json([
            'data' => ExpenseResource::collection($expenses)
        ]);
    }

    /**
     * Search expenses
     */
    public function search(Request $request): JsonResponse
    {
        $expenses = $this->expenseService->search(
            $request->user()->id,
            $request->get('query')
        );

        return response()->json([
            'data' => ExpenseResource::collection($expenses)
        ]);
    }
}