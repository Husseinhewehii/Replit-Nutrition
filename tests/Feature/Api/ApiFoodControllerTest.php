<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Food;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ApiFoodControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_api_foods_index_requires_authentication()
    {
        $response = $this->getJson('/api/foods');

        $response->assertStatus(401);
    }

    public function test_api_foods_index_returns_accessible_foods()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $globalFood = Food::create([
            'name' => 'Rice',
            'slug' => 'rice',
            'kcal_per_100g' => 130,
            'protein_per_100g' => 2.7,
            'carbs_per_100g' => 28,
            'fat_per_100g' => 0.3,
            'is_global' => true,
        ]);

        $userFood = Food::create([
            'name' => 'My Food',
            'slug' => 'my_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson('/api/foods');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_api_can_create_food()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/foods', [
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'Chicken Breast']);
        $this->assertDatabaseHas('foods', [
            'name' => 'Chicken Breast',
            'user_id' => $user->id,
        ]);
    }

    public function test_api_can_show_food()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::create([
            'name' => 'Rice',
            'slug' => 'rice',
            'kcal_per_100g' => 130,
            'protein_per_100g' => 2.7,
            'carbs_per_100g' => 28,
            'fat_per_100g' => 0.3,
            'is_global' => true,
        ]);

        $response = $this->getJson("/api/foods/{$food->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Rice']);
    }

    public function test_api_can_update_own_food()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::create([
            'name' => 'Original Name',
            'slug' => 'original_name',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        $response = $this->putJson("/api/foods/{$food->id}", [
            'name' => 'Updated Name',
            'slug' => 'updated_name',
            'kcal_per_100g' => 150,
            'protein_per_100g' => 15,
            'carbs_per_100g' => 5,
            'fat_per_100g' => 7,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_api_cannot_update_global_food()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::create([
            'name' => 'Global Food',
            'slug' => 'global_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'is_global' => true,
        ]);

        $response = $this->putJson("/api/foods/{$food->id}", [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_api_can_delete_own_food()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $food = Food::create([
            'name' => 'Food to Delete',
            'slug' => 'food_to_delete',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson("/api/foods/{$food->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('foods', ['id' => $food->id]);
    }

    public function test_api_food_validation_requires_all_fields()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/foods', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'kcal_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g']);
    }
}
