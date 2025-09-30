<?php

namespace App\Http\Controllers;

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
        $userFoods = $this->foodService->getUserFoods($request->user()->id);
        $globalFoods = $this->foodService->getGlobalFoods();

        return view('foods.index', [
            'userFoods' => $userFoods,
            'globalFoods' => $globalFoods,
        ]);
    }

    public function create()
    {
        return view('foods.create');
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

        $this->foodService->createFood($validated, $request->user()->id);

        return redirect()->route('foods.index')->with('success', 'Food created successfully!');
    }

    public function edit(Food $food)
    {
        return view('foods.edit', ['food' => $food]);
    }

    public function update(Request $request, Food $food)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'kcal_per_100g' => 'required|numeric|min:0',
            'protein_per_100g' => 'required|numeric|min:0',
            'carbs_per_100g' => 'required|numeric|min:0',
            'fat_per_100g' => 'required|numeric|min:0',
        ]);

        $this->foodService->updateFood($food, $validated);

        return redirect()->route('foods.index')->with('success', 'Food updated successfully!');
    }

    public function destroy(Food $food)
    {
        $this->foodService->deleteFood($food);

        return redirect()->route('foods.index')->with('success', 'Food deleted successfully!');
    }
}
