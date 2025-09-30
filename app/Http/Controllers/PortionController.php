<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PortionService;
use App\Services\AiFoodLookupService;

class PortionController extends Controller
{
    public function __construct(
        protected PortionService $portionService,
        protected AiFoodLookupService $aiFoodLookupService
    ) {}

    public function quickAdd(Request $request)
    {
        $validated = $request->validate([
            'slug_grams' => 'required|string',
        ]);

        $input = $validated['slug_grams'];

        if (!$this->portionService->isValidSlugGramsFormat($input)) {
            return back()->withErrors(['slug_grams' => 'Invalid format. Use: slug-grams (e.g., chicken_breast-150)']);
        }

        $parts = explode('-', $input);
        $slug = $parts[0];
        $grams = (float) $parts[1];

        $result = $this->aiFoodLookupService->findOrCreateFood($slug, $request->user()->id);

        if (!$result) {
            return back()->withErrors(['slug_grams' => 'Could not find or create food. Please try again.']);
        }

        $portion = $this->portionService->createPortion($request->user()->id, $result['food']->id, $grams);

        if (!$portion) {
            return back()->withErrors(['slug_grams' => 'You do not have access to this food.']);
        }

        $message = $result['source'] === 'ai' 
            ? "Portion added! Food '{$result['food']->name}' was automatically created with AI."
            : 'Portion added successfully!';

        return back()->with('success', $message);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'food_id' => 'required|exists:foods,id',
            'grams' => 'required|numeric|min:0.01',
        ]);

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
