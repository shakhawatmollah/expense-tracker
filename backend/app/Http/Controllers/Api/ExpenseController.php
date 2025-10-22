<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ExpenseDatabaseException;
use App\Exceptions\ExpenseNotFoundException;
use App\Exceptions\ExpenseUnauthorizedException;
use App\Exceptions\ExpenseValidationException;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Expense\DateRangeRequest;
use App\Http\Requests\Expense\SearchExpenseRequest;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Check if pagination is requested
        if ($request->has('paginate') && $request->get('paginate') !== 'false') {
            $expenses = $this->expenseService->getPaginatedUserExpenses(
                $request->user()->id,
                $request->all()
            );

            return ApiResponse::success(
                ExpenseResource::collection($expenses->items()),
                null,
                [
                    'current_page' => $expenses->currentPage(),
                    'from' => $expenses->firstItem(),
                    'last_page' => $expenses->lastPage(),
                    'per_page' => $expenses->perPage(),
                    'to' => $expenses->lastItem(),
                    'total' => $expenses->total(),
                    'links' => [
                        'first' => $expenses->url(1),
                        'last' => $expenses->url($expenses->lastPage()),
                        'prev' => $expenses->previousPageUrl(),
                        'next' => $expenses->nextPageUrl(),
                    ],
                ]
            );
        }

        // Return all expenses without pagination
        $expenses = $this->expenseService->getUserExpenses(
            $request->user()->id,
            $request->all()
        );

        return ApiResponse::collection(
            ExpenseResource::collection($expenses)
        );
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

            return ApiResponse::created(
                new ExpenseResource($expense),
                'Expense created successfully'
            );

        } catch (ExpenseValidationException $e) {
            Log::warning('Expense validation failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
            ]);

            throw $e;

        } catch (ExpenseDatabaseException $e) {
            Log::error('Database error creating expense', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
            ]);

            throw $e;

        } catch (\Exception $e) {
            Log::critical('Unexpected error creating expense', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new ExpenseDatabaseException(
                'expense creation',
                $e->getMessage(),
                ['user_id' => $request->user()->id]
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $expense = $this->expenseService->findForUser($id, $request->user()->id);

            return ApiResponse::success(
                new ExpenseResource($expense)
            );

        } catch (ExpenseNotFoundException $e) {
            return ApiResponse::error(
                $e->getUserMessage(),
                [],
                404
            );

        } catch (ExpenseUnauthorizedException $e) {
            return ApiResponse::error(
                $e->getUserMessage(),
                [],
                403
            );

        } catch (\Exception $e) {
            Log::error('Unexpected error fetching expense', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            throw new ExpenseDatabaseException(
                'expense retrieval',
                $e->getMessage(),
                ['expense_id' => $id, 'user_id' => $request->user()->id]
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, string $id): JsonResponse
    {
        try {
            $expense = $this->expenseService->update($id, $request->user()->id, $request->validated());

            return ApiResponse::success(
                new ExpenseResource($expense),
                'Expense updated successfully'
            );

        } catch (ExpenseNotFoundException $e) {
            Log::info('Expense not found for update', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
            ]);

            throw $e;

        } catch (ExpenseUnauthorizedException $e) {
            Log::warning('Unauthorized expense update attempt', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
            ]);

            throw $e;

        } catch (ExpenseValidationException $e) {
            Log::warning('Expense update validation failed', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
                'errors' => $e->getErrors(),
            ]);

            throw $e;

        } catch (ExpenseDatabaseException $e) {
            Log::error('Database error updating expense', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;

        } catch (\Exception $e) {
            Log::critical('Unexpected error updating expense', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new ExpenseDatabaseException(
                'expense update',
                $e->getMessage(),
                ['expense_id' => $id, 'user_id' => $request->user()->id]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->expenseService->delete($id, $request->user()->id);

            return ApiResponse::message(
                'Expense deleted successfully'
            );

        } catch (ExpenseNotFoundException $e) {
            Log::info('Expense not found for deletion', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
            ]);

            throw $e;

        } catch (ExpenseUnauthorizedException $e) {
            Log::warning('Unauthorized expense deletion attempt', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
            ]);

            throw $e;

        } catch (ExpenseDatabaseException $e) {
            Log::error('Database error deleting expense', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;

        } catch (\Exception $e) {
            Log::critical('Unexpected error deleting expense', [
                'expense_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new ExpenseDatabaseException(
                'expense deletion',
                $e->getMessage(),
                ['expense_id' => $id, 'user_id' => $request->user()->id]
            );
        }
    }

    /**
     * Get expenses by date range
     */
    public function getByDateRange(DateRangeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $expenses = $this->expenseService->getByDateRange(
            $request->user()->id,
            $validated['start_date'],
            $validated['end_date']
        );

        return ApiResponse::success(
            ExpenseResource::collection($expenses),
            null,
            [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_expenses' => $expenses->count(),
                'total_amount' => $expenses->sum('amount'),
            ]
        );
    }

    /**
     * Search expenses
     */
    public function search(SearchExpenseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $expenses = $this->expenseService->search(
            $request->user()->id,
            $validated['query'] ?? null
        );

        return ApiResponse::success(
            ExpenseResource::collection($expenses),
            null,
            [
                'query' => $validated['query'] ?? '',
                'results_count' => $expenses->count(),
            ]
        );
    }
}
