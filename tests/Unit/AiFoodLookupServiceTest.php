<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AiFoodLookupService;
use App\Services\FoodService;
use App\Models\Food;
use Mockery;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AiFoodLookupServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected $foodService;
    protected $aiLookupService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->foodService = Mockery::mock(FoodService::class);
        $this->aiLookupService = new AiFoodLookupService($this->foodService);
    }

    public function test_find_or_create_food_returns_existing_food_from_database()
    {
        $food = new Food([
            'id' => 1,
            'slug' => 'chicken_breast',
            'name' => 'Chicken Breast',
        ]);

        $this->foodService->shouldReceive('findBySlug')
            ->with('chicken_breast', 1)
            ->once()
            ->andReturn($food);

        $result = $this->aiLookupService->findOrCreateFood('chicken_breast', 1);

        $this->assertEquals('database', $result['source']);
        $this->assertEquals($food, $result['food']);
    }

    public function test_find_or_create_food_returns_null_when_no_user_id_and_food_not_found()
    {
        $this->foodService->shouldReceive('findBySlug')
            ->with('unknown_food', null)
            ->once()
            ->andReturn(null);

        $result = $this->aiLookupService->findOrCreateFood('unknown_food', null);

        $this->assertNull($result);
    }

    public function test_find_or_create_food_creates_food_from_ai_when_not_in_database()
    {
        $this->foodService->shouldReceive('findBySlug')
            ->with('salmon', 1)
            ->once()
            ->andReturn(null);

        OpenAI::fake([
            \OpenAI\Responses\Chat\CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'name' => 'Salmon',
                                'kcal_per_100g' => 208,
                                'protein_per_100g' => 20,
                                'carbs_per_100g' => 0,
                                'fat_per_100g' => 13,
                            ]),
                        ],
                    ],
                ],
            ]),
        ]);

        $aiFood = [
            'name' => 'Salmon',
            'slug' => 'salmon',
            'kcal_per_100g' => 208,
            'protein_per_100g' => 20,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 13,
        ];

        $createdFood = new Food($aiFood);

        $this->foodService->shouldReceive('createFood')
            ->with($aiFood, 1)
            ->once()
            ->andReturn($createdFood);

        $result = $this->aiLookupService->findOrCreateFood('salmon', 1);

        $this->assertEquals('ai', $result['source']);
        $this->assertEquals($createdFood, $result['food']);
    }

    public function test_find_or_create_food_returns_null_when_ai_fails()
    {
        $this->foodService->shouldReceive('findBySlug')
            ->with('invalid_food', 1)
            ->once()
            ->andReturn(null);

        OpenAI::fake([
            new \Exception('API Error'),
        ]);

        $result = $this->aiLookupService->findOrCreateFood('invalid_food', 1);

        $this->assertNull($result);
    }

    public function test_lookup_food_with_ai_handles_json_code_blocks()
    {
        $this->foodService->shouldReceive('findBySlug')
            ->with('tuna', 1)
            ->once()
            ->andReturn(null);

        OpenAI::fake([
            \OpenAI\Responses\Chat\CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => "```json\n" . json_encode([
                                'name' => 'Tuna',
                                'kcal_per_100g' => 144,
                                'protein_per_100g' => 30,
                                'carbs_per_100g' => 0,
                                'fat_per_100g' => 0.95,
                            ]) . "\n```",
                        ],
                    ],
                ],
            ]),
        ]);

        $aiFood = [
            'name' => 'Tuna',
            'slug' => 'tuna',
            'kcal_per_100g' => 144,
            'protein_per_100g' => 30,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 0.95,
        ];

        $createdFood = new Food($aiFood);

        $this->foodService->shouldReceive('createFood')
            ->with($aiFood, 1)
            ->once()
            ->andReturn($createdFood);

        $result = $this->aiLookupService->findOrCreateFood('tuna', 1);

        $this->assertEquals('ai', $result['source']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
