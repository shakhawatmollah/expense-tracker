<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(
        private ExportService $exportService
    ) {}

    /**
     * Export expenses to CSV
     */
    public function exportExpenses(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:categories,id',
            'format' => 'nullable|in:csv,xlsx,pdf',
        ]);

        $format = $validated['format'] ?? 'csv';
        $userId = $request->user()->id;

        $filePath = $this->exportService->exportExpenses(
            $userId,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            $validated['category_id'] ?? null,
            $format
        );

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Export categories to CSV
     */
    public function exportCategories(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'format' => 'nullable|in:csv,xlsx',
        ]);

        $format = $validated['format'] ?? 'csv';
        $userId = $request->user()->id;

        $filePath = $this->exportService->exportCategories($userId, $format);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Export budgets to CSV
     */
    public function exportBudgets(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'format' => 'nullable|in:csv,xlsx',
            'period' => 'nullable|in:weekly,monthly,yearly,custom',
        ]);

        $format = $validated['format'] ?? 'csv';
        $userId = $request->user()->id;

        $filePath = $this->exportService->exportBudgets(
            $userId,
            $validated['period'] ?? null,
            $format
        );

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Export financial report (comprehensive)
     */
    public function exportFinancialReport(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'nullable|in:csv,xlsx,pdf',
            'include_charts' => 'nullable|boolean',
        ]);

        $format = $validated['format'] ?? 'pdf';
        $userId = $request->user()->id;

        $filePath = $this->exportService->exportFinancialReport(
            $userId,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            $format,
            $validated['include_charts'] ?? true
        );

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Get export history
     */
    public function history(Request $request): JsonResponse
    {
        $exports = $this->exportService->getExportHistory($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $exports
        ]);
    }
}
