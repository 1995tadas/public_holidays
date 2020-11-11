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
        if ($countriesFromDatabase->isNotEmpty()) {
            //todo
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

    protected function getCountriesFromDatabase(): object
    {
        return Country::all();
    }

    protected function storeCountriesToDatabase(array $countries): void
    {
        $countryModel = new Country();
        $dateService = new DateService();
        foreach ($countries as $country) {
            $fromDate = $dateService->extractDateFromApi($country['fromDate']);
            $toDate = $dateService->extractDateFromApi($country['toDate']);
            if ($fromDate && $toDate) {
                $created = $countryModel::firstOrcreate([
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
