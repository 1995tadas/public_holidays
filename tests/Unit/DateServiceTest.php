<?php

namespace Tests\Unit;

use App\Http\Services\DateService;
use PHPUnit\Framework\TestCase;

class DateServiceTest extends TestCase
{
    private $dateService;

    public function setUp(): void
    {
        parent::setUp();
        $this->dateService = new DateService();
    }

    /**
     * @dataProvider dataProviderForMergeDate
     * @param array $dateArray
     * @param string $expectation
     * @return void
     */
    public function testIsTodayHoliday(array $dateArray, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->dateService->mergeDate($dateArray)
        );
    }

    public function dataProviderForMergeDate(): array
    {
        return [
            'date starting at day' => [[
                'day' => '31',
                'month' => '12',
                'year' => '2020'
            ], '2020-12-31'],
            'date starting at year' => [[
                'year' => '188820',
                'month' => '12',
                'day' => '31'
            ], '31-12-188820'],
            'integer values' => [[
                'year' => 7777,
                'month' => 12,
                'day' => 31
            ], '31-12-7777']
        ];
    }

    /**
     * @dataProvider dataProviderForSplitDate
     * @param string $dateString
     * @param array $expectation
     * @return void
     */
    public function testSplitDate(string $dateString, array $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->dateService->splitDate($dateString)
        );
    }

    public function dataProviderForSplitDate(): array
    {
        return [
            'normal date' => [
                '1995-12-04',
                ['day' => '04', 'month' => '12', 'year' => '1995']
            ]
        ];

    }
}
