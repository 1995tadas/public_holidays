<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class CountryService
{
    public function getCountries()
    {
        return $this->getCountriesFromApi();
    }

    protected function getCountriesFromApi()
    {
        try {
            $url = Http::get(config('app.api'), ['action' => 'getSupportedCountries']);
            return $url->json();
        } catch (\Exception $e) {
            abort(404, 'api error');
        }
    }

}
