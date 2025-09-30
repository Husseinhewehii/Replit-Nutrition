<?php

namespace App\Services;

use App\Repositories\PortionRepository;
use Carbon\Carbon;

class DailyTotalsService
{
    public function __construct(
        protected PortionRepository $portionRepository
    ) {}

    public function calculateDailyTotals(int $userId, string $date): array
    {
        $portions = $this->portionRepository->getUserPortionsByDate($userId, $date);

        $totals = [
            'kcal' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fat' => 0,
        ];

        foreach ($portions as $portion) {
            $food = $portion->food;
            $multiplier = $portion->grams / 100;

            $totals['kcal'] += $food->kcal_per_100g * $multiplier;
            $totals['protein'] += $food->protein_per_100g * $multiplier;
            $totals['carbs'] += $food->carbs_per_100g * $multiplier;
            $totals['fat'] += $food->fat_per_100g * $multiplier;
        }

        $totals['kcal'] = round($totals['kcal'], 1);
        $totals['protein'] = round($totals['protein'], 1);
        $totals['carbs'] = round($totals['carbs'], 1);
        $totals['fat'] = round($totals['fat'], 1);

        return $totals;
    }

    public function getTodayTotals(int $userId): array
    {
        return $this->calculateDailyTotals($userId, Carbon::today()->toDateString());
    }
}
