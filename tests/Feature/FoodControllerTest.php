<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Food;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FoodControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_foods_index_requires_authentication()
    {
        $response = $this->get('/foods');

        $response->assertRedirect('/login');
    }

    public function test_foods_index_shows_user_and_global_foods()
    {
        $user = User::factory()->create();
        
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

        $response = $this->actingAs($user)->get('/foods');

        $response->assertStatus(200);
        $response->assertSee('Rice');
        $response->assertSee('My Food');
    }

    public function test_foods_create_page_loads()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/foods/create');

        $response->assertStatus(200);
        $response->assertSee('name');
        $response->assertSee('slug');
    }

    public function test_can_create_food()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/foods', [
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
        ]);

        $response->assertRedirect('/foods');
        $this->assertDatabaseHas('foods', [
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'user_id' => $user->id,
        ]);
    }

    public function test_can_edit_own_food()
    {
        $user = User::factory()->create();
        
        $food = Food::create([
            'name' => 'Original Name',
            'slug' => 'original_name',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/foods/{$food->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Original Name');
    }

    public function test_cannot_edit_global_food()
    {
        $user = User::factory()->create();
        
        $food = Food::create([
            'name' => 'Global Food',
            'slug' => 'global_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'is_global' => true,
        ]);

        $response = $this->actingAs($user)->get("/foods/{$food->id}/edit");

        $response->assertStatus(403);
    }

    public function test_can_update_own_food()
    {
        $user = User::factory()->create();
        
        $food = Food::create([
            'name' => 'Original Name',
            'slug' => 'original_name',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put("/foods/{$food->id}", [
            'name' => 'Updated Name',
            'slug' => 'updated_name',
            'kcal_per_100g' => 150,
            'protein_per_100g' => 15,
            'carbs_per_100g' => 5,
            'fat_per_100g' => 7,
        ]);

        $response->assertRedirect('/foods');
        $this->assertDatabaseHas('foods', [
            'id' => $food->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_own_food()
    {
        $user = User::factory()->create();
        
        $food = Food::create([
            'name' => 'Food to Delete',
            'slug' => 'food_to_delete',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/foods/{$food->id}");

        $response->assertRedirect('/foods');
        $this->assertDatabaseMissing('foods', ['id' => $food->id]);
    }

    public function test_cannot_delete_global_food()
    {
        $user = User::factory()->create();
        
        $food = Food::create([
            'name' => 'Global Food',
            'slug' => 'global_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'is_global' => true,
        ]);

        $response = $this->actingAs($user)->delete("/foods/{$food->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('foods', ['id' => $food->id]);
    }

    public function test_food_validation_requires_all_fields()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/foods', []);

        $response->assertSessionHasErrors(['name', 'kcal_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g']);
    }

    public function test_slug_validation_requires_lowercase_and_underscores()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/foods', [
            'name' => 'Test Food',
            'slug' => 'Invalid-Slug',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
        ]);

        $response->assertSessionHasErrors(['slug']);
    }
}
