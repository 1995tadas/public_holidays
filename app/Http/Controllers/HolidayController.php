<?php

namespace App\Http\Controllers;

use App\Http\Requests\HolidayRequest;
use App\Http\Services\HolidayService;
use App\Models\Holiday;
use App\Models\YearCombination;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;

class HolidayController extends Controller
{

    public function create()
    {
        try {
            $url = Http::get("https://kayaposoft.com/enrico/json/v2.0?action=getSupportedCountries");
            return view('holidays.create', ['countries' => $url->json()]);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function store(array $data, int $year, string $country, $region = '')
    {
        $yearCombinationModel = new YearCombination();
        $holidayService = new HolidayService();
        $preparedData = $holidayService->prepareDataForStoring($year, $country, $data, $region);
        try {
            $created = $yearCombinationModel::create($preparedData);
        } catch (QueryException $e) {
            return $e;
        }

        if ($created) {
            $holidayModel = new Holiday();
            $date = [];
            foreach ($data['holidays'] as $month => $day) {
                $monthNumber = Carbon::parse($month)->month;
                foreach ($day as $dayNumber => $name) {
                    $date[] = [
                        'date' => $year . '-' . $monthNumber . '-' . $dayNumber,
                        'name' => $name,
                        'year_combination_id' => $created->id];
                }
            }

            return $holidayModel::insert($date);
        }
    }

    public function show(HolidayRequest $request)
    {
        $holiday = new Holiday();
        $holidayService = new HolidayService();
        $dataFromDatabase = $holiday->getHolidaysFromDatabase($request->year, $request->country, $request->region);
        if ($dataFromDatabase) {
            $response = $dataFromDatabase;
        } else {
            $holidayService = new HolidayService();
            $response = $holidayService->getHolidaysFromApi($request->year, $request->country, $request->region);
            if (array_key_exists('error', $response)) {
                return response()->json($response);
            }
        }

        $response['today'] = $holidayService->isTodayHoliday($response['holidays']);
        return response()->json($response);
    }
}
