<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Requests\Category\IndexCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Helpers\ApiResponse;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(IndexCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $categories = $this->categoryService->getCategoriesWithExpenseCounts($request->user()->id);

        return ApiResponse::collection(
            CategoryResource::collection($categories),
            null,
            [
                'total' => $categories->count(),
                'with_counts' => $validated['with_counts'] ?? true,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->create(
                array_merge($request->validated(), ['user_id' => $request->user()->id])
            );

            return ApiResponse::created(
                new CategoryResource($category),
                'Category created successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to create category',
                ['exception' => $e->getMessage()],
                422
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $category = $this->categoryService->findForUser($id, $request->user()->id);

            return ApiResponse::success(
                new CategoryResource($category)
            );
        } catch (\Exception $e) {
            return ApiResponse::notFound('Category not found', 'category');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        try {
            $category = $this->categoryService->update($id, $request->user()->id, $request->validated());

            return ApiResponse::success(
                new CategoryResource($category),
                'Category updated successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to update category',
                ['exception' => $e->getMessage()],
                422
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->categoryService->delete($id, $request->user()->id);

            return ApiResponse::message('Category deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to delete category',
                ['exception' => $e->getMessage()],
                422
            );
        }
    }
}