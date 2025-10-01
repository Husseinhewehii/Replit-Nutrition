<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FoodService;
use App\Repositories\FoodRepository;
use App\Models\Food;
use Mockery;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FoodServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected $foodRepository;
    protected $foodService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->foodRepository = Mockery::mock(FoodRepository::class);
        $this->foodService = new FoodService($this->foodRepository);
    }

    public function test_find_by_slug_returns_food()
    {
        $food = new Food(['id' => 1, 'slug' => 'chicken_breast', 'name' => 'Chicken Breast']);
        $this->foodRepository->shouldReceive('findBySlug')
            ->with('chicken_breast', 1)
            ->once()
            ->andReturn($food);

        $result = $this->foodService->findBySlug('chicken_breast', 1);

        $this->assertEquals($food, $result);
    }

    public function test_get_user_foods_returns_collection()
    {
        $foods = new \Illuminate\Database\Eloquent\Collection([new Food(['id' => 1, 'name' => 'Food 1'])]);
        $this->foodRepository->shouldReceive('getUserFoods')
            ->with(1)
            ->once()
            ->andReturn($foods);

        $result = $this->foodService->getUserFoods(1);

        $this->assertEquals($foods, $result);
    }

    public function test_get_global_foods_returns_collection()
    {
        $foods = new \Illuminate\Database\Eloquent\Collection([new Food(['id' => 1, 'name' => 'Global Food', 'is_global' => true])]);
        $this->foodRepository->shouldReceive('getGlobalFoods')
            ->once()
            ->andReturn($foods);

        $result = $this->foodService->getGlobalFoods();

        $this->assertEquals($foods, $result);
    }

    public function test_create_food_generates_slug_from_name()
    {
        $data = [
            'name' => 'Chicken Breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
        ];

        $expectedData = array_merge($data, ['slug' => 'chicken-breast', 'user_id' => 1]);
        $food = new Food($expectedData);

        $this->foodRepository->shouldReceive('create')
            ->with($expectedData)
            ->once()
            ->andReturn($food);

        $result = $this->foodService->createFood($data, 1);

        $this->assertEquals($food, $result);
    }

    public function test_create_food_uses_provided_slug()
    {
        $data = [
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
        ];

        $expectedData = array_merge($data, ['user_id' => 1]);

        $this->foodRepository->shouldReceive('create')
            ->with($expectedData)
            ->once()
            ->andReturn(new Food($expectedData));

        $result = $this->foodService->createFood($data, 1);

        $this->assertEquals('chicken_breast', $result->slug);
    }

    public function test_update_food_generates_slug_if_name_changed()
    {
        $food = new Food(['id' => 1, 'name' => 'Old Name', 'slug' => 'old_name']);
        $data = ['name' => 'New Name'];

        $this->foodRepository->shouldReceive('update')
            ->with($food, ['name' => 'New Name', 'slug' => 'new-name'])
            ->once()
            ->andReturn(true);

        $result = $this->foodService->updateFood($food, $data);

        $this->assertTrue($result);
    }

    public function test_update_food_keeps_existing_slug_if_provided()
    {
        $food = new Food(['id' => 1, 'name' => 'Old Name', 'slug' => 'old_name']);
        $data = ['name' => 'New Name', 'slug' => 'custom_slug'];

        $this->foodRepository->shouldReceive('update')
            ->with($food, $data)
            ->once()
            ->andReturn(true);

        $result = $this->foodService->updateFood($food, $data);

        $this->assertTrue($result);
    }

    public function test_delete_food_returns_true()
    {
        $food = new Food(['id' => 1, 'name' => 'Food']);

        $this->foodRepository->shouldReceive('delete')
            ->with($food)
            ->once()
            ->andReturn(true);

        $result = $this->foodService->deleteFood($food);

        $this->assertTrue($result);
    }

    public function test_create_global_food_sets_is_global_true()
    {
        $data = [
            'name' => 'Rice',
            'kcal_per_100g' => 130,
            'protein_per_100g' => 2.7,
            'carbs_per_100g' => 28,
            'fat_per_100g' => 0.3,
        ];

        $expectedData = array_merge($data, [
            'slug' => 'rice',
            'is_global' => true,
            'user_id' => null,
        ]);

        $this->foodRepository->shouldReceive('create')
            ->with($expectedData)
            ->once()
            ->andReturn(new Food($expectedData));

        $result = $this->foodService->createGlobalFood($data);

        $this->assertTrue($result->is_global);
        $this->assertNull($result->user_id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
