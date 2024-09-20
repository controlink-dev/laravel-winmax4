<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4Document;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4CurrencyService;
use Controlink\LaravelWinmax4\app\Services\Winmax4DocumentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Winmax4DocumentsController extends Controller
{
    /**
     * @var Winmax4CurrencyService The service responsible for handling interactions with the Winmax4 API.
     */
    protected $winmax4Service;

    /**
     * Init Constructor for Winmax4DocumentsController.
     *
     * This constructor initializes the `Winmax4DocumentsController` based on the settings retrieved from the database.
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
            $this->winmax4Service = new Winmax4DocumentService(true);
        }else{
            $this->winmax4Service = new Winmax4DocumentService(
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
     * Get documents from the Winmax4 API.
     *
     * This method queries the Winmax4Currency model using Eloquent ORM to fetch
     * all records from the corresponding database table. It then returns these records
     * as a JSON response with an HTTP status code of 200 (OK).
     *
     * ### Usage Example
     *
     * ```php
     * $response = $this->getDocuments();
     * ```
     *
     * ### Response Format
     *
     * The response is a JSON-encoded array of objects, each representing a record from the
     * Winmax4Currency model. The format of each object corresponds to the columns of the
     * underlying database table.
     *
     * ### Return Type
     *
     * | Type          | Description                                                     |
     * |---------------|-----------------------------------------------------------------|
     * | `JsonResponse`| A JSON response containing the fetched documents with status 200.|
     *
     * ### Possible Exceptions
     *
     * This method generally doesn't throw exceptions directly, but underlying database
     * connectivity issues or application errors might trigger exceptions at a higher level.
     *
     * @return JsonResponse Returns a JSON response with all documents.
     */
    public function getDocuments(): JsonResponse
    {
        return response()->json(Winmax4Document::get(), 200);
    }

    public function postDocuments(Request $request): JsonResponse
    {
        $request->validate([
            'documentType' => 'required|string',
            'entity' => 'required|string',
            'details.*' => 'required|array',
            'details.*.ArticleCode' => 'required',
            'details.*.Quantity' => 'required',
            'details.*.DiscountPercentage1' => 'required',
            'details.*.DiscountPercentage2' => 'required',
        ]);

        dd($request->all());

        return response()->json($this->winmax4Service->postDocuments(
            $request->documentType,
            $request->entity,
            $request->details
        ), 200);
    }
}
