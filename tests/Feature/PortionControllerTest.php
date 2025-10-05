<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Food;
use App\Models\Portion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use OpenAI\Laravel\Facades\OpenAI;

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

    public function test_quick_add_shows_user_friendly_error_when_ai_fails()
    {
        $user = User::factory()->create();

        // Mock AI failure
        OpenAI::fake([
            new \Exception('API Error'),
        ]);

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post('/portions/quick-add', [
                'quick_add' => 'unknown_food-150',
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors(['quick_add']);
        $response->assertSessionHas('errors', function ($errors) {
            return str_contains($errors->first('quick_add'), 'Unable to find nutrition information for: unknown_food');
        });
    }

    public function test_can_quick_add_multiple_foods_comma_separated()
    {
        $user = User::factory()->create();
        
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

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post('/portions/quick-add', [
                'quick_add' => 'chicken_breast-150, rice-200',
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success', 'Successfully added 2 foods!');
        
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

    public function test_can_quick_add_multiple_foods_newline_separated()
    {
        $user = User::factory()->create();
        
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

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post('/portions/quick-add', [
                'quick_add' => "apple-100\nbanana-120",
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success', 'Successfully added 2 foods!');
        
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

    public function test_quick_add_multiple_foods_handles_partial_failures()
    {
        $user = User::factory()->create();
        
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

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post('/portions/quick-add', [
                'quick_add' => 'chicken_breast-150, unknown_food-200',
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors(['quick_add']);
        $response->assertSessionHas('errors', function ($errors) {
            return str_contains($errors->first('quick_add'), 'Some foods could not be added') &&
                   str_contains($errors->first('quick_add'), '1 foods were added successfully');
        });
        
        // Should still add the successful food
        $this->assertDatabaseHas('portions', [
            'user_id' => $user->id,
            'food_id' => $food1->id,
            'grams' => 150,
        ]);
    }

    public function test_quick_add_multiple_foods_validation_errors()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post('/portions/quick-add', [
                'quick_add' => 'chicken_breast-150, invalid-format-here',
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors(['quick_add']);
        $response->assertSessionHas('errors', function ($errors) {
            return str_contains($errors->first('quick_add'), 'Invalid format');
        });
    }

    public function test_quick_add_multiple_foods_empty_input()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post('/portions/quick-add', [
                'quick_add' => '',
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors(['quick_add']);
    }
}
