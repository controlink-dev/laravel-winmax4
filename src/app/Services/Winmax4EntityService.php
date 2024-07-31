<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use GuzzleHttp\Exception\GuzzleException;

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
    public function getEntities(): object|array|null
    {
        $response = $this->client->get($this->url . '/Files/Entities', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
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
     * | `$code`       | `string`| Entity code                                            | N/A     |
     * | `$name`       | `string`| Entity name                                            | N/A     |
     * | `$entityType` | `int`   | Type of the entity as described in the table above     | N/A     |
     * | `$taxPayerID` | `string`| Entity tax payer ID                                    | N/A     |
     * | `$address`    | `string`| Entity address                                         | N/A     |
     * | `$zipCode`    | `string`| Entity zip code                                        | N/A     |
     * | `$locality`   | `string`| Entity locality                                        | N/A     |
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
     * @param string $code Entity code
     * @param string $name Entity name
     * @param int $entityType Entity type (See table above for possible values)
     * @param string $taxPayerID Entity tax payer ID
     * @param string $address Entity address
     * @param string $zipCode Entity zip code
     * @param string $locality Entity locality
     * @param int $isActive Set entity as active or not, default is 1
     * @param string|null $phone Entity phone, default is null
     * @param string|null $fax Entity fax, default is null
     * @param string|null $mobilePhone Entity mobile phone, default is null
     * @param string|null $email Entity email, default is null
     * @param string $country Entity country, default is 'PT'
     * @return Winmax4Entity Returns the entity object
     * @throws GuzzleException If there is a problem with the HTTP request
     */
    public function postEntities(string $code, string $name, int $entityType, string $taxPayerID, string $address, string $zipCode, string $locality, ?int $isActive = 1, string $phone = null, string $fax = null, string $mobilePhone = null, string $email = null, ?string $country = 'PT'): Winmax4Entity
    {
        $response = $this->client->post($this->url . '/Files/Entities', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
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
            ],
        ]);

        $entity = json_decode($response->getBody()->getContents());

        return Winmax4Entity::updateOrCreate(
            [
                'code' => $entity->Data->Entity->Code,
            ],
            [
                'id_winmax4' => $entity->Data->Entity->ID,
                'name' => $entity->Data->Entity->Name,
                'address' => $entity->Data->Entity->Address,
                'code' => $entity->Data->Entity->Code,
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
            ]
        );
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
     * | `$code`       | `string`| Entity code                                            | N/A     |
     * | `$name`       | `string`| Entity name                                            | N/A     |
     * | `$entityType` | `int`   | Type of the entity as described in the table above     | N/A     |
     * | `$taxPayerID` | `string`| Entity tax payer ID                                    | N/A     |
     * | `$address`    | `string`| Entity address                                         | N/A     |
     * | `$zipCode`    | `string`| Entity zip code                                        | N/A     |
     * | `$locality`   | `string`| Entity locality                                        | N/A     |
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
     * @param int $isActive Set entity as active or not, default is 1
     * @param string|null $phone Entity phone, default is null
     * @param string|null $fax Entity fax, default is null
     * @param string|null $mobilePhone Entity mobile phone, default is null
     * @param string|null $email Entity email, default is null
     * @param string $country Entity country, default is 'PT'
     * @return Winmax4Entity Returns the entity object
     * @throws GuzzleException If there is a problem with the HTTP request
     */
    public function putEntities(int $idWinmax4, string $code, string $name, int $entityType, string $taxPayerID, string $address, string $zipCode, string $locality, ?int $isActive = 1, string $phone = null, string $fax = null, string $mobilePhone = null, string $email = null, ?string $country = 'PT'): Winmax4Entity
    {
        $response = $this->client->put($this->url . '/Files/Entities/?id='.$idWinmax4, [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
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
            ],
        ]);

        $entity = json_decode($response->getBody()->getContents());

        Winmax4Entity::where('code', $code)->update([
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

        return Winmax4Entity::where('code', $code)->first();
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
     * @return \Illuminate\Http\JsonResponse|Winmax4Entity JSON response or deleted entity object.
     * @throws GuzzleException
     */
    public function deleteEntities(int $idWinmax4): Winmax4Entity|\Illuminate\Http\JsonResponse
    {
        $response = $this->client->delete($this->url . '/Files/Entities/?id='.$idWinmax4, [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        $entity = json_decode($response->getBody()->getContents());

        if($entity->Results[0]->Code !== self::WINMAX4_RESPONSE_OK){

            // If the result is not OK, we will disable the entity
            Winmax4Entity::where('id_winmax4', $idWinmax4)->update([
                'is_active' => 0,
            ]);

            return response()->json(['message' => 'Entity disabled successfully!'], 200);
        }

        // If the result is OK, we will delete the entity or force delete it
        if(config('winmax4.use_soft_deletes')){
            Winmax4Entity::where('id_winmax4', $idWinmax4)->update([
                'is_active' => 0,
            ]);

            $entityToDelete = Winmax4Entity::where('id_winmax4', $idWinmax4)->first();
            $entityToDelete->delete();

            return $entityToDelete;

        }else{

            return Winmax4Entity::where('id_winmax4', $idWinmax4)->forceDelete();
        }
    }
}