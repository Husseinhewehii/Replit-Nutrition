<?php

namespace App\Repositories;

use App\Models\Food;
use Illuminate\Database\Eloquent\Collection;

class FoodRepository
{
    public function findById(int $id): ?Food
    {
        return Food::find($id);
    }

    public function findBySlug(string $slug, ?int $userId = null): ?Food
    {
        return Food::where('slug', $slug)
            ->where(function ($query) use ($userId) {
                $query->where('is_global', true)
                    ->orWhere('user_id', $userId);
            })
            ->first();
    }

    public function getUserFoods(int $userId): Collection
    {
        return Food::where('user_id', $userId)->get();
    }

    public function getGlobalFoods(): Collection
    {
        return Food::where('is_global', true)->get();
    }

    public function getAllAccessibleFoods(?int $userId = null): Collection
    {
        return Food::where('is_global', true)
            ->orWhere('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Food
    {
        return Food::create($data);
    }

    public function update(Food $food, array $data): bool
    {
        return $food->update($data);
    }

    public function delete(Food $food): bool
    {
        return $food->delete();
    }
}
