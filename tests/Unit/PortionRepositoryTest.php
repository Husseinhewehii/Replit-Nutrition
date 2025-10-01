<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Repositories\PortionRepository;
use App\Models\Portion;
use App\Models\Food;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PortionRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PortionRepository();
    }

    public function test_find_by_id_returns_portion_with_food()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();
        
        $portion = Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => '2025-10-01',
        ]);

        $result = $this->repository->findById($portion->id);

        $this->assertEquals($portion->id, $result->id);
        $this->assertTrue($result->relationLoaded('food'));
    }

    public function test_find_by_id_returns_null_when_not_found()
    {
        $result = $this->repository->findById(999);

        $this->assertNull($result);
    }

    public function test_get_user_portions_by_date()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();

        Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => '2025-10-01',
        ]);

        Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 100,
            'consumed_at' => '2025-10-02',
        ]);

        $result = $this->repository->getUserPortionsByDate($user->id, '2025-10-01');

        $this->assertCount(1, $result);
        $this->assertEquals(150, $result->first()->grams);
    }

    public function test_get_user_portions_by_date_eager_loads_food()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();

        Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => '2025-10-01',
        ]);

        $result = $this->repository->getUserPortionsByDate($user->id, '2025-10-01');

        $this->assertTrue($result->first()->relationLoaded('food'));
    }

    public function test_get_user_portions_grouped_by_date()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();

        for ($i = 1; $i <= 20; $i++) {
            Portion::create([
                'user_id' => $user->id,
                'food_id' => $food->id,
                'grams' => 100,
                'consumed_at' => Carbon::today()->subDays($i)->toDateString(),
            ]);
        }

        $result = $this->repository->getUserPortionsGroupedByDate($user->id, 10);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(20, $result->total());
    }

    public function test_create_portion()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();

        $data = [
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => '2025-10-01',
        ];

        $portion = $this->repository->create($data);

        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
        ]);
        $this->assertEquals(150, $portion->grams);
    }

    public function test_delete_portion()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();

        $portion = Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => '2025-10-01',
        ]);

        $result = $this->repository->delete($portion);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('portions', ['id' => $portion->id]);
    }

    public function test_get_user_portions_by_date_orders_by_created_at_desc()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();

        $this->travel(-2)->hours();
        $portion1 = Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 100,
            'consumed_at' => '2025-10-01',
        ]);
        $this->travelBack();

        $this->travel(-1)->hours();
        $portion2 = Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 200,
            'consumed_at' => '2025-10-01',
        ]);
        $this->travelBack();

        $result = $this->repository->getUserPortionsByDate($user->id, '2025-10-01');

        $this->assertEquals($portion2->id, $result->first()->id);
        $this->assertEquals($portion1->id, $result->last()->id);
    }
}
