<?php

namespace App\Services;

use App\Repositories\PortionRepository;
use App\Repositories\FoodRepository;
use App\Models\Portion;
use Carbon\Carbon;

class PortionService
{
    public function __construct(
        protected PortionRepository $portionRepository,
        protected FoodRepository $foodRepository
    ) {}

    public function createPortion(int $userId, int $foodId, float $grams, ?string $date = null): Portion
    {
        return $this->portionRepository->create([
            'user_id' => $userId,
            'food_id' => $foodId,
            'grams' => $grams,
            'consumed_at' => $date ?? Carbon::today()->toDateString(),
        ]);
    }

    public function createPortionFromSlugGrams(int $userId, string $input, ?string $date = null): ?Portion
    {
        if (!$this->isValidSlugGramsFormat($input)) {
            return null;
        }

        $parts = explode('-', $input);
        $slug = $parts[0];
        $grams = (float) $parts[1];

        if ($grams <= 0) {
            return null;
        }

        $food = $this->foodRepository->findBySlug($slug, $userId);
        
        if (!$food) {
            return null;
        }

        return $this->createPortion($userId, $food->id, $grams, $date);
    }

    public function isValidSlugGramsFormat(string $input): bool
    {
        if (!str_contains($input, '-')) {
            return false;
        }

        $parts = explode('-', $input);
        
        if (count($parts) !== 2) {
            return false;
        }

        $slug = $parts[0];
        $grams = $parts[1];

        if (!preg_match('/^[a-z0-9_]+$/', $slug)) {
            return false;
        }

        if (!is_numeric($grams) || (float) $grams <= 0) {
            return false;
        }

        return true;
    }

    public function getUserPortionsByDate(int $userId, string $date)
    {
        return $this->portionRepository->getUserPortionsByDate($userId, $date);
    }

    public function getUserPortionsGroupedByDate(int $userId, int $perPage = 15)
    {
        return $this->portionRepository->getUserPortionsGroupedByDate($userId, $perPage);
    }

    public function deletePortion(Portion $portion): bool
    {
        return $this->portionRepository->delete($portion);
    }
}
