<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4Tax;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Controlink\LaravelWinmax4\app\Services\Winmax4TaxService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class Winmax4TaxesController extends Controller
{
    /**
     * @var Winmax4TaxService The service responsible for handling interactions with the Winmax4 API.
     */
    protected $winmax4Service;

    /**
     * Init Constructor for Winmax4TaxesController.
     *
     * This constructor initializes the `Winmax4TaxService` based on the settings retrieved from the database.
     * If no settings are found for the current license, the service is initialized in test mode.
     *
     * ### Configuration Details
     *
     * The constructor retrieves Winmax4 settings from the database using the following parameters:
     *
     * - **License Column**: The database column defined in `winmax4.license_column` config.
     * - **License Session Key**: The session key for the current user's license, as defined in `winmax4.license_session_key`.
     *
     * Depending on whether the settings are found, the service is initialized differently:
     *
     * | Condition              | Service Initialization                             |
     * |------------------------|-----------------------------------------------------|
     * | No settings found      | Test mode (`isTestMode = true`)                     |
     * | Settings found         | Production mode with retrieved settings applied     |
     *
     * ### Settings Retrieved from the Database
     *
     * | Setting      | Description                                           |
     * |--------------|-------------------------------------------------------|
     * | `url`        | API endpoint URL for Winmax4                          |
     * | `company_code` | Code representing the company in Winmax4             |
     * | `username`   | Username for authentication                           |
     * | `password`   | Password for authentication                           |
     * | `n_terminal` | Terminal number used for transactions                 |
     *
     * @throws ModelNotFoundException If no settings are found for the current license.
     */
    public function __construct()
    {
        $winmaxSettings = Winmax4Setting::where(config('winmax4.license_column'), session(config('winmax4.license_session_key')))->first();

        if(!$winmaxSettings) {
            $this->winmax4Service = new Winmax4TaxService(true);
        }else{
            $this->winmax4Service = new Winmax4TaxService(
                false,
                $winmaxSettings->url,
                $winmaxSettings->company_code,
                $winmaxSettings->username,
                $winmaxSettings->password,
                $winmaxSettings->n_terminal
            );
        }
    }

    /**
     * Get taxes from the Winmax4 API.
     *
     * This method queries the Winmax4Tax model using Eloquent ORM to fetch
     * all records from the corresponding database table. It then returns these records
     * as a JSON response with an HTTP status code of 200 (OK).
     *
     * ### Usage Example
     *
     * ```php
     * $response = $this->getTaxes();
     * ```
     *
     * ### Response Format
     *
     * The response is a JSON-encoded array of objects, each representing a record from the
     * Winmax4Tax model. The format of each object corresponds to the columns of the
     * underlying database table.
     *
     * ### Return Type
     *
     * | Type          | Description                                                     |
     * |---------------|-----------------------------------------------------------------|
     * | `JsonResponse`| A JSON response containing the fetched taxes with status 200.|
     *
     * ### Possible Exceptions
     *
     * This method generally doesn't throw exceptions directly, but underlying database
     * connectivity issues or application errors might trigger exceptions at a higher level.
     *
     * @return JsonResponse Returns a JSON response with all taxes.
     */
    public function getTaxes(): JsonResponse
    {

        $taxes = Winmax4Tax::with('taxRates')->get()->map(function ($tax){

            $value = 0;
            $is_percentage = true;
            $rates = [];

            foreach($tax->taxRates as $rate){
                $is_percentage = true;
                if($rate->percentage == 0){
                    if($rate->fixedAmount != 0){
                        $value = $rate->fixedAmount;
                        $is_percentage = false;
                    }else{
                        $value = 0;
                    }
                }else{
                    $value = $rate->percentage;
                }


                $rates[] = [
                    'is_percentage' => $is_percentage,
                    'tax_rate' => $value,
                ];
            }

            return [
                'tax_code' => $tax->code,
                'tax_name' => $tax->designation,
                'is_active' => $tax->is_active,
                'tax_rates' => $rates,
            ];
        });

        return response()->json($taxes, 200);
    }
}
