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

        if (!$this->portionService->isValidSlugGramsFormat($validated['slug_grams'])) {
            return response()->json([
                'error' => 'Invalid format. Use: slug-grams (e.g., chicken_breast-150)'
            ], 422);
        }

        $parts = explode('-', $validated['slug_grams']);
        $slug = $parts[0];
        $grams = (float) $parts[1];

        $result = $this->aiFoodLookupService->findOrCreateFood($slug, $request->user()->id);

        if (!$result) {
            return response()->json([
                'error' => 'Could not find or create food'
            ], 404);
        }

        $portion = $this->portionService->createPortion($request->user()->id, $result['food']->id, $grams);

        if (!$portion) {
            return response()->json([
                'error' => 'You do not have access to this food.'
            ], 403);
        }

        return response()->json([
            'portion' => $portion->load('food'),
            'source' => $result['source'],
            'message' => $result['source'] === 'ai' 
                ? "Food '{$result['food']->name}' was automatically created with AI"
                : 'Portion added successfully'
        ], 201);
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
