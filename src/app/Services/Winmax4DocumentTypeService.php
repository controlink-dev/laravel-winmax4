<?php

namespace Controlink\LaravelWinmax4\app\Services;

use GuzzleHttp\Exception\GuzzleException;

class Winmax4DocumentTypeService extends Winmax4Service
{
    /**
     * Get document types from Winmax4 API
     *
     * This method sends a GET request to the specified URL endpoint to fetch a
     * list of document types. It uses the Guzzle HTTP client for making the request and
     * requires an authorization token to access the API.
     *
     * ### Headers
     *
     * | Header           | Value                               |
     * |------------------|-------------------------------------|
     * | Authorization    | Bearer {AccessToken}                |
     * | Content-Type     | application/json                    |
     *
     * The method fetches data from the endpoint `/Files/DocumentTypes` and expects a
     * JSON response which is then decoded into an object or array.
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
    public function getDocumentTypes(): object|array|null
    {
        $url = $this->url . '/Files/DocumentTypes';

        $response = $this->client->get($url, [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        $responseJSONDecoded = json_decode($response->getBody()->getContents());

        if(is_null($responseJSONDecoded)){
            return null;
        }

        if($responseJSONDecoded->Data->Filter->TotalPages > 1){
            for($i = 2; $i <= $responseJSONDecoded->Data->Filter->TotalPages; $i++){
                $response = $this->client->get($url . '&PageNumber=' . $i, [
                    'verify' => $this->settings['verify_ssl_guzzle'],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                        'Content-Type' => 'application/json',
                    ],
                ]);

                $responseJSONDecoded->Data->DocumentTypes = array_merge($responseJSONDecoded->Data->DocumentTypes, json_decode($response->getBody()->getContents())->Data->DocumentTypes);
            }
        }

        return $responseJSONDecoded;
    }
}