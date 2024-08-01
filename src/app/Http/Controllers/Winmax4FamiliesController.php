<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubFamily;
use Controlink\LaravelWinmax4\app\Services\Winmax4DocumentTypeService;
use Controlink\LaravelWinmax4\app\Services\Winmax4FamilyService;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class Winmax4FamiliesController extends Controller
{
    /**
     * @var Winmax4FamilyService The service responsible for handling interactions with the Winmax4 API.
     */
    protected $winmax4Service;

    /**
     * Init Constructor for Winmax4FamiliesController.
     *
     * This constructor initializes the `Winmax4FamilyService` based on the settings retrieved from the database.
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
            $this->winmax4Service = new Winmax4FamilyService(true);
        }else{
            $this->winmax4Service = new Winmax4FamilyService(
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
     * Get all families with subfamilies and sub-subfamilies.
     *
     * This method queries the `Winmax4Family` model and eagerly loads associated
     * subfamilies and sub-subfamilies. It returns a JSON response containing all
     * family records with their respective nested relationships. The response has
     * an HTTP status code of 200 (OK).
     *
     * ### Usage Example
     *
     * ```php
     * $response = $this->getFamilies();
     * ```
     *
     * ### Response Format
     *
     * The response is a JSON-encoded array of objects, each representing a family
     * with nested subfamily and sub-subfamily data. The typical structure is:
     *
     * ```json
     * [
     *     {
     *         "id": 1,
     *         "code": "FAM001",
     *         "designation": "Electronics",
     *         "subFamilies": [
     *             {
     *                 "id": 10,
     *                 "code": "SUBF001",
     *                 "designation": "Smartphones",
     *                 "subSubFamilies": [
     *                     {
     *                         "id": 100,
     *                         "code": "SUBSUB001",
     *                         "designation": "Android Phones"
     *                     },
     *                     // Additional sub-subfamilies...
     *                 ]
     *             },
     *             // Additional subfamilies...
     *         ]
     *     },
     *     // Additional families...
     * ]
     * ```
     *
     * ### Return Type
     *
     * | Type           | Description                                                     |
     * |----------------|-----------------------------------------------------------------|
     * | `JsonResponse` | A JSON response containing all families with status code 200.   |
     *
     * ### Possible Exceptions
     *
     * This method typically doesn't throw exceptions directly, but underlying database
     * connectivity issues or application errors might trigger exceptions at a higher level.
     * It's recommended to handle these exceptions to ensure graceful error handling.
     *
     * @return JsonResponse Returns a JSON response with all families and nested relationships.
     */
    public function getFamilies(): JsonResponse
    {
        return response()->json(Winmax4Family::with('subFamilies.subSubFamilies')->get(), 200);
    }

    /**
     * Get subfamilies for a specific family.
     *
     * This method fetches a specific family by its code from the `Winmax4Family` model
     * and loads its associated subfamilies. It returns a JSON response containing the
     * subfamilies for the specified family code with an HTTP status code of 200 (OK).
     *
     * ### Usage Example
     *
     * ```php
     * $response = $this->getSubFamilies('FAM001');
     * ```
     *
     * ### Response Format
     *
     * The response is a JSON-encoded array of objects, each representing a subfamily
     * associated with the specified family code. The typical structure is:
     *
     * ```json
     * [
     *     {
     *         "id": 10,
     *         "code": "SUBF001",
     *         "designation": "Smartphones",
     *         // Additional attributes...
     *     },
     *     // Additional subfamilies...
     * ]
     * ```
     *
     * ### Return Type
     *
     * | Type           | Description                                                     |
     * |----------------|-----------------------------------------------------------------|
     * | `JsonResponse` | A JSON response containing the subfamilies with status code 200.|
     *
     * ### Possible Exceptions
     *
     * This method may throw exceptions if the specified family code does not exist or
     * if there are underlying database connectivity issues:
     * - `Illuminate\Database\Eloquent\ModelNotFoundException` if the family is not found.
     * - `Exception` for any general database errors.
     *
     * It's advisable to handle these exceptions to provide meaningful error responses.
     *
     * @param string $family_code The code of the family whose subfamilies are to be retrieved.
     * @return JsonResponse Returns a JSON response with subfamilies for the given family code.
     */
    public function getSubFamilies(string $family_code): JsonResponse
    {
        $family = Winmax4Family::with('subFamilies')->where('code', $family_code)->first();
        return response()->json($family->subFamilies, 200);
    }

    /**
     * Retrieve sub-subfamilies for a given subfamily code.
     *
     * This method fetches a specific subfamily by its code from the `Winmax4SubFamily` model
     * and loads its associated sub-subfamilies. It returns a JSON response containing the
     * sub-subfamilies for the specified subfamily code with an HTTP status code of 200 (OK).
     *
     * ### Usage Example
     *
     * ```php
     * $response = $this->getSubSubFamilies('SUBF001');
     * ```
     *
     * ### Response Format
     *
     * The response is a JSON-encoded array of objects, each representing a sub-subfamily
     * associated with the specified subfamily code. The typical structure is:
     *
     * ```json
     * [
     *     {
     *         "id": 100,
     *         "code": "SUBSUB001",
     *         "designation": "Android Phones",
     *         // Additional attributes...
     *     },
     *     // Additional sub-subfamilies...
     * ]
     * ```
     *
     * ### Return Type
     *
     * | Type           | Description                                                            |
     * |----------------|------------------------------------------------------------------------|
     * | `JsonResponse` | A JSON response containing the sub-subfamilies with status code 200.   |
     *
     * ### Possible Exceptions
     *
     * This method may throw exceptions if the specified subfamily code does not exist or
     * if there are underlying database connectivity issues:
     * - `Illuminate\Database\Eloquent\ModelNotFoundException` if the subfamily is not found.
     * - `Exception` for any general database errors.
     *
     * It's recommended to handle these exceptions to provide meaningful error responses.
     *
     * @param string $sub_family_code The code of the subfamily whose sub-subfamilies are to be retrieved.
     * @return JsonResponse Returns a JSON response with sub-subfamilies for the given subfamily code.
     */
    public function getSubSubFamilies(string $sub_family_code): JsonResponse
    {
        $sub_family = Winmax4SubFamily::with('subSubFamilies')->where('code', $sub_family_code)->first();
        return response()->json($sub_family->subSubFamilies, 200);
    }
}
