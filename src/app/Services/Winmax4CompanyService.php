<?php

namespace Controlink\LaravelWinmax4\app\Services;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;

class Winmax4CompanyService extends Winmax4Service
{

    /**
     * Get companies from Winmax4 API
     *
     * This method sends a GET request to the specified URL endpoint to fetch a
     * list of currencies. It uses the Guzzle HTTP client for making the request and
     * requires an authorization token to access the API.
     *
     * ### Headers
     *
     * | Header           | Value                               |
     * |------------------|-------------------------------------|
     * | Authorization    | Bearer {AccessToken}                |
     * | Content-Type     | application/json                    |
     *
     * The method fetches data from the endpoint `/Files/Currencies` and expects a
     * JSON response which is then decoded into an object or array.
     *
     * ### Return
     *
     * | Type         | Description                                  |
     * |--------------|----------------------------------------------|
     * | `object`     | Returns an object containing currency details. |
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
    public function getCompanies(): object|array|null
    {
        try{
            $response = $this->client->get('Settings/Company', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                    'Content-Type' => 'application/json',
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        $responseDecoded = json_decode($response->getBody()->getContents());

        if (is_array($responseDecoded) && $responseDecoded['error'] === true) {
            return $responseDecoded;
        }

        return $responseDecoded;
    }
}