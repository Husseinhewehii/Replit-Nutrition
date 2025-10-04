<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PortionService;
use App\Repositories\PortionRepository;
use App\Repositories\FoodRepository;
use App\Models\Portion;
use App\Models\Food;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PortionServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected $portionRepository;
    protected $foodRepository;
    protected $portionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->portionRepository = $this->createMock(PortionRepository::class);
        $this->foodRepository = $this->createMock(FoodRepository::class);
        $this->portionService = new PortionService($this->portionRepository, $this->foodRepository);
    }

    public function test_create_portion_with_global_food()
    {
        $food = new Food([
            'id' => 1,
            'name' => 'Rice',
            'is_global' => true,
            'user_id' => null,
        ]);

        $this->foodRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($food);

        $expectedData = [
            'user_id' => 1,
            'food_id' => 1,
            'grams' => 150.0,
            'consumed_at' => Carbon::today()->toDateString(),
        ];

        $portion = new Portion($expectedData);

        $this->portionRepository->expects($this->once())
            ->method('create')
            ->with($expectedData)
            ->willReturn($portion);

        $result = $this->portionService->createPortion(1, 1, 150);

        $this->assertEquals($portion, $result);
    }

    public function test_create_portion_with_user_owned_food()
    {
        $food = new Food([
            'id' => 1,
            'name' => 'My Food',
            'is_global' => false,
            'user_id' => 1,
        ]);

        $this->foodRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($food);

        $expectedData = [
            'user_id' => 1,
            'food_id' => 1,
            'grams' => 100.0,
            'consumed_at' => Carbon::today()->toDateString(),
        ];

        $portion = new Portion($expectedData);

        $this->portionRepository->expects($this->once())
            ->method('create')
            ->with($expectedData)
            ->willReturn($portion);

        $result = $this->portionService->createPortion(1, 1, 100);

        $this->assertEquals($portion, $result);
    }

    public function test_create_portion_returns_null_when_food_not_found()
    {
        $this->foodRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->portionService->createPortion(1, 999, 100);

        $this->assertNull($result);
    }

    public function test_create_portion_returns_null_when_user_not_authorized()
    {
        $food = new Food([
            'id' => 1,
            'name' => 'Other User Food',
            'is_global' => false,
            'user_id' => 2,
        ]);

        $this->foodRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($food);

        $result = $this->portionService->createPortion(1, 1, 100);

        $this->assertNull($result);
    }

    public function test_is_valid_slug_grams_format_with_valid_input()
    {
        $this->assertTrue($this->portionService->isValidSlugGramsFormat('chicken_breast-150'));
        $this->assertTrue($this->portionService->isValidSlugGramsFormat('rice-200'));
        $this->assertTrue($this->portionService->isValidSlugGramsFormat('egg_white-50'));
    }

    public function test_is_valid_slug_grams_format_with_invalid_input()
    {
        $this->assertFalse($this->portionService->isValidSlugGramsFormat('no-dash'));
        $this->assertFalse($this->portionService->isValidSlugGramsFormat('Invalid_Slug-150'));
        $this->assertFalse($this->portionService->isValidSlugGramsFormat('chicken-breast-150'));
        $this->assertFalse($this->portionService->isValidSlugGramsFormat('rice-0'));
        $this->assertFalse($this->portionService->isValidSlugGramsFormat('rice--50'));
        $this->assertFalse($this->portionService->isValidSlugGramsFormat('rice-abc'));
    }

    public function test_create_portion_from_slug_grams_success()
    {
        $food = new Food([
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'is_global' => true,
        ]);
        $food->id = 1;

        $this->foodRepository->expects($this->once())
            ->method('findBySlug')
            ->with('chicken_breast', 1)
            ->willReturn($food);

        $this->foodRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($food);

        $expectedData = [
            'user_id' => 1,
            'food_id' => 1,
            'grams' => 150.0,
            'consumed_at' => Carbon::today()->toDateString(),
        ];

        $portion = new Portion($expectedData);

        $this->portionRepository->expects($this->once())
            ->method('create')
            ->with($expectedData)
            ->willReturn($portion);

        $result = $this->portionService->createPortionFromSlugGrams(1, 'chicken_breast-150');

        $this->assertEquals($portion, $result);
    }

    public function test_create_portion_from_slug_grams_returns_null_when_food_not_found()
    {
        $this->foodRepository->expects($this->once())
            ->method('findBySlug')
            ->with('unknown_food', 1)
            ->willReturn(null);

        $result = $this->portionService->createPortionFromSlugGrams(1, 'unknown_food-100');

        $this->assertNull($result);
    }

    public function test_delete_portion_returns_true()
    {
        $portion = new Portion(['id' => 1]);

        $this->portionRepository->expects($this->once())
            ->method('delete')
            ->with($portion)
            ->willReturn(true);

        $result = $this->portionService->deletePortion($portion);

        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
