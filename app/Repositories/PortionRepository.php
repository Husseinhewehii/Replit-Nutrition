<?php

namespace App\Repositories;

use App\Models\Portion;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class PortionRepository
{
    public function findById(int $id): ?Portion
    {
        return Portion::with('food')->find($id);
    }

    public function getUserPortionsByDate(int $userId, string $date): Collection
    {
        return Portion::with('food')
            ->where('user_id', $userId)
            ->whereDate('consumed_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUserPortionsGroupedByDate(int $userId, int $perPage = 15)
    {
        return Portion::with('food')
            ->where('user_id', $userId)
            ->orderBy('consumed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Portion
    {
        return Portion::create($data);
    }

    public function delete(Portion $portion): bool
    {
        return $portion->delete();
    }
}
