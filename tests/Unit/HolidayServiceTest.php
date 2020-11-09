<?php

namespace Tests\Unit;

use App\Http\Services\HolidayService;
use App\Models\Holiday;
use App\Models\YearCombination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Faker\Factory as Faker;

class HolidayServiceTest extends TestCase
{
    use RefreshDatabase;

    private $holidayService;

    public function setUp(): void
    {
        parent::setUp();
        $this->holidayService = new HolidayService();
    }

    /**
     * @dataProvider dataProviderForIsTodayHoliday
     * @param array $holidays
     * @param string $date
     * @param string $expectation
     * @return void
     */
    public function testIsTodayHoliday(array $holidays, string $date, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->holidayService->isTodayHoliday($holidays, $date)
        );
    }

    public function dataProviderForIsTodayHoliday(): array
    {
        return [
            'holiday' => [
                [
                    'January' => [2 => 'January holiday'],
                    'October' => [27 => 'October Holiday']
                ],
                '2020-10-27',
                'holiday'
            ],
            'free day' => [
                [
                    'December' => [2 => 'December holiday'],
                    'October' => [17 => 'October Holiday', 18 => 'Second Holiday'],
                    'November' => [07 => 'November Holiday'],
                ],
                '2020-10-25',
                'free day'
            ],
            'workday' => [
                [
                    'June' => [4 => 'June holiday'],
                    'July' => [15 => 'July Holiday'],
                    'November' => [01 => 'November Holiday'],
                ],
                '2020-10-27',
                'workday'
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForInitAPI
     * @param int $year
     * @param string $country
     * @param string|null $region
     * @param array $expectation
     * @return void
     */
    public function testInitAPI(int $year, string $country, array $expectation, ?string $region = '')
    {
        $data = $this->holidayService->initAPI($year, $country, $region);
        $this->assertCount(
            $expectation['count'],
            $data
        );

        foreach ($expectation['keys'] as $key) {
            $this->assertArrayHasKey(
                $key,
                $data[0]
            );
        }
    }

    public function dataProviderForInitAPI(): array
    {
        return [
            'Lithuania' => [
                2020,
                'ltu',
                ['count' => 16, 'keys' => ['date', 'name']]
            ],
            'Future Lithuania' => [
                5555,
                'ltu',
                ['count' => 16, 'keys' => ['date', 'name']]
            ],
            'Germany' => [
                2020,
                'ger',
                ['count' => 11, 'keys' => ['date', 'name']],
                'rp'
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForHolidaysInARow
     * @param int $year
     * @param string $country
     * @param int $expectation
     * @param string|null $region
     * @return void
     */
    public function testHolidaysInARow(int $year, string $country, int $expectation, ?string $region = '')
    {
        $holidays = $this->holidayService->initAPI($year, $country, $region);
        $longestStreak = [
            'date' => ['week' => 0, 'day' => 0, 'month' => 0],
            'data' => ['streak' => 1, 'record' => 1]
        ];
        foreach ($holidays as $holiday) {
            $longestStreak = $this->holidayService->holidaysInARow($holiday['date'], $year, $longestStreak);
        }

        $this->assertEquals(
            $expectation,
            $longestStreak['data']['record']
        );
    }

    public function dataProviderForHolidaysInARow(): array
    {
        return [
            'Lithuania' => [2020, 'ltu', 4],
            'Latvia' => [2020, 'lva', 4],
            'Estonia' => [2014, 'est', 5],
        ];
    }

    /**
     * @dataProvider dataProviderForGetHolidaysFromApi
     * @param int $year
     * @param string $country
     * @param string|null $region
     * @param array $expectation
     * @return void
     */
    public function testGetHolidaysFromApi(int $year, string $country, array $expectation, ?string $region = '')
    {
        $data = $this->holidayService->getHolidaysFromApi($year, $country, $region);
        foreach ($expectation['keys'] as $key) {
            $this->assertArrayHasKey(
                $key,
                $data
            );
        }

        $this->assertCount(
            $expectation['count'],
            $data['holidays']
        );
    }

    public function dataProviderForGetHolidaysFromApi(): array
    {
        return [
            'Lithuania' => [
                2014,
                'ltu',
                ['count' => 10, 'keys' => ['holidays', 'total', 'longestStreak']]
            ],
            'China' => [
                2020,
                'chn',
                ['count' => 5, 'keys' => ['holidays', 'total', 'longestStreak']]
            ],
            'Germany' => [
                2020,
                'ger',
                ['count' => 7, 'keys' => ['holidays', 'total', 'longestStreak']],
                'sn'
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForPrepareDataForStoring
     * @param int $year
     * @param string $country
     * @param array $data
     * @param array $expectation
     * @param string|null $region
     * @return void
     */
    public function testPrepareDataForStoring(int $year, string $country, array $data, array $expectation, ?string $region = '')
    {
        $this->assertEquals(
            $expectation,
            $this->holidayService->prepareDataForStoring($year, $country, $data, $region)
        );
    }

    public function dataProviderForPrepareDataForStoring(): array
    {
        return [
            'Lithuania' => [
                2014,
                'ltu',
                ['total' => 10, 'longestStreak' => 4],
                [
                    'year' => 2014,
                    'country' => 'ltu',
                    'total' => 10,
                    'streak' => 4
                ]
            ],
            'Australia' => [
                2020,
                'aus',
                ['total' => 7, 'longestStreak' => 78],
                [
                    'year' => 2020,
                    'country' => 'aus',
                    'total' => 7,
                    'streak' => 78,
                    'region' => 'nsw'
                ],
                'nsw'
            ],
            'Colombia' => [
                6969,
                'col',
                ['total' => 11, 'longestStreak' => 44],
                [
                    'year' => 6969,
                    'country' => 'col',
                    'total' => 11,
                    'streak' => 44,
                ]
            ]
        ];
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

        $data = $this->holidayService->getHolidaysFromDatabase($year, $country, $region);
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
