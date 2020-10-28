<?php

namespace Tests\Unit;

use App\Models\Holiday;
use App\Models\YearCombination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Faker\Factory as Faker;

class HolidayModelTest extends TestCase
{
    use RefreshDatabase;

    private $holidayModel;

    public function setUp(): void
    {
        parent::setUp();
        $this->holidayModel = new Holiday();
    }

    /**
     * @dataProvider dataProviderForGetHolidaysFromDatabase
     * @param int $year
     * @param string $country
     * @param array $expectation
     * @param string|null $region
     * @return void
     */
    public function testGetHolidaysByYearCombination(int $year, string $country, array $expectation, ?string $region = '')
    {
        $faker = Faker::create();

        $yearCombinationFactory = YearCombination::factory()->create([
            'year' => $year,
            'country' => $country,
            'total' => $expectation['total'],
            'region' => $region
        ]);
        for ($i = 0; $i < $expectation['total']; $i++) {
            Holiday::factory()->create([
                'date' => $year . '-' . $faker->unique()->date($format = 'm-d'),
                'year_combination_id' => $yearCombinationFactory->id
            ]);
        }

        $data = $this->holidayModel->getHolidaysFromDatabase($year, $country, $region);
        foreach ($expectation['keys'] as $key) {
            $this->assertArrayHasKey($key, $data);
        }

        $count = 0;
        foreach ($data['holidays'] as $month) {
            foreach ($month as $holidays) {
                $count++;
            }
        }

        $this->assertEquals(
            $expectation['total'],
            $count
        );
    }

    public function dataProviderForGetHolidaysFromDatabase(): array
    {
        return [
            'Lithuania' => [2087, 'ltu', ['total' => 15, 'keys' => ['holidays', 'total', 'longestStreak']],'zem'],
            'Random' => [2777, 'rus', ['total' => 7, 'keys' => ['holidays', 'total', 'longestStreak']]],
            'Last country' => [1993, 'lst', ['total' => 19, 'keys' => ['holidays', 'total', 'longestStreak']]],
        ];
    }
}
