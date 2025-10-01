<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Food;
use App\Models\Portion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PortionControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_entries_index_requires_authentication()
    {
        $response = $this->get('/entries');

        $response->assertRedirect('/login');
    }

    public function test_entries_index_shows_portions_grouped_by_date()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();

        Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/entries');

        $response->assertStatus(200);
        $response->assertViewHas(['portions', 'dailyTotals']);
    }

    public function test_can_add_portion_with_food_id()
    {
        $user = User::factory()->create();
        $food = Food::create([
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'is_global' => true,
        ]);

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post('/portions/add', [
                'food_id' => $food->id,
                'grams' => 150,
            ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
        ]);
    }

    public function test_can_quick_add_portion_with_slug_grams()
    {
        $user = User::factory()->create();
        $food = Food::create([
            'name' => 'Chicken Breast',
            'slug' => 'chicken_breast',
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
            'is_global' => true,
        ]);

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post('/portions/quick-add', [
                'quick_add' => 'chicken_breast-150',
            ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
        ]);
    }

    public function test_quick_add_fails_with_invalid_format()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/portions/quick-add', [
            'slug_grams' => 'invalid-format-here',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_quick_add_fails_with_non_existent_food()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/portions/quick-add', [
            'slug_grams' => 'nonexistent_food-150',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_cannot_add_portion_for_other_users_food()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $food = Food::create([
            'name' => 'User 1 Food',
            'slug' => 'user_1_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user1->id,
            'is_global' => false,
        ]);

        $response = $this->actingAs($user2)->post('/portions/add', [
            'food_id' => $food->id,
            'grams' => 100,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('portions', [
            'user_id' => $user2->id,
            'food_id' => $food->id,
        ]);
    }

    public function test_portion_validation_requires_positive_grams()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();

        $response = $this->actingAs($user)->post('/portions/add', [
            'food_id' => $food->id,
            'grams' => -50,
        ]);

        $response->assertSessionHasErrors(['grams']);
    }

    public function test_portion_validation_requires_grams()
    {
        $user = User::factory()->create();
        $food = Food::factory()->create();

        $response = $this->actingAs($user)->post('/portions/add', [
            'food_id' => $food->id,
        ]);

        $response->assertSessionHasErrors(['grams']);
    }
}
