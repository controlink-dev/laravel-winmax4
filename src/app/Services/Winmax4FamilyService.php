<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubFamily;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubSubFamily;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;

class Winmax4FamilyService extends Winmax4Service
{
    /**
     * Get families from Winmax4 API
     *
     * This method sends a GET request to the specified URL endpoint to fetch a
     * list of families. It uses the Guzzle HTTP client for making the request and
     * requires an authorization token to access the API.
     *
     * ### Headers
     *
     * | Header           | Value                               |
     * |------------------|-------------------------------------|
     * | Authorization    | Bearer {AccessToken}                |
     * | Content-Type     | application/json                    |
     *
     * The method fetches data from the endpoint `/Files/Families` and expects a
     * JSON response which is then decoded into an object or array.
     *
     * The endpoint also includes subfamilies in the response, which can be included or excluded.
     *
     * ### Return
     *
     * | Type         | Description                                  |
     * |--------------|----------------------------------------------|
     * | `object`     | Returns an object containing document type details. |
     * | `array`      | Returns an array if JSON decoding returns it.|
     * | `null`       | Returns null if the response is empty or invalid. |
     *
     * ### Exceptions
     *
     * | Exception                              | Condition                                         |
     * |----------------------------------------|---------------------------------------------------|
     * | `GuzzleHttp\Exception\GuzzleException` | Thrown when the HTTP request fails for any reason.|
     *
     * @return object|array|null Returns the decoded JSON response.
     * @throws GuzzleException
     */
    public function getFamilies(): object|array|null
    {
        $url = 'Files/Families?IncludeSubFamilies=true';

        try{
            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }


        $responseJSONDecoded = json_decode($response->getBody()->getContents());

        if (is_object($responseJSONDecoded) && isset($responseJSONDecoded->error) && $responseJSONDecoded->error === true) {
            return $responseJSONDecoded;
        }

        if(is_null($responseJSONDecoded)){
            return null;
        }

        if($responseJSONDecoded->Data->Filter->TotalPages > 1){
            for($i = 2; $i <= $responseJSONDecoded->Data->Filter->TotalPages; $i++){
                try{
                    $response = $this->client->get($url . '&PageNumber=' . $i, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                        ],
                    ]);
                } catch (ConnectException $e) {
                    // Handle timeouts, connection failures, DNS errors, etc.
                    return $this->handleConnectionError($e);
                }


                $responseJSONDecoded->Data->Families = array_merge($responseJSONDecoded->Data->Families, json_decode($response->getBody()->getContents())->Data->Families);
            }
        }

        return $responseJSONDecoded;
    }

    /**
     * Create a family in the Winmax4 API
     *
     * This method sends a POST request to the `/Files/Families` endpoint to create
     * a new family. On success, the family (and its subfamilies and sub-subfamilies)
     * is stored in the local database.
     *
     * ### Payload
     *
     * | Parameter     | Type      | Description                                                    |
     * |---------------|-----------|----------------------------------------------------------------|
     * | `Code`        | `int`     | Family code (optional, Winmax4 generates one when omitted).    |
     * | `Designation` | `string`  | Family designation.                                            |
     * | `IsActive`    | `bool`    | Indicates if the family is active.                             |
     * | `SubFamilies` | `array`   | Subfamilies (`Code`, `Designation`, `SubSubFamilies`).         |
     *
     * ### Return
     *
     * | Type         | Description                                           |
     * |--------------|-------------------------------------------------------|
     * | `array`      | The local family with subfamilies, or the API error.  |
     *
     * @param string $designation Family designation.
     * @param int|null $code Family code (optional).
     * @param bool|null $isActive Indicates if the family is active.
     * @param array $subFamilies Subfamilies to create with the family.
     * @return array The local family or the API error payload.
     * @throws GuzzleException
     */
    public function postFamilies(string $designation, ?int $code = null, ?bool $isActive = true, array $subFamilies = []): array
    {
        $json = [
            'Designation' => $designation,
            'IsActive' => $isActive ?? true,
        ];

        if (!is_null($code)) {
            $json['Code'] = $code;
        }

        if (!empty($subFamilies)) {
            $json['SubFamilies'] = $subFamilies;
        }

        try {
            $response = $this->client->post('Files/Families', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
                'json' => $json,
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        $responseDecoded = json_decode($response->getBody()->getContents());

        if (isset($responseDecoded->error) && $responseDecoded->error === true) {
            return (array) $responseDecoded;
        }

        if (!isset($responseDecoded->Data->Family)) {
            return json_decode(json_encode($responseDecoded), true) ?? [];
        }

        return $this->saveLocalFamily($responseDecoded->Data->Family);
    }

    /**
     * Update a family in the Winmax4 API
     *
     * This method sends a PUT request to the `/Files/Families` endpoint to update
     * an existing family, identified by its code. On success, the local family
     * (and its subfamilies and sub-subfamilies) is updated.
     *
     * ### Payload
     *
     * | Parameter     | Type      | Description                                                    |
     * |---------------|-----------|----------------------------------------------------------------|
     * | `Code`        | `int`     | Family code to update.                                         |
     * | `Designation` | `string`  | Family designation (optional).                                 |
     * | `IsActive`    | `bool`    | Indicates if the family is active (optional).                  |
     * | `SubFamilies` | `array`   | Subfamilies to create/update (optional).                       |
     *
     * ### Return
     *
     * | Type         | Description                                           |
     * |--------------|-------------------------------------------------------|
     * | `array`      | The local family with subfamilies, or the API error.  |
     *
     * @param int $code Family code to update.
     * @param string|null $designation Family designation.
     * @param bool|null $isActive Indicates if the family is active.
     * @param array|null $subFamilies Subfamilies to create/update.
     * @return array The local family or the API error payload.
     * @throws GuzzleException
     */
    public function putFamilies(int $code, ?string $designation = null, ?bool $isActive = null, ?array $subFamilies = null): array
    {
        $json = [
            'Code' => $code,
        ];

        if (!is_null($designation)) {
            $json['Designation'] = $designation;
        }

        if (!is_null($isActive)) {
            $json['IsActive'] = $isActive;
        }

        if (!empty($subFamilies)) {
            $json['SubFamilies'] = $subFamilies;
        }

        try {
            $response = $this->client->put('Files/Families', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
                'json' => $json,
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        $responseDecoded = json_decode($response->getBody()->getContents());

        if (isset($responseDecoded->error) && $responseDecoded->error === true) {
            return (array) $responseDecoded;
        }

        if (!isset($responseDecoded->Data->Family)) {
            return json_decode(json_encode($responseDecoded), true) ?? [];
        }

        return $this->saveLocalFamily($responseDecoded->Data->Family);
    }

    /**
     * Delete a family from the Winmax4 API
     *
     * This method sends a DELETE request to the `/Files/Families` endpoint with the
     * family code in the body. Depending on the response, the family is either
     * removed from the local database or disabled.
     *
     * ### API Response Handling
     *
     * | Response Code           | Description                                                     |
     * |-------------------------|-----------------------------------------------------------------|
     * | `WINMAX4_RESPONSE_OK`   | Family deleted on API side; removed locally (or disabled if in use by local articles). |
     * | `other`                 | API deletion failed (e.g. family in use); family is disabled on API and locally. |
     *
     * @param int $code The code of the family to delete.
     * @return array The API response or error payload.
     * @throws GuzzleException
     */
    public function deleteFamilies(int $code): array
    {
        $localFamily = Winmax4Family::where('code', $code)->first();

        try {
            $response = $this->client->delete('Files/Families', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
                'json' => [
                    'Code' => $code,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        $family = json_decode($response->getBody()->getContents(), true);

        if (is_array($family) && isset($family['error']) && $family['error'] === true) {
            return $family;
        }

        if (($family['Results'][0]['Code'] ?? null) !== self::WINMAX4_RESPONSE_OK) {
            // The family could not be deleted (e.g. it is in use), so it is disabled instead
            $updatedFamily = $this->putFamilies($code, null, false);

            if (isset($updatedFamily['error'])) {
                return $updatedFamily;
            }

            return $family;
        }

        if ($localFamily) {
            if (Winmax4Article::withoutGlobalScopes()->where('family_id', $localFamily->id)->exists()) {
                $localFamily->is_active = 0;
                $localFamily->save();
            } else {
                $localFamily->delete();
            }
        }

        return $family;
    }

    /**
     * Create or update the local family, subfamilies and sub-subfamilies from the Winmax4 API data.
     *
     * @param object $familyData The `Data->Family` object returned by the Winmax4 API.
     * @return array The local family with subfamilies and sub-subfamilies.
     */
    private function saveLocalFamily(object $familyData): array
    {
        $family = Winmax4Family::updateOrCreate(
            [
                'code' => $familyData->Code,
            ],
            [
                'designation' => $familyData->Designation,
                'is_active' => $familyData->IsActive ?? true,
            ]
        );

        if (isset($familyData->SubFamilies) && is_array($familyData->SubFamilies)) {
            foreach ($familyData->SubFamilies as $subFamilyData) {
                $subFamily = Winmax4SubFamily::updateOrCreate(
                    [
                        'family_id' => $family->id,
                        'code' => $subFamilyData->Code,
                    ],
                    [
                        'designation' => $subFamilyData->Designation,
                    ]
                );

                if (isset($subFamilyData->SubSubFamilies) && is_array($subFamilyData->SubSubFamilies)) {
                    foreach ($subFamilyData->SubSubFamilies as $subSubFamilyData) {
                        Winmax4SubSubFamily::updateOrCreate(
                            [
                                'sub_family_id' => $subFamily->id,
                                'code' => $subSubFamilyData->Code,
                            ],
                            [
                                'designation' => $subSubFamilyData->Designation,
                            ]
                        );
                    }
                }
            }
        }

        return $family->load('subFamilies.subSubFamilies')->toArray();
    }
}
