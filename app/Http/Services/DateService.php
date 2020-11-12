<?php

namespace App\Http\Services;


class DateService
{
    public function mergeDate(array $dateArray): string
    {
        if (count($dateArray) === 3) {
            return implode('-', array_reverse($dateArray));
        }

        return '';
    }

    public function splitDate(string $dateString): array
    {
        $dateArray = explode('-', $dateString);
        return [
            'day' => $dateArray[2],
            'month' => $dateArray[1],
            'year' => $dateArray[0],
        ];
    }
}
