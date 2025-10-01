<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Repositories\FoodRepository;
use App\Models\Food;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FoodRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FoodRepository();
    }

    public function test_find_by_id_returns_food()
    {
        $food = Food::create([
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'is_global' => true,
        ]);

        $result = $this->repository->findById($food->id);

        $this->assertEquals($food->id, $result->id);
    }

    public function test_find_by_id_returns_null_when_not_found()
    {
        $result = $this->repository->findById(999);

        $this->assertNull($result);
    }

    public function test_find_by_slug_returns_global_food()
    {
        $food = Food::create([
            'name' => 'Rice',
            'slug' => 'rice',
            'kcal_per_100g' => 130,
            'protein_per_100g' => 2.7,
            'carbs_per_100g' => 28,
            'fat_per_100g' => 0.3,
            'is_global' => true,
        ]);

        $result = $this->repository->findBySlug('rice', 1);

        $this->assertEquals($food->id, $result->id);
    }

    public function test_find_by_slug_returns_user_owned_food()
    {
        $user = User::factory()->create();

        $food = Food::create([
            'name' => 'My Custom Food',
            'slug' => 'my_custom_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
            'is_global' => false,
        ]);

        $result = $this->repository->findBySlug('my_custom_food', $user->id);

        $this->assertEquals($food->id, $result->id);
    }

    public function test_find_by_slug_does_not_return_other_users_food()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Food::create([
            'name' => 'User 1 Food',
            'slug' => 'user_1_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user1->id,
            'is_global' => false,
        ]);

        $result = $this->repository->findBySlug('user_1_food', $user2->id);

        $this->assertNull($result);
    }

    public function test_get_user_foods_returns_only_user_foods()
    {
        $user = User::factory()->create();

        Food::create([
            'name' => 'User Food',
            'slug' => 'user_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        Food::create([
            'name' => 'Global Food',
            'slug' => 'global_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'is_global' => true,
        ]);

        $result = $this->repository->getUserFoods($user->id);

        $this->assertCount(1, $result);
        $this->assertEquals('User Food', $result->first()->name);
    }

    public function test_get_global_foods_returns_only_global_foods()
    {
        $user = User::factory()->create();

        Food::create([
            'name' => 'User Food',
            'slug' => 'user_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        Food::create([
            'name' => 'Global Food',
            'slug' => 'global_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'is_global' => true,
        ]);

        $result = $this->repository->getGlobalFoods();

        $this->assertCount(1, $result);
        $this->assertEquals('Global Food', $result->first()->name);
    }

    public function test_get_all_accessible_foods_returns_global_and_user_foods()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Food::create([
            'name' => 'User Food',
            'slug' => 'user_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        Food::create([
            'name' => 'Other User Food',
            'slug' => 'other_user_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $otherUser->id,
        ]);

        Food::create([
            'name' => 'Global Food',
            'slug' => 'global_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'is_global' => true,
        ]);

        $result = $this->repository->getAllAccessibleFoods($user->id);

        $this->assertCount(2, $result);
        $names = $result->pluck('name')->toArray();
        $this->assertContains('User Food', $names);
        $this->assertContains('Global Food', $names);
        $this->assertNotContains('Other User Food', $names);
    }

    public function test_create_food()
    {
        $data = [
            'name' => 'New Food',
            'slug' => 'new_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'is_global' => true,
        ];

        $food = $this->repository->create($data);

        $this->assertDatabaseHas('foods', ['slug' => 'new_food']);
        $this->assertEquals('New Food', $food->name);
    }

    public function test_update_food()
    {
        $food = Food::create([
            'name' => 'Original Name',
            'slug' => 'original_name',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
        ]);

        $result = $this->repository->update($food, ['name' => 'Updated Name']);

        $this->assertTrue($result);
        $this->assertEquals('Updated Name', $food->fresh()->name);
    }

    public function test_delete_food()
    {
        $food = Food::create([
            'name' => 'Food to Delete',
            'slug' => 'food_to_delete',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
        ]);

        $result = $this->repository->delete($food);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('foods', ['id' => $food->id]);
    }
}
