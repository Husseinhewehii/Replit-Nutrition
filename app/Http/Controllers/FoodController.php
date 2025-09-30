<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FoodService;
use App\Models\Food;
use App\Http\Requests\StoreFoodRequest;
use App\Http\Requests\UpdateFoodRequest;

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

    public function store(StoreFoodRequest $request)
    {
        $validated = $request->validated();

        $this->foodService->createFood($validated, $request->user()->id);

        return redirect()->route('foods.index')->with('success', 'Food created successfully!');
    }

    public function edit(Food $food)
    {
        $this->authorize('update', $food);
        return view('foods.edit', ['food' => $food]);
    }

    public function update(UpdateFoodRequest $request, Food $food)
    {
        $validated = $request->validated();

        $this->foodService->updateFood($food, $validated);

        return redirect()->route('foods.index')->with('success', 'Food updated successfully!');
    }

    public function destroy(Food $food)
    {
        $this->authorize('delete', $food);
        $this->foodService->deleteFood($food);

        return redirect()->route('foods.index')->with('success', 'Food deleted successfully!');
    }
}
