<?php

namespace Tests\Unit;

use App\Http\Services\CountryService;
use Tests\TestCase;

class CountryServiceTest extends TestCase
{
    private $countryService;

    public function setUp(): void
    {
        parent::setUp();
        $this->countryService = new CountryService();
    }

    /**
     * @dataProvider dataProviderForGetCountries
     * @param array $expectation
     * @return void
     */
    public function testIsTodayHoliday(array $expectation)
    {
        $countries = $this->countryService->getCountries();
        $this->assertIsArray(
            $countries
        );
        foreach ($countries as $country) {
            $this->assertEquals(
                $expectation['countyCodeNumber'], strlen($country['countryCode'])
            );
            $this->assertIsString($country['fullName']);
            foreach ($expectation['dateKeys'] as $key) {
                $this->assertArrayHasKey($key, $country['toDate']);
                $this->assertIsNumeric($country['toDate'][$key]);
                $this->assertArrayHasKey($key, $country['fromDate']);
                $this->assertIsNumeric($country['fromDate'][$key]);
                if ($country['regions']) {
                    foreach ($country['regions'] as $region) {
                        $this->assertIsString($region);
                    }
                }
            }
        }
    }

    public function dataProviderForGetCountries(): array
    {
        return [
            'first for api' => [[
                'countyCodeNumber' => 3,
                'dateKeys' => ['day', 'month', 'year']
            ]],
            'second for database' => [[
                'countyCodeNumber' => 3,
                'dateKeys' => ['day', 'month', 'year']
            ]]
        ];
    }
}
