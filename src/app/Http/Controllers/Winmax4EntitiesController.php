<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4EntityService;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Winmax4EntitiesController extends Controller
{
    protected $winmax4Service;

    /**
     * Winmax4Controller constructor.
     *
     */
    public function __construct()
    {
        $winmaxSettings = Winmax4Setting::where(config('winmax4.license_column'), session('licenseID'))->first();

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
    public function postEntities(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'entityType' => 'required|integer|in:0,1,2,3,4',
            'taxPayerID' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'zipCode' => 'required|string|max:20',
            'locality' => 'required|string|max:255',
            'isActive' => 'required|boolean',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'mobilePhone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'country' => 'required|string|size:2|in:PT',
        ]);

        return response()->json($this->winmax4Service->postEntities(
            $request->code,
            $request->name,
            $request->entityType,
            $request->taxPayerID,
            $request->address,
            $request->zipCode,
            $request->locality,
            $request->phone,
            $request->fax,
            $request->mobilePhone,
            $request->email,
        ), 200);
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
            'address' => 'required|string|max:255',
            'zipCode' => 'required|string|max:20',
            'locality' => 'required|string|max:255',
            'isActive' => 'nullable|boolean',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'mobilePhone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
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
    public function deleteEntities(int $id): JsonResponse
    {
        return response()->json($this->winmax4Service->deleteEntities($id), 200);
    }
}
