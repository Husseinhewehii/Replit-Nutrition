<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Food;
use App\Models\Portion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DashboardTest extends TestCase
{
    use DatabaseMigrations;

    public function test_dashboard_requires_authentication()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_shows_today_totals()
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

        Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 150,
            'consumed_at' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('todayTotals');
    }

    public function test_dashboard_shows_accessible_foods()
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
            'name' => 'My Custom Food',
            'slug' => 'my_custom_food',
            'kcal_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 10,
            'fat_per_100g' => 5,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Rice');
        $response->assertSee('My Custom Food');
    }

    public function test_dashboard_shows_todays_portions()
    {
        $user = User::factory()->create();
        
        $food = Food::create([
            'name' => 'Salmon',
            'slug' => 'salmon',
            'kcal_per_100g' => 208,
            'protein_per_100g' => 20,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 13,
            'is_global' => true,
        ]);

        Portion::create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'grams' => 200,
            'consumed_at' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Salmon');
    }
}
