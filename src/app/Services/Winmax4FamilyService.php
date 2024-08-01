<?php

namespace Controlink\LaravelWinmax4\app\Services;

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
        $response = $this->client->get($this->url . '/Files/Families?IncludeSubFamilies=true', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }
}