<?php

namespace App\Http\Services;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Support\Facades\Http;

class CountryService
{
    public function getCountries(): array
    {
        $countriesFromDatabase = $this->getCountriesFromDatabase();
        if (!empty($countriesFromDatabase)) {
            return $countriesFromDatabase;
        } else {
            return $this->getCountriesFromApi();
        }
    }

    protected function getCountriesFromApi(): array
    {
        try {
            $url = Http::get(config('app.api'), ['action' => 'getSupportedCountries']);
            $this->storeCountriesToDatabase($url->json());
            return $url->json();
        } catch (\Exception $e) {
            abort(404, 'api error');
        }
    }

    protected function getCountriesFromDatabase(): array
    {
        $countries = Country::with(['regions' => function ($query) {
            $query->select('country_id', 'region');
        }])->get(['id', 'countryCode', 'fullName', 'fromDate', 'toDate'])->toArray();
        if ($countries) {
            return $this->prepareCountriesFromDatabase($countries);
        }

        return [];
    }

    protected function prepareCountriesFromDatabase(array $countries): array
    {
        $dateService = new DateService();
        foreach ($countries as $index => $country) {
            $countries[$index]['fromDate'] = $dateService->splitDate($country['fromDate']);
            $countries[$index]['toDate'] = $dateService->splitDate($country['toDate']);
            if ($countries[$index]['regions']) {
                foreach ($countries[$index]['regions'] as $region_index => $region) {
                    $countries[$index]['regions'][$region_index] = $region['region'];
                }
            }
        }

        return $countries;
    }

    protected function storeCountriesToDatabase(array $countries): void
    {
        $countryModel = new Country();
        $dateService = new DateService();
        foreach ($countries as $country) {
            $fromDate = $dateService->mergeDate($country['fromDate']);
            $toDate = $dateService->mergeDate($country['toDate']);
            if ($fromDate && $toDate) {
                $created = $countryModel::create([
                    'countryCode' => $country['countryCode'],
                    'fullName' => $country['fullName'],
                    'fromDate' => $fromDate,
                    'toDate' => $toDate
                ]);
                if ($created && $country['regions']) {
                    $this->storeCountryRegionsToDatabase($created->id, $country['regions']);
                }
            }
        }
    }

    protected function storeCountryRegionsToDatabase(int $countryId, array $regions): bool
    {
        $regionModel = new Region();
        $data = [];
        foreach ($regions as $region) {
            if (!empty($region)) {
                $data[] = [
                    'country_id' => $countryId,
                    'region' => $region
                ];
            }
        }

        return $regionModel::insert($data);
    }

}
