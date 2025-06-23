<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;

class Winmax4EntityService extends Winmax4Service
{
    /**
     * Get entities from Winmax4 API
     *
     * This method sends a GET request to the specified URL endpoint to fetch a
     * list of entities. It uses the Guzzle HTTP client for making the request and
     * requires an authorization token to access the API.
     *
     * ### Headers
     *
     * | Header           | Value                               |
     * |------------------|-------------------------------------|
     * | Authorization    | Bearer {AccessToken}                |
     * | Content-Type     | application/json                    |
     *
     * The method fetches data from the endpoint `/Files/Entities` and expects a
     * JSON response which is then decoded into an object or array.
     *
     * ### Return
     *
     * | Type         | Description                                  |
     * |--------------|----------------------------------------------|
     * | `object`     | Returns an object containing entity details. |
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
    public function getEntities($lastChangeDateAfter = null): object|array|null
    {
        $url ='Files/Entities';

        if($lastChangeDateAfter){
            $url .= "?LastChangeDateAfter=". $lastChangeDateAfter;
        }

        try {
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

        if (is_array($responseJSONDecoded) && $responseJSONDecoded['error'] === true) {
            return $responseJSONDecoded;
        }

        if(is_null($responseJSONDecoded)){
            return null;
        }

        if($responseJSONDecoded->Data->Filter->TotalPages > 1){
            for($i = 2; $i <= $responseJSONDecoded->Data->Filter->TotalPages; $i++){
                try{
                    $response = $this->client->get($url . '?PageNumber=' . $i, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                        ],
                    ]);
                } catch (ConnectException $e) {
                    // Handle timeouts, connection failures, DNS errors, etc.
                    return $this->handleConnectionError($e);
                }

                $responseJSONDecoded->Data->Entities = array_merge($responseJSONDecoded->Data->Entities, json_decode($response->getBody()->getContents())->Data->Entities);
            }
        }

        return $responseJSONDecoded;
    }

    /**
     * Post entities to Winmax4 API
     *
     * This method posts an entity such as customers or suppliers to the Winmax4 API.
     *
     * ### Entity Types
     *
     * | Entity Type            | Value |
     * |------------------------|-------|
     * | Customer               | 0     |
     * | Supplier               | 1     |
     * | CustomerAndSupplier    | 2     |
     * | Other                  | 3     |
     * | All                    | 4     |
     *
     * ### Parameters
     *
     * | Parameter     | Type    | Description                                            | Default |
     * |---------------|---------|--------------------------------------------------------|---------|
     * | `$name`       | `string`| Entity name                                            | N/A     |
     * | `$code`       | `string`| Entity code                                            | `null`  |
     * | `$entityType` | `int`   | Type of the entity as described in the table above     | `null`  |
     * | `$taxPayerID` | `string`| Entity tax payer ID                                    | `null`  |
     * | `$address`    | `string`| Entity address                                         | `null`  |
     * | `$zipCode`    | `string`| Entity zip code                                        | `null`  |
     * | `$locality`   | `string`| Entity locality                                        | `null`  |
     * | `$isActive`   | `int`   | Set entity as active or not                            | `1`     |
     * | `$phone`      | `null`  | Entity phone                                           | `null`  |
     * | `$fax`        | `null`  | Entity fax                                             | `null`  |
     * | `$mobilePhone`| `null`  | Entity mobile phone                                    | `null`  |
     * | `$email`      | `null`  | Entity email                                           | `null`  |
     * | `$country`    | `string`| Entity country                                         | `'PT'`  |
     *
     * ### Return
     *
     * | Type    | Description                       |
     * |---------|-----------------------------------|
     * | `object`| Returns an object of the API response. |
     *
     * ### Exceptions
     *
     * | Exception         | Condition                               |
     * |-------------------|-----------------------------------------|
     * | `GuzzleException` | Throws when there is a HTTP client error|
     *
     * @param string $name Entity name
     * @param string|null $code Entity code
     * @param int|null $entityType Entity type (See table above for possible values)
     * @param string|null $taxPayerID Entity tax payer ID
     * @param string $address Entity address
     * @param string|null $zipCode Entity zip code
     * @param string|null $locality Entity locality
     * @param int|null $isActive Set entity as active or not, default is 1
     * @param string|null $phone Entity phone, default is null
     * @param string|null $fax Entity fax, default is null
     * @param string|null $mobilePhone Entity mobile phone, default is null
     * @param string|null $email Entity email, default is null
     * @param string|null $country Entity country, default is 'PT'
     * @return array Returns the entity object
     * @throws GuzzleException If there is a problem with the HTTP request
     */
    public function postEntities(string $name, string $code = null, int $entityType = null, string $taxPayerID = null, string $address = null, string $zipCode = null, string $locality = null, ?int $isActive = 1, string $phone = null, string $fax = null, string $mobilePhone = null, string $email = null, ?string $country = 'PT')
    {
            //Check if taxPayerID do not start with 5 or 6
            $gdpr = [];
            if($taxPayerID && !in_array(substr($taxPayerID, 0, 1), ['5', '6'])){
                $gdpr = [
                    'GDPRAllowAccessToPersonalInformation' => true,
                    'GDPRPersonalInformationAccessExpirationDate' => '2099-12-31',
                ];
            }

            try{
                $response = $this->client->post('Files/Entities', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                    ],
                    'json' => [
                        'Code' => $code,
                        'Name' => $name,
                        'IsActive' => $isActive,
                        'EntityType' => $entityType,
                        'TaxPayerID' => $taxPayerID,
                        'Address' => $address,
                        'ZipCode' => $zipCode,
                        'Phone' => $phone,
                        'Fax' => $fax,
                        'MobilePhone' => $mobilePhone,
                        'Email' => $email,
                        'Location' => $locality,
                        'Country' => $country,
                        ...$gdpr,
                    ],
                ]);
            }catch (ConnectException $e) {
                // Handle timeouts, connection failures, DNS errors, etc.
                return $this->handleConnectionError($e);
            }


            if(config('winmax4.use_soft_deletes')) {
                $builder = Winmax4Entity::withTrashed();
            } else {
                $builder = new Winmax4Entity();
            }

            $responseDecoded = json_decode($response->getBody()->getContents());

            if ($responseDecoded && $responseDecoded->error === true) {
                return json_decode(json_encode($responseDecoded), true);
            }

            $builder->updateOrCreate(
                [
                    'id_winmax4' => $responseDecoded->Data->Entity->ID,
                ],
                [
                    'id_winmax4' => $responseDecoded->Data->Entity->ID,
                    'name' => $responseDecoded->Data->Entity->Name,
                    'address' => $responseDecoded->Data->Entity->Address,
                    'code' => $responseDecoded->Data->Entity->Code,
                    'country_code' => $responseDecoded->Data->Entity->CountryCode,
                    'email' => $responseDecoded->Data->Entity->Email,
                    'entity_type' => $responseDecoded->Data->Entity->EntityType,
                    'fax' => $responseDecoded->Data->Entity->Fax,
                    'is_active' => $responseDecoded->Data->Entity->IsActive,
                    'location' => $responseDecoded->Data->Entity->Location,
                    'mobile_phone' => $responseDecoded->Data->Entity->MobilePhone,
                    'phone' => $responseDecoded->Data->Entity->Phone,
                    'tax_payer_id' => $responseDecoded->Data->Entity->TaxPayerID,
                    'zip_code' => $responseDecoded->Data->Entity->ZipCode,
                ]
            );

            return $builder->where('id_winmax4', $responseDecoded->Data->Entity->ID)->first()->toArray();
    }

    /**
     * Put entities to Winmax4 API
     *
     * This method updates an entity such as customers or suppliers to the Winmax4 API.
     *
     * ### Entity Types
     *
     * | Entity Type            | Value |
     * |------------------------|-------|
     * | Customer               | 0     |
     * | Supplier               | 1     |
     * | CustomerAndSupplier    | 2     |
     * | Other                  | 3     |
     * | All                    | 4     |
     *
     * ### Parameters
     *
     * | Parameter     | Type    | Description                                            | Default |
     * |---------------|---------|--------------------------------------------------------|---------|
     * | `$idWinmax4`  | `int`   | Entity ID in Winmax4                                   | N/A     |
     * | `$name`       | `string`| Entity name                                            | N/A     |
     * | `$code`       | `string`| Entity code                                            | `null`  |
     * | `$entityType` | `int`   | Type of the entity as described in the table above     | `null`  |
     * | `$taxPayerID` | `string`| Entity tax payer ID                                    | `null`  |
     * | `$address`    | `string`| Entity address                                         | `null`  |
     * | `$zipCode`    | `string`| Entity zip code                                        | `null`  |
     * | `$locality`   | `string`| Entity locality                                        | `null`  |
     * | `$isActive`   | `int`   | Set entity as active or not                            | `1`     |
     * | `$phone`      | `null`  | Entity phone                                           | `null`  |
     * | `$fax`        | `null`  | Entity fax                                             | `null`  |
     * | `$mobilePhone`| `null`  | Entity mobile phone                                    | `null`  |
     * | `$email`      | `null`  | Entity email                                           | `null`  |
     * | `$country`    | `string`| Entity country                                         | `'PT'`  |
     *
     * ### Return
     *
     * | Type    | Description                       |
     * |---------|-----------------------------------|
     * | `object`| Returns an object of the API response. |
     *
     * ### Exceptions
     *
     * | Exception         | Condition                               |
     * |-------------------|-----------------------------------------|
     * | `GuzzleException` | Throws when there is a HTTP client error|
     *
     * @param int $idWinmax4 Entity ID in Winmax4
     * @param string $code Entity code
     * @param string $name Entity name
     * @param int $entityType Entity type (See table above for possible values)
     * @param string $taxPayerID Entity tax payer ID
     * @param string $address Entity address
     * @param string $zipCode Entity zip code
     * @param string $locality Entity locality
     * @param int|null $isActive Set entity as active or not, default is 1
     * @param string|null $phone Entity phone, default is null
     * @param string|null $fax Entity fax, default is null
     * @param string|null $mobilePhone Entity mobile phone, default is null
     * @param string|null $email Entity email, default is null
     * @param string|null $expirationDate Entity expiration date, default is null
     * @param string|null $country Entity country, default is 'PT'
     * @return array Returns the entity object
     * @throws GuzzleException If there is a problem with the HTTP request
     */
    public function putEntities(int $idWinmax4, string $code, string $name, int $entityType, string $taxPayerID, string $address = null, string $zipCode = null, string $locality = null, ?int $isActive = 1, string $phone = null, string $fax = null, string $mobilePhone = null, string $email = null, string $expirationDate = null, ?string $country = 'PT'): array
    {
        //Check if taxPayerID do not start with 5 or 6
        $gdpr = [];
        if($taxPayerID && !in_array(substr($taxPayerID, 0, 1), ['5', '6'])){
            $gdpr = [
                'GDPRAllowAccessToPersonalInformation' => true,
                'GDPRPersonalInformationAccessExpirationDate' => $expirationDate ?: '2099-12-31',
            ];
        }

        try{
            $response = $this->client->put('Files/Entities/?id='.$idWinmax4, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
                'json' => [
                    'Code' => $code,
                    'Name' => $name,
                    'IsActive' => $isActive,
                    'EntityType' => $entityType,
                    'TaxPayerID' => $taxPayerID,
                    'Address' => $address,
                    'ZipCode' => $zipCode,
                    'Phone' => $phone,
                    'Fax' => $fax,
                    'MobilePhone' => $mobilePhone,
                    'Email' => $email,
                    'Location' => $locality,
                    'Country' => $country,
                    ...$gdpr,
                ],
            ]);
        }catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }


        $entity = json_decode($response->getBody()->getContents());

        if (is_array($entity) && $entity['error'] === true) {
            return $entity;
        }

        Winmax4Entity::where('id_winmax4', $idWinmax4)->update([
            'name' => $entity->Data->Entity->Name,
            'address' => $entity->Data->Entity->Address,
            'country_code' => $entity->Data->Entity->CountryCode,
            'email' => $entity->Data->Entity->Email,
            'entity_type' => $entity->Data->Entity->EntityType,
            'fax' => $entity->Data->Entity->Fax,
            'is_active' => $entity->Data->Entity->IsActive,
            'location' => $entity->Data->Entity->Location,
            'mobile_phone' => $entity->Data->Entity->MobilePhone,
            'phone' => $entity->Data->Entity->Phone,
            'tax_payer_id' => $entity->Data->Entity->TaxPayerID,
            'zip_code' => $entity->Data->Entity->ZipCode,
        ]);

        return Winmax4Entity::where('id_winmax4', $idWinmax4)->first()->toArray();
    }

    /**
     * Delete entities from Winmax4 API
     *
     * This method attempts to delete an entity from the Winmax4 system using its API.
     * It sends a DELETE request to the API, which returns a response indicating the
     * success or failure of the operation. Depending on the response, the entity is
     * either disabled locally in the database or deleted.
     *
     * ### API Response Handling
     *
     * The API responds with a JSON object containing a `Results` array. The method
     * checks the first result's `Code` to determine the success of the deletion.
     *
     * | Response Code           | Description                             |
     * |-------------------------|-----------------------------------------|
     * | `WINMAX4_RESPONSE_OK`   | Entity deleted successfully on API side |
     * | `other`                 | API deletion failed; entity is disabled locally |
     *
     * ### Soft Deletes
     *
     * The method supports soft deletes based on the application's configuration.
     * When soft deletes are enabled (`winmax4.use_soft_deletes`), the entity is
     * marked as inactive in the local database without removing it completely.
     * Otherwise, a hard delete (force delete) is performed.
     *
     * ### Parameters
     *
     * | Parameter      | Type    | Description                           |
     * |----------------|---------|---------------------------------------|
     * | `$idWinmax4`   | `int`   | The ID of the entity to be deleted.   |
     *
     * ### Return
     *
     * | Type             | Description                                                           |
     * |------------------|-----------------------------------------------------------------------|
     * | `JsonResponse`   | Returns a JSON response if the entity is disabled locally.            |
     * | `Winmax4Entity`  | Returns the entity object if deleted successfully.                    |
     *
     * ### Exceptions
     *
     * | Exception                                  | Condition                                         |
     * |--------------------------------------------|---------------------------------------------------|
     * | `GuzzleHttp\Exception\GuzzleException`     | Throws when there is an HTTP client error during the DELETE request. |
     *
     * @param int $idWinmax4 The ID of the Winmax4 entity to delete.
     * @return array | JsonResponse JSON response or deleted entity object.
     * @throws GuzzleException
     */
    public function deleteEntities(int $idWinmax4): array
    {
        $localEntity = Winmax4Entity::where('id_winmax4', $idWinmax4)->first();

        try{
            $response = $this->client->delete('Files/Entities/?id='.$idWinmax4, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        $localEntity->is_active = 0;
        $localEntity->deleted_at = now();
        $localEntity->save();

        return response()->json([
            'message' => 'Entity deleted successfully',
        ]);
    }
}