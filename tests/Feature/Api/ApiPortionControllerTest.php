<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Food;
use App\Models\Portion;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ApiPortionControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_api_portions_index_requires_authentication()
    {
        $response = $this->getJson('/api/portions');

        $response->assertStatus(401);
    }

    public function test_api_portions_index_returns_user_portions()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::factory()->create();

        Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => Carbon::today()->toDateString(),
        ]);

        $response = $this->getJson('/api/portions');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_api_can_create_portion()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::create([
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'is_global' => true,
        ]);

        $response = $this->postJson('/api/portions', [
            'food_id' => $food->id,
            'grams' => 150,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
        ]);
    }

    public function test_api_can_quick_add_portion()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::create([
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'is_global' => true,
        ]);

        $response = $this->postJson('/api/portions/quick-add', [
            'slug_grams' => 'chicken_breast-150',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
        ]);
    }

    public function test_api_quick_add_fails_with_invalid_format()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/portions/quick-add', [
            'slug_grams' => 'invalid-format-here',
        ]);

        $response->assertStatus(422);
    }

    public function test_api_can_show_portion()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::factory()->create();
        
        $portion = Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => Carbon::today()->toDateString(),
        ]);

        $response = $this->getJson("/api/portions/{$portion->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['grams' => '150.00']);
    }

    public function test_api_can_delete_portion()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::factory()->create();
        
        $portion = Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => Carbon::today()->toDateString(),
        ]);

        $response = $this->deleteJson("/api/portions/{$portion->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('portions', ['id' => $portion->id]);
    }

    public function test_api_daily_totals_returns_totals()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::create([
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'is_global' => true,
        ]);

        Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => Carbon::today()->toDateString(),
        ]);

        $response = $this->getJson('/api/daily-totals');

        $response->assertStatus(200);
        $response->assertJsonStructure(['date', 'totals']);
    }

    public function test_api_cannot_delete_other_users_portion()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user2);

        $food = Food::factory()->create();
        
        $portion = Portion::create([
            'user_id' => $user1->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => Carbon::today()->toDateString(),
        ]);

        $response = $this->deleteJson("/api/portions/{$portion->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('portions', ['id' => $portion->id]);
    }
}
