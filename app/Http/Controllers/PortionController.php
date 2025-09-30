<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PortionService;
use App\Services\AiFoodLookupService;
use App\Http\Requests\QuickAddPortionRequest;
use App\Http\Requests\StorePortionRequest;

class PortionController extends Controller
{
    public function __construct(
        protected PortionService $portionService,
        protected AiFoodLookupService $aiFoodLookupService
    ) {}

    public function quickAdd(QuickAddPortionRequest $request)
    {
        $input = $request->validated()['quick_add'];

        $parts = explode('-', $input);
        $slug = $parts[0];
        $grams = (float) $parts[1];

        $result = $this->aiFoodLookupService->findOrCreateFood($slug, $request->user()->id);

        if (!$result) {
            return back()->withErrors(['quick_add' => 'Could not find or create food. Please try again.']);
        }

        $portion = $this->portionService->createPortion($request->user()->id, $result['food']->id, $grams);

        if (!$portion) {
            return back()->withErrors(['quick_add' => 'You do not have access to this food.']);
        }

        $message = $result['source'] === 'ai' 
            ? "Portion added! Food '{$result['food']->name}' was automatically created with AI."
            : 'Portion added successfully!';

        return back()->with('success', $message);
    }

    public function add(StorePortionRequest $request)
    {
        $validated = $request->validated();

        $portion = $this->portionService->createPortion(
            $request->user()->id,
            $validated['food_id'],
            $validated['grams']
        );

        if (!$portion) {
            return back()->withErrors(['food_id' => 'You do not have access to this food.']);
        }

        return back()->with('success', 'Portion added successfully!');
    }

    public function index(Request $request)
    {
        $portions = $this->portionService->getUserPortionsGroupedByDate($request->user()->id);

        $dailyTotals = [];
        foreach ($portions->groupBy('consumed_at') as $date => $dayPortions) {
            $totals = [
                'kcal' => 0,
                'protein' => 0,
                'carbs' => 0,
                'fat' => 0,
            ];

            foreach ($dayPortions as $portion) {
                $food = $portion->food;
                $multiplier = $portion->grams / 100;

                $totals['kcal'] += $food->kcal_per_100g * $multiplier;
                $totals['protein'] += $food->protein_per_100g * $multiplier;
                $totals['carbs'] += $food->carbs_per_100g * $multiplier;
                $totals['fat'] += $food->fat_per_100g * $multiplier;
            }

            $dailyTotals[$date] = [
                'kcal' => round($totals['kcal'], 1),
                'protein' => round($totals['protein'], 1),
                'carbs' => round($totals['carbs'], 1),
                'fat' => round($totals['fat'], 1),
            ];
        }

        return view('entries.index', [
            'portions' => $portions,
            'dailyTotals' => $dailyTotals,
        ]);
    }
}
