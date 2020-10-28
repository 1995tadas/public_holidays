<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class Holiday extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function getHolidaysFromDatabase(int $year, string $country, ?string $region = '')
    {
        $yearCombinationModel = YearCombination::where('year', $year)->where('country', $country);
        if ($region) {
            $yearCombinationModel = $yearCombinationModel->where('region', $region);
        }

        try {
            $yearCombinationModel = $yearCombinationModel->first();
        } catch (QueryException $e) {
            return false;
        }

        if (!$yearCombinationModel) {
            return false;
        }

        $listOfHolidays = $this->getHolidaysByYearCombination($yearCombinationModel->id);
        if ($listOfHolidays) {
            return [
                'holidays' => $listOfHolidays,
                'total' => $yearCombinationModel->total,
                'longestStreak' => $yearCombinationModel->streak,
            ];
        }

        return false;
    }

    protected function getHolidaysByYearCombination(int $yearCombinationId): array
    {

        $holidayModel = $this::where('year_combination_id', $yearCombinationId)->get();
        if (!$holidayModel) {
            return $holidayModel;
        }

        $listOfHolidays = [];
        foreach ($holidayModel as $holiday) {
            $carbon = Carbon::parse($holiday->date);
            $month = $carbon->format('F');
            $day = $carbon->day;
            $listOfHolidays[$month][$day] = $holiday->name;
        }

        return $listOfHolidays;
    }
}
