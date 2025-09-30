<?php

namespace App\Services;

use App\Repositories\FoodRepository;
use App\Models\Food;
use Illuminate\Support\Str;

class FoodService
{
    public function __construct(
        protected FoodRepository $foodRepository
    ) {}

    public function findBySlug(string $slug, ?int $userId = null): ?Food
    {
        return $this->foodRepository->findBySlug($slug, $userId);
    }

    public function getUserFoods(int $userId)
    {
        return $this->foodRepository->getUserFoods($userId);
    }

    public function getGlobalFoods()
    {
        return $this->foodRepository->getGlobalFoods();
    }

    public function getAllAccessibleFoods(?int $userId = null)
    {
        return $this->foodRepository->getAllAccessibleFoods($userId);
    }

    public function createFood(array $data, ?int $userId = null): Food
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['user_id'] = $userId;
        
        return $this->foodRepository->create($data);
    }

    public function updateFood(Food $food, array $data): bool
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        return $this->foodRepository->update($food, $data);
    }

    public function deleteFood(Food $food): bool
    {
        return $this->foodRepository->delete($food);
    }

    public function createGlobalFood(array $data): Food
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_global'] = true;
        $data['user_id'] = null;
        
        return $this->foodRepository->create($data);
    }
}
