<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DailyTotalsService;
use App\Repositories\PortionRepository;
use App\Models\Portion;
use App\Models\Food;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DailyTotalsServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected $portionRepository;
    protected $dailyTotalsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->portionRepository = $this->createMock(PortionRepository::class);
        $this->dailyTotalsService = new DailyTotalsService($this->portionRepository);
    }

    public function test_calculate_daily_totals_with_no_portions()
    {
        $this->portionRepository->expects($this->once())
            ->method('getUserPortionsByDate')
            ->with(1, '2025-10-01')
            ->willReturn(new \Illuminate\Database\Eloquent\Collection([]));

        $result = $this->dailyTotalsService->calculateDailyTotals(1, '2025-10-01');

        $this->assertEquals([
            'kcal' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fat' => 0,
        ], $result);
    }

    public function test_calculate_daily_totals_with_portions()
    {
        $food1 = new Food([
            'kcal_per_100g' => 165,
            'protein_per_100g' => 31,
            'carbs_per_100g' => 0,
            'fat_per_100g' => 3.6,
        ]);

        $food2 = new Food([
            'kcal_per_100g' => 130,
            'protein_per_100g' => 2.7,
            'carbs_per_100g' => 28,
            'fat_per_100g' => 0.3,
        ]);

        $portion1 = new Portion(['grams' => 150]);
        $portion1->setRelation('food', $food1);

        $portion2 = new Portion(['grams' => 200]);
        $portion2->setRelation('food', $food2);

        $this->portionRepository->expects($this->once())
            ->method('getUserPortionsByDate')
            ->with(1, '2025-10-01')
            ->willReturn(new \Illuminate\Database\Eloquent\Collection([$portion1, $portion2]));

        $result = $this->dailyTotalsService->calculateDailyTotals(1, '2025-10-01');

        $this->assertEquals([
            'kcal' => round((165 * 1.5) + (130 * 2), 1),
            'protein' => round((31 * 1.5) + (2.7 * 2), 1),
            'carbs' => round((0 * 1.5) + (28 * 2), 1),
            'fat' => round((3.6 * 1.5) + (0.3 * 2), 1),
        ], $result);
    }

    public function test_get_today_totals_uses_current_date()
    {
        Carbon::setTestNow('2025-10-01');

        $this->portionRepository->expects($this->once())
            ->method('getUserPortionsByDate')
            ->with(1, '2025-10-01')
            ->willReturn(new \Illuminate\Database\Eloquent\Collection([]));

        $result = $this->dailyTotalsService->getTodayTotals(1);

        $this->assertEquals([
            'kcal' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fat' => 0,
        ], $result);

        Carbon::setTestNow();
    }

    public function test_calculate_daily_totals_rounds_correctly()
    {
        $food = new Food([
            'kcal_per_100g' => 123.456,
            'protein_per_100g' => 12.345,
            'carbs_per_100g' => 23.456,
            'fat_per_100g' => 4.567,
        ]);

        $portion = new Portion(['grams' => 75]);
        $portion->setRelation('food', $food);

        $this->portionRepository->expects($this->once())
            ->method('getUserPortionsByDate')
            ->with(1, '2025-10-01')
            ->willReturn(new \Illuminate\Database\Eloquent\Collection([$portion]));

        $result = $this->dailyTotalsService->calculateDailyTotals(1, '2025-10-01');

        $this->assertEquals(92.6, $result['kcal']);
        $this->assertEquals(9.3, $result['protein']);
        $this->assertEquals(17.6, $result['carbs']);
        $this->assertEquals(3.4, $result['fat']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
