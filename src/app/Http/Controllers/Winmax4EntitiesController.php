<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4EntityService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Winmax4EntitiesController extends Controller
{
    /**
     * @var Winmax4EntityService The service responsible for handling interactions with the Winmax4 API.
     */
    protected $winmax4Service;

    /**
     * Init Constructor for Winmax4EntitiesController.
     *
     * This constructor initializes the `Winmax4EntityService` based on the settings retrieved from the database.
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
            $this->winmax4Service = new Winmax4EntityService(true);
        }else{
            $this->winmax4Service = new Winmax4EntityService(
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
     * Get entities from the Winmax4 API.
     *
     * This method queries the Winmax4Entity model using Eloquent ORM to fetch
     * all records from the corresponding database table. It then returns these records
     * as a JSON response with an HTTP status code of 200 (OK).
     *
     * ### Usage Example
     *
     * ```php
     * $response = $this->getEntities();
     * ```
     *
     * ### Response Format
     *
     * The response is a JSON-encoded array of objects, each representing a record from the
     * Winmax4Entity model. The format of each object corresponds to the columns of the
     * underlying database table.
     *
     * ### Return Type
     *
     * | Type          | Description                                                     |
     * |---------------|-----------------------------------------------------------------|
     * | `JsonResponse`| A JSON response containing the fetched entities with status 200.|
     *
     * ### Possible Exceptions
     *
     * This method generally doesn't throw exceptions directly, but underlying database
     * connectivity issues or application errors might trigger exceptions at a higher level.
     *
     * @return JsonResponse Returns a JSON response with all entities.
     */
    public function getEntities(): JsonResponse
    {
        return response()->json(Winmax4Entity::get(), 200);
    }

    /**
     * Post entities to the Winmax4 API.
     *
     * This method validates the request data and sends the entity information to the Winmax4 API.
     * It expects specific input fields and ensures they conform to the validation rules defined within.
     *
     * ### Request Validation
     *
     * | Parameter    | Type     | Rules                                   | Description                                    |
     * |--------------|----------|-----------------------------------------|------------------------------------------------|
     * | `code`       | `string` | required, string, max:255               | Unique entity code.                            |
     * | `name`       | `string` | required, string, max:255               | Entity name.                                   |
     * | `entityType` | `int`    | required, integer, in:0,1,2,3,4         | Entity type: (0=Customer, 1=Supplier, etc.).   |
     * | `taxPayerID` | `string` | required, string, max:50                | Tax payer identification.                      |
     * | `address`    | `string` | required, string, max:255               | Address of the entity.                         |
     * | `zipCode`    | `string` | required, string, max:20                | Zip code of the entity.                        |
     * | `locality`   | `string` | required, string, max:255               | Locality of the entity.                        |
     * | `isActive`   | `boolean`| required, boolean                       | Indicates if the entity is active (true/false).|
     * | `phone`      | `string` | nullable, string, max:20                | Optional phone number.                         |
     * | `fax`        | `string` | nullable, string, max:20                | Optional fax number.                           |
     * | `mobilePhone`| `string` | nullable, string, max:20                | Optional mobile phone number.                  |
     * | `email`      | `string` | nullable, string, email, max:255        | Optional email address.                        |
     * | `country`    | `string` | required, string, size:2, in:PT         | Country code, must be 'PT'.                    |
     *
     * ### Entity Type Values
     *
     * | Type                  | Value |
     * |-----------------------|-------|
     * | Customer              | 0     |
     * | Supplier              | 1     |
     * | CustomerAndSupplier   | 2     |
     * | Other                 | 3     |
     * | All                   | 4     |
     *
     * @param Request $request The HTTP request instance containing all required data.
     * @return JsonResponse Returns a JSON response with the API result.
     * @throws GuzzleException If an error occurs during the API request.
     */
    public function postEntities(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'string|max:255',
            'entityType' => 'integer|in:0,1,2,3,4',
            'taxPayerID' => 'string|max:50',
            'address' => 'string|max:255|nullable',
            'zipCode' => 'string|max:20|nullable',
            'locality' => 'string|max:255|nullable',
            'isActive' => 'nullable|integer|in:0,1',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'mobilePhone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'country' => 'string|size:2|in:PT',
        ]);

        $response = $this->winmax4Service->postEntities(
            $request->name,
            $request->code,
            $request->entityType,
            $request->taxPayerID,
            $request->address,
            $request->zipCode,
            $request->locality,
            $request->isActive,
            $request->phone,
            $request->fax,
            $request->mobilePhone,
            $request->email,
            $request->country,
        );

        if(isset($response['error']) && $response['error'] && $response['status'] === 'ENTITYCODEINUSE') {
            $idWinmax4 = Winmax4Entity::where('code', $request->code)->value('id_winmax4');

            if($idWinmax4){
                if(Winmax4Entity::where('code', $request->code)->first()->is_active == 0){
                    $this->winmax4Service->putEntities($idWinmax4,
                        $request->name,
                        $request->code,
                        $request->entityType,
                        $request->taxPayerID,
                        $request->address,
                        $request->zipCode,
                        $request->locality,
                        1,
                        $request->phone,
                        $request->fax,
                        $request->mobilePhone,
                        $request->email,
                        $request->country,
                    );

                    return Winmax4Entity::where('code', $request->code)->first()->toArray();
                }
            }
        }

        return response()->json($response);
    }

    /**
     * Put entities to the Winmax4 API.
     *
     * This method validates the request data and sends the entity information to the Winmax4 API.
     * It expects specific input fields and ensures they conform to the validation rules defined within.
     *
     * ### Request Validation
     *
     * | Parameter    | Type     | Rules                                   | Description                                    |
     * |--------------|----------|-----------------------------------------|------------------------------------------------|
     * | `code`       | `string` | required, string, max:255               | Unique entity code.                            |
     * | `name`       | `string` | required, string, max:255               | Entity name.                                   |
     * | `entityType` | `int`    | required, integer, in:0,1,2,3,4         | Entity type: (0=Customer, 1=Supplier, etc.).   |
     * | `taxPayerID` | `string` | required, string, max:50                | Tax payer identification.                      |
     * | `address`    | `string` | required, string, max:255               | Address of the entity.                         |
     * | `zipCode`    | `string` | required, string, max:20                | Zip code of the entity.                        |
     * | `locality`   | `string` | required, string, max:255               | Locality of the entity.                        |
     * | `isActive`   | `boolean`| required, boolean                       | Indicates if the entity is active (true/false).|
     * | `phone`      | `string` | nullable, string, max:20                | Optional phone number.                         |
     * | `fax`        | `string` | nullable, string, max:20                | Optional fax number.                           |
     * | `mobilePhone`| `string` | nullable, string, max:20                | Optional mobile phone number.                  |
     * | `email`      | `string` | nullable, string, email, max:255        | Optional email address.                        |
     * | `country`    | `string` | required, string, size:2, in:PT         | Country code, must be 'PT'.                    |
     *
     * ### Entity Type Values
     *
     * | Type                  | Value |
     * |-----------------------|-------|
     * | Customer              | 0     |
     * | Supplier              | 1     |
     * | CustomerAndSupplier   | 2     |
     * | Other                 | 3     |
     * | All                   | 4     |
     *
     * @param Request $request The HTTP request instance containing all required data.
     * @return JsonResponse Returns a JSON response with the API result.
     * @throws GuzzleException If an error occurs during the API request.
     */
    public function putEntities(Request $request): JsonResponse
    {
        $request->validate([
            'id_winmax4' => 'required|integer|exists:winmax4_entities,id_winmax4',
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'entityType' => 'required|integer|in:0,1,2,3,4',
            'taxPayerID' => 'required|string|max:50',
            'address' => 'string|max:255|nullable',
            'zipCode' => 'string|max:20|nullable',
            'locality' => 'string|max:255|nullable',
            'isActive' => 'nullable|integer|in:0,1',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'mobilePhone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'expirationDate' => 'nullable|date_format:Y-m-d',
            'country' => 'nullable|string|size:2|in:PT',
        ]);

        return response()->json($this->winmax4Service->putEntities(
            $request->id_winmax4,
            $request->code,
            $request->name,
            $request->entityType,
            $request->taxPayerID,
            $request->address,
            $request->zipCode,
            $request->locality,
            $request->isActive,
            $request->phone,
            $request->fax,
            $request->mobilePhone,
            $request->email,
            $request->expirationDate,
            $request->country
        ), 200);
    }

    /**
     * Delete entities from the Winmax4 API.
     *
     * This method interfaces with the Winmax4Service to delete an entity specified by the given ID.
     * It returns the result of the deletion operation as a JSON response with an HTTP status code of 200.
     *
     * ### Parameters
     *
     * | Parameter | Type  | Description                     |
     * |-----------|-------|---------------------------------|
     * | `$id`     | `int` | The ID of the entity to delete. |
     *
     * ### Return
     *
     * | Type               | Description                                             |
     * |--------------------|---------------------------------------------------------|
     * | `JsonResponse`     | A JSON response containing the deletion result.         |
     * |                    | The structure of the response depends on the service implementation. |
     *
     * ### Exceptions
     *
     * | Exception                                  | Condition                                              |
     * |--------------------------------------------|--------------------------------------------------------|
     * | `Exception`                                | Throws when the deletion process encounters an error.  |
     *
     * @param int $id The ID of the entity to be deleted.
     * @return \Illuminate\Http\JsonResponse JSON response with the deletion result.
     * @throws GuzzleException
     */
    public function deleteEntities(int $id)
    {
        $localEntity = Winmax4Entity::where('id_winmax4', $id)->first();
        $response = $this->winmax4Service->deleteEntities($id);

        $responseDecoded = json_decode($response, true);
        if(isset($responseDecoded['error'])){
            // If the result is not OK, we will disable the entity
            $response = $this->winmax4Service->putEntities($id, $localEntity->code, $localEntity->name, $localEntity->entity_type, $localEntity->tax_payer_id, $localEntity->address, $localEntity->zip_code, $localEntity->location, 0, $localEntity->phone, $localEntity->fax, $localEntity->mobile_phone, $localEntity->email, $localEntity->country_code);
        }

        return response()->json($response, 200);
    }
}
