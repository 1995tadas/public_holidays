<?php

namespace App\Http\Services;


class DateService
{
    public function mergeDate(array $dateArray, string $glue = '-'): string
    {
        if (count($dateArray) === 3 && $this->areArrayItemsNumerical($dateArray)) {
            return implode($glue, array_reverse($dateArray));
        }

        return '';
    }

    public function splitDate(string $dateString, string $delimiter = '-'): array
    {
        $dateArray = explode($delimiter, $dateString);
        if (count($dateArray) === 3 && $this->areArrayItemsNumerical($dateArray)) {
            return [
                'day' => $dateArray[2],
                'month' => $dateArray[1],
                'year' => $dateArray[0],
            ];
        }
        return [];
    }

    private function areArrayItemsNumerical(array $array): bool
    {
        $numeric = true;
        foreach ($array as $item) {
            if (!is_numeric($item)) {
                $numeric = false;
                break;
            }
        }

        return $numeric;
    }
}
