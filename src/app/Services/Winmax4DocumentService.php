<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Document;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
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
    public function postDocuments(Winmax4Setting $documentType, Winmax4Entity $entity, array $details): object|array|null
    {
        dd($documentType);
        $response = $this->client->post($this->url . '/Transactions/Documents', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'DocumentTypeCode' => $documentType['invoice_receipt']->code,
                'Entity' => [
                    'Code' => $entity->code,
                    'TaxPayerID' => $entity->tax_payer_id,
                ],
                'Details' => $details,
                'Format' => 'json',
            ],
        ]);

        $document = json_decode($response->getBody()->getContents());

        $document = new Winmax4Document();
        $document->document_type_id = $document->Data->DocumentTypeID;
        $document->document_number = $document->Data->DocumentNumber;
        $document->serie = $document->Data->Serie;
        $document->number = $document->Data->Number;
        $document->date = $document->Data->Date;
        $document->external_identification = $document->Data->ExternalIdentification;
        $document->currency_code = $document->Data->CurrencyCode;
        $document->is_deleted = $document->Data->IsDeleted;
        $document->user_login = $document->Data->UserLogin;
        $document->terminal_code = $document->Data->TerminalCode;
        $document->source_warehouse_code = $document->Data->SourceWarehouseCode;
        $document->target_warehouse_code = $document->Data->TargetWarehouseCode;
        $document->entity_id = $document->Data->EntityID;
        $document->total_without_taxes = $document->Data->TotalWithoutTaxes;
        $document->total_applied_taxes = $document->Data->TotalAppliedTaxes;
        $document->total_with_taxes = $document->Data->TotalWithTaxes;
        $document->total_liquidated = $document->Data->TotalLiquidated;
        $document->load_address = $document->Data->LoadAddress;
        $document->load_location = $document->Data->LoadLocation;
        $document->load_zip_code = $document->Data->LoadZipCode;
        $document->load_date_time = $document->Data->LoadDateTime;
        $document->load_vehicle_license_plate = $document->Data->LoadVehicleLicensePlate;
        $document->load_country_code = $document->Data->LoadCountryCode;
        $document->unload_address = $document->Data->UnloadAddress;
        $document->unload_location = $document->Data->UnloadLocation;
        $document->unload_zip_code = $document->Data->UnloadZipCode;
        $document->unload_date_time = $document->Data->UnloadDateTime;
        $document->unload_country_code = $document->Data->UnloadCountryCode;
        $document->hash_characters = $document->Data->HashCharacters;
        $document->ta_doc_code_id = $document->Data->TADocCodeID;
        $document->atcudd = $document->Data->ATCUD;
        $document->table_number = $document->Data->TableNumber;
        $document->table_split_number = $document->Data->TableSplitNumber;
        $document->sales_person_code = $document->Data->SalesPersonCode;
        $document->remarks = $document->Data->Remarks;
        $document->save();



        dd($document->Data);
    }
}