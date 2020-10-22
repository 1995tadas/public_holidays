<?php

namespace App\Http\Services;

use App\Http\Controllers\HolidayController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class HolidayService
{
    public function prepareDataForStoring(int $year, string $country, array $data, $region)
    {
        $preparedData = [
            'year' => $year,
            'country' => $country,
            'total' => $data['total'],
            'streak' => $data['longestStreak']
        ];
        if ($region) {
            $preparedData['region'] = $region;
        }

        return $preparedData;
    }

    public function getFromApi(int $year, string $country, $region = '')
    {
        $preparedRegion = '';
        if (!empty($region)) {
            $preparedRegion = '&region=' . $region;
        }
        try {
            $url = Http::get("https://kayaposoft.com/enrico/json/v2.0?action=getHolidaysForYear&year=" . $year . "&country=" . $country . $preparedRegion . "&holidayType=public_holiday");
        } catch (\ErrorException $e) {
            abort(404);
        }

        if (array_key_exists('error', $url->json())) {
            return $url->json();
        }

        $listOfHolidays = [];
        $longestStreak = [
            'date' => ['week' => 0, 'day' => 0, 'month' => 0],
            'data' => ['streak' => 1, 'record' => 1]
        ];
        $count = 0;
        foreach (json_decode($url->body()) as $holiday) {
            $longestStreak = $this->holidaysInARow($holiday->date, $year, $longestStreak);
            foreach ($holiday->name as $name) {
                if ($name->lang === 'en') {
                    $month = date("F", mktime(0, 0, 0, $holiday->date->month));
                    $listOfHolidays[$month][$holiday->date->day] = $name->text;
                    $count++;
                }
            }
        }

        $response = [
            'holidays' => $listOfHolidays,
            'total' => $count,
            'longestStreak' => $longestStreak['data']['record'],
        ];
        $holidayController = new HolidayController();
        $holidayController->store($response, $year, $country, $region);
        return $response;
    }

    private function holidaysInARow(object $currentDate, int $year, array $longestStrike)
    {
        $date = $longestStrike['date'];
        $data = $longestStrike['data'];
        if ($date['day'] !== 0 && $date['month'] !== 0 && $date['week'] !== 0) {
            if ($currentDate->day !== 1) {
                if ($date['month'] === $currentDate->month && $date['day'] === $currentDate->day - 1) {
                    $data['streak']++;
                } else if (!($date['month'] === $currentDate->month && $date['day'] === $currentDate->day)) {
                    $data['streak'] = 1;
                }
            } else if ($currentDate->day === 1) {
                $lastMonthDay = Carbon::parse($year . '-' . ($date['month'] - 1))->endOfMonth()->format('d');
                if ($date['month'] === $currentDate->month - 1 && $date['day'] === $lastMonthDay) {
                    $data['streak']++;
                } else if (!($date['month'] === $currentDate->month && $date['day'] === $currentDate->day)) {
                    $data['streak'] = 1;
                }
            }
        }

        $date['day'] = $currentDate->day;
        $date['month'] = $currentDate->month;
        $date['week'] = $currentDate->dayOfWeek;


        if ($date['week'] === 5 || $date['week'] === 6) {
            $lastMonthDay = Carbon::parse($year . '-' . $date['month'])->endOfMonth()->format('d');
            if ($date['week'] === 5) {
                $data['streak'] += 2;
                $date['week'] = 7;
                $date['day'] += 2;
            } else if ($date['week'] === 6) {
                $data['streak']++;
                $date['day']++;
                $date['week'] = 7;
            }
            if ($date['day'] > $lastMonthDay) {
                $date['day'] = $date['day'] - $lastMonthDay;
                $date['month']++;
            }
        }

        if ($data['streak'] > $data['record']) {
            $data['record'] = $data['streak'];
        }

        return ['date' => $date, 'data' => $data];
    }

    public function isTodayHoliday(array $listOfHolidays)
    {
        $date = Carbon::now();
        $month = $date->format('F');
        if (array_key_exists($month, $listOfHolidays)) {
            $day = $date->day;
            if (array_key_exists($day, $listOfHolidays[$month])) {
                return 'holiday';
            }
        }

        if ($date->isWeekend()) {
            return 'free day';
        } else {
            return 'workday';
        }
    }
}
