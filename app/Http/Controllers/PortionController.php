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
        
        // Parse multiple food entries (comma or newline separated)
        $lines = array_filter(array_map('trim', preg_split('/[\n\r,]+/', $input)));
        
        $results = [];
        $errors = [];
        $successCount = 0;
        $aiCreatedCount = 0;

        foreach ($lines as $line) {
            $parts = explode('-', $line);
            $slug = strtolower($parts[0]); // Convert to lowercase for database lookup
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
                'food' => $result['food']->name,
                'grams' => $grams,
                'source' => $result['source']
            ];
        }

        // Build response messages
        if (!empty($errors)) {
            $errorMessage = 'Some foods could not be added: ' . implode(', ', $errors);
            if ($successCount > 0) {
                $errorMessage .= " ({$successCount} foods were added successfully)";
            }
            return back()->withErrors(['quick_add' => $errorMessage]);
        }

        // Success message
        $message = "Successfully added {$successCount} food" . ($successCount > 1 ? 's' : '') . "!";
        if ($aiCreatedCount > 0) {
            $message .= " {$aiCreatedCount} food" . ($aiCreatedCount > 1 ? 's were' : ' was') . " automatically created with AI.";
        }

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
