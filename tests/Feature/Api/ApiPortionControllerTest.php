<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Food;
use App\Models\Portion;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use OpenAI\Laravel\Facades\OpenAI;

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

    public function test_api_quick_add_returns_503_when_ai_fails()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Mock AI failure
        OpenAI::fake([
            new \Exception('API Error'),
        ]);

        $response = $this->postJson('/api/portions/quick-add', [
            'slug_grams' => 'unknown_food-150',
        ]);

        $response->assertStatus(207); // Multi-Status for partial failures
        $response->assertJsonStructure([
            'results',
            'summary' => ['total', 'successful', 'failed', 'ai_created'],
            'errors',
            'message'
        ]);
        $response->assertJson([
            'summary' => [
                'total' => 1,
                'successful' => 0,
                'failed' => 1,
                'ai_created' => 0
            ]
        ]);
    }

    public function test_api_can_quick_add_multiple_foods_comma_separated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food1 = Food::create([
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'is_global' => true,
        ]);

        $food2 = Food::create([
            'name' => 'Rice',
            'slug' => 'rice',
            'kcal_per_100g' => 130,
            'protein_per_100g' => 2.7,
            'carbs_per_100g' => 28,
            'fat_per_100g' => 0.3,
            'is_global' => true,
        ]);

        $response = $this->postJson('/api/portions/quick-add', [
            'slug_grams' => 'chicken_breast-150, rice-200',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'results' => [
                '*' => ['portion', 'food', 'grams', 'source']
            ],
            'summary' => ['total', 'successful', 'failed', 'ai_created'],
            'message'
        ]);

        $response->assertJson([
            'summary' => [
                'total' => 2,
                'successful' => 2,
                'failed' => 0,
                'ai_created' => 0
            ]
        ]);

        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food1->id,
            'grams' => 150,
        ]);

        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food2->id,
            'grams' => 200,
        ]);
    }

    public function test_api_can_quick_add_multiple_foods_newline_separated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food1 = Food::create([
            'name' => 'Apple',
            'slug' => 'apple',
            'kcal_per_100g' => 52,
            'protein_per_100g' => 0.3,
            'carbs_per_100g' => 14,
            'fat_per_100g' => 0.2,
            'is_global' => true,
        ]);

        $food2 = Food::create([
            'name' => 'Banana',
            'slug' => 'banana',
            'kcal_per_100g' => 89,
            'protein_per_100g' => 1.1,
            'carbs_per_100g' => 23,
            'fat_per_100g' => 0.3,
            'is_global' => true,
        ]);

        $response = $this->postJson('/api/portions/quick-add', [
            'slug_grams' => "apple-100\nbanana-120",
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'summary' => [
                'total' => 2,
                'successful' => 2,
                'failed' => 0,
                'ai_created' => 0
            ]
        ]);

        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food1->id,
            'grams' => 100,
        ]);

        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food2->id,
            'grams' => 120,
        ]);
    }

    public function test_api_quick_add_multiple_foods_handles_partial_failures()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food1 = Food::create([
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'is_global' => true,
        ]);

        // Mock AI failure for the second food
        OpenAI::fake([
            new \Exception('API Error'),
        ]);

        $response = $this->postJson('/api/portions/quick-add', [
            'slug_grams' => 'chicken_breast-150, unknown_food-200',
        ]);

        $response->assertStatus(207); // Multi-Status
        $response->assertJsonStructure([
            'results',
            'summary' => ['total', 'successful', 'failed', 'ai_created'],
            'errors',
            'message'
        ]);

        $response->assertJson([
            'summary' => [
                'total' => 2,
                'successful' => 1,
                'failed' => 1,
                'ai_created' => 0
            ]
        ]);

        // Should still add the successful food
        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food1->id,
            'grams' => 150,
        ]);
    }

    public function test_api_quick_add_multiple_foods_validation_errors()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/portions/quick-add', [
            'slug_grams' => 'chicken_breast-150, invalid-format-here',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
        $response->assertJsonFragment(['error' => "Invalid format: 'invalid-format-here'. Use: slug-grams (e.g., chicken_breast-150)"]);
    }

    public function test_api_quick_add_multiple_foods_empty_input()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/portions/quick-add', [
            'slug_grams' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }
}
