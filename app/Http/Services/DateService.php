<?php

namespace App\Http\Services;


use Carbon\Carbon;

class DateService
{
    public function extractDateFromApi(array $dateArray): string
    {
        if (count($dateArray) === 3) {
            $date = implode('-', array_reverse($dateArray));
            return Carbon::parse($date)->format('Y-m-d');
        }

        return '';
    }
}
