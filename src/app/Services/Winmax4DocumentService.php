<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Dflydev\DotAccessData\Data;
use GuzzleHttp\Exception\GuzzleException;

class Winmax4DocumentService extends Winmax4Service
{

    /**
     * Get documents from Winmax4 API
     *
     * This method sends a GET request to the specified URL endpoint to fetch a
     * list of documents. It uses the Guzzle HTTP client for making the request and
     * requires an authorization token to access the API.
     *
     * ### Headers
     *
     * | Header           | Value                               |
     * |------------------|-------------------------------------|
     * | Authorization    | Bearer {AccessToken}                |
     * | Content-Type     | application/json                    |
     *
     * The method fetches data from the endpoint `/Transactions/Documents` and expects a
     * JSON response which is then decoded into an object or array.
     *
     * ### Return
     *
     * | Type         | Description                                  |
     * |--------------|----------------------------------------------|
     * | `object`     | Returns an object containing document details. |
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
    public function getDocuments(): object|array|null
    {
        $response = $this->client->get($this->url . '/Transactions/Documents', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Post documents to Winmax4 API
     *
     * This method posts a document to the Winmax4 API using the provided document type, entity, and details.
     *
     * ### Parameters
     *
     * | Parameter       | Type                     | Description                                                   | Default |
     * |-----------------|--------------------------|---------------------------------------------------------------|---------|
     * | `$documentType`  | `string`                | Type of the document to be posted                             | N/A     |
     * | `$entity`        | `string`                | Entity associated with the document                           | N/A     |
     * | `$details`       | `array`                 | Array of document details                                     | N/A     |
     *
     * ### Return
     *
     * | Type             | Description                        |
     * |------------------|------------------------------------|
     * | `object|array|null` | Returns the API response decoded from JSON, or null on failure |
     *
     * ### Exceptions
     *
     * | Exception         | Condition                                    |
     * |-------------------|----------------------------------------------|
     * | `GuzzleException` | Throws when there is a HTTP client error     |
     *
     * ### Example Usage
     *
     * ```php
     * $documentType = new Winmax4DocumentType(/* params * /);
     * $entity = new Winmax4Entity(/* params * /);
     * $details = [/* details array * /];
     *
     * $response = $apiClient->postDocuments($documentType, $entity, $details);
     * ```
     *
     * @param string $documentType Type of the document
     * @param string $entity Entity associated with the document
     * @param array $details Array of document details
     * @return object|array|null Returns the API response decoded from JSON, or null on failure
     * @throws GuzzleException If there is a problem with the HTTP request
     */
    public function postDocuments(string $documentType, string $entity, array $details): object|array|null
    {
        $response = $this->client->post($this->url . '/Transactions/Documents', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'DocumentTypeCode' => $documentType,
                'Entity' => [
                    'Code' => $entity,
                ],
                'Details' => $details,
                'Format' => 'json',
            ],
        ]);

        $document = json_decode($response->getBody()->getContents());

        dd($document->Data);
    }
}