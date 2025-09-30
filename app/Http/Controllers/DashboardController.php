<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DailyTotalsService;
use App\Services\FoodService;

class DashboardController extends Controller
{
    public function __construct(
        protected DailyTotalsService $dailyTotalsService,
        protected FoodService $foodService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $todayTotals = $this->dailyTotalsService->getTodayTotals($user->id);
        $foods = $this->foodService->getAllAccessibleFoods($user->id);

        return view('dashboard', [
            'todayTotals' => $todayTotals,
            'foods' => $foods,
        ]);
    }
}
