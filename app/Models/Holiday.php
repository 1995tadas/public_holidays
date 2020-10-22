<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    public function getHolidaysFromDatabase(int $year, string $country, $region = '')
    {
        $yearCombinationModel = YearCombination::where('year', $year)->where('country', $country);
        if ($region) {
            $yearCombinationModel = $yearCombinationModel->where('region', $region);
        }
        $yearCombinationModel = $yearCombinationModel->first();
        if (!$yearCombinationModel) {
            return $yearCombinationModel;
        }

        $holidayModel = $this::where('year_combination_id', $yearCombinationModel->id)->get();
        $listOfHolidays = [];
        foreach ($holidayModel as $holiday) {
            $carbon = Carbon::parse($holiday->date);
            $month = $carbon->format('F');
            $day = $carbon->day;
            $listOfHolidays[$month][$day] = $holiday->name;
        }
        return [
            'holidays' => $listOfHolidays,
            'total' => $yearCombinationModel->total,
            'longestStreak' => $yearCombinationModel->streak,
        ];
    }
}
