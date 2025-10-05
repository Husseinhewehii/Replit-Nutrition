<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PortionService;
use App\Services\DailyTotalsService;
use App\Services\AiFoodLookupService;
use App\Models\Portion;

class PortionController extends Controller
{
    public function __construct(
        protected PortionService $portionService,
        protected DailyTotalsService $dailyTotalsService,
        protected AiFoodLookupService $aiFoodLookupService
    ) {}

    public function index(Request $request)
    {
        $portions = $this->portionService->getUserPortionsGroupedByDate($request->user()->id, 50);
        return response()->json($portions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'food_id' => 'required|exists:foods,id',
            'grams' => 'required|numeric|min:0.01',
            'consumed_at' => 'nullable|date',
        ]);

        $portion = $this->portionService->createPortion(
            $request->user()->id,
            $validated['food_id'],
            $validated['grams'],
            $validated['consumed_at'] ?? null
        );

        if (!$portion) {
            return response()->json([
                'error' => 'You do not have access to this food.'
            ], 403);
        }

        return response()->json($portion->load('food'), 201);
    }

    public function show(Portion $portion)
    {
        $this->authorize('view', $portion);
        return response()->json($portion->load('food'));
    }

    public function destroy(Portion $portion)
    {
        $this->authorize('delete', $portion);
        $this->portionService->deletePortion($portion);

        return response()->noContent();
    }

    public function quickAdd(Request $request)
    {
        $validated = $request->validate([
            'slug_grams' => 'required|string',
        ]);

        // Parse multiple food entries (comma or newline separated)
        $lines = array_filter(array_map('trim', preg_split('/[\n\r,]+/', $validated['slug_grams'])));
        
        if (empty($lines)) {
            return response()->json([
                'error' => 'At least one food entry is required.'
            ], 422);
        }

        // Validate each line format
        foreach ($lines as $line) {
            if (!$this->portionService->isValidSlugGramsFormat($line)) {
                return response()->json([
                    'error' => "Invalid format: '{$line}'. Use: slug-grams (e.g., chicken_breast-150)"
                ], 422);
            }
        }

        $results = [];
        $errors = [];
        $successCount = 0;
        $aiCreatedCount = 0;

        foreach ($lines as $line) {
            $parts = explode('-', $line);
            $slug = $parts[0];
            $grams = (float) $parts[1];

            $result = $this->aiFoodLookupService->findOrCreateFood($slug, $request->user()->id);

            if (!$result) {
                $errors[] = "Could not find or create food: {$slug}";
                continue;
            }

            // Handle AI failure case
            if (isset($result['error_type']) && $result['error_type'] === 'ai_failure') {
                $errors[] = "Unable to find nutrition information for: {$slug}";
                continue;
            }

            $portion = $this->portionService->createPortion($request->user()->id, $result['food']->id, $grams);

            if (!$portion) {
                $errors[] = "You do not have access to food: {$slug}";
                continue;
            }

            $successCount++;
            if ($result['source'] === 'ai') {
                $aiCreatedCount++;
            }
            
            $results[] = [
                'portion' => $portion->load('food'),
                'food' => $result['food']->name,
                'grams' => $grams,
                'source' => $result['source']
            ];
        }

        // Build response
        $response = [
            'results' => $results,
            'summary' => [
                'total' => count($lines),
                'successful' => $successCount,
                'failed' => count($errors),
                'ai_created' => $aiCreatedCount
            ]
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = "Some foods could not be added: " . implode(', ', $errors);
            if ($successCount > 0) {
                $response['message'] .= " ({$successCount} foods were added successfully)";
            }
            return response()->json($response, 207); // 207 Multi-Status
        }

        // Success response
        $response['message'] = "Successfully added {$successCount} food" . ($successCount > 1 ? 's' : '') . "!";
        if ($aiCreatedCount > 0) {
            $response['message'] .= " {$aiCreatedCount} food" . ($aiCreatedCount > 1 ? 's were' : ' was') . " automatically created with AI.";
        }

        return response()->json($response, 201);
    }

    public function dailyTotals(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $totals = $this->dailyTotalsService->calculateDailyTotals($request->user()->id, $date);

        return response()->json([
            'date' => $date,
            'totals' => $totals,
        ]);
    }
}
