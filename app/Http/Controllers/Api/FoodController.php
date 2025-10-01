<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FoodService;
use App\Models\Food;

class FoodController extends Controller
{
    public function __construct(
        protected FoodService $foodService
    ) {}

    public function index(Request $request)
    {
        $foods = $this->foodService->getAllAccessibleFoods($request->user()?->id);
        return response()->json($foods);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'kcal_per_100g' => 'required|numeric|min:0',
            'protein_per_100g' => 'required|numeric|min:0',
            'carbs_per_100g' => 'required|numeric|min:0',
            'fat_per_100g' => 'required|numeric|min:0',
        ]);

        $food = $this->foodService->createFood($validated, $request->user()->id);

        return response()->json($food, 201);
    }

    public function show(Food $food)
    {
        $this->authorize('view', $food);
        return response()->json($food);
    }

    public function update(Request $request, Food $food)
    {
        $this->authorize('update', $food);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'kcal_per_100g' => 'required|numeric|min:0',
            'protein_per_100g' => 'required|numeric|min:0',
            'carbs_per_100g' => 'required|numeric|min:0',
            'fat_per_100g' => 'required|numeric|min:0',
        ]);

        $this->foodService->updateFood($food, $validated);

        return response()->json($food);
    }

    public function destroy(Food $food)
    {
        $this->authorize('delete', $food);
        $this->foodService->deleteFood($food);

        return response()->noContent();
    }
}
