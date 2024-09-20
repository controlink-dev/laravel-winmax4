<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Document;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentDetail;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentTax;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
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
     * @param array $documentType Type of the document
     * @param string $entity Entity associated with the document
     * @param array $details Array of document details
     * @return object|array|null Returns the API response decoded from JSON, or null on failure
     * @throws GuzzleException If there is a problem with the HTTP request
     */
    public function postDocuments(Winmax4DocumentType $documentType, Winmax4Entity $entity, array $details): object|array|null
    {
        $response = $this->client->post($this->url . '/Transactions/Documents', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'DocumentTypeCode' => $documentType->code,
                'Entity' => [
                    'Code' => $entity->code,
                    'TaxPayerID' => $entity->tax_payer_id,
                ],
                'Details' => $details,
                'Format' => 'json',
            ],
        ]);

        $documentResponse = json_decode($response->getBody()->getContents());

        $document = new Winmax4Document();
        $document->document_type_id = $documentType->id;
        $document->document_number = $documentResponse->Data->DocumentNumber;
        $document->serie = $documentResponse->Data->Serie;
        $document->number = $documentResponse->Data->Number;
        $document->date = $documentResponse->Data->Date;
        $document->external_identification = $documentResponse->Data->ExternalIdentification ?? null;
        $document->currency_code = $documentResponse->Data->CurrencyCode;
        $document->is_deleted = $documentResponse->Data->IsDeleted;
        $document->user_login = $documentResponse->Data->UserLogin;
        $document->terminal_code = $documentResponse->Data->TerminalCode;
        $document->source_warehouse_code = $documentResponse->Data->SourceWarehouseCode;
        $document->target_warehouse_code = $documentResponse->Data->TargetWarehouseCode ?? null;
        $document->entity_id = Winmax4Entity::where('code', $documentResponse->Data->Entity->Code)->first();
        $document->total_without_taxes = $documentResponse->Data->TotalWithoutTaxes;
        $document->total_applied_taxes = $documentResponse->Data->TotalAppliedTaxes;
        $document->total_with_taxes = $documentResponse->Data->TotalWithTaxes;
        $document->total_liquidated = $documentResponse->Data->TotalLiquidated;
        $document->load_address = $documentResponse->Data->LoadAddress;
        $document->load_location = $documentResponse->Data->LoadLocation;
        $document->load_zip_code = $documentResponse->Data->LoadZipCode;
        $document->load_date_time = $documentResponse->Data->LoadDateTime;
        $document->load_vehicle_license_plate = $documentResponse->Data->LoadVehicleLicensePlate ?? null;
        $document->load_country_code = $documentResponse->Data->LoadCountryCode;
        $document->unload_address = $documentResponse->Data->UnloadAddress;
        $document->unload_location = $documentResponse->Data->UnloadLocation;
        $document->unload_zip_code = $documentResponse->Data->UnloadZipCode;
        $document->unload_date_time = $documentResponse->Data->UnloadDateTime;
        $document->unload_country_code = $documentResponse->Data->UnloadCountryCode;
        $document->hash_characters = $documentResponse->Data->HashCharacters;
        $document->ta_doc_code_id = $documentResponse->Data->TADocCodeID;
        $document->atcudd = $documentResponse->Data->ATCUD;
        $document->table_number = $documentResponse->Data->TableNumber;
        $document->table_split_number = $documentResponse->Data->TableSplitNumber;
        $document->sales_person_code = $documentResponse->Data->SalesPersonCode;
        $document->remarks = $documentResponse->Data->Remarks;
        $document->save();

        foreach($documentResponse->Data->Details as $detail){
            $documentDetail = new Winmax4DocumentDetail();
            $documentDetail->document_id = $document->id;
            $documentDetail->article_id = Winmax4Article::where('code', $detail->ArticleCode)->first()->id;
            $documentDetail->unitary_price_without_taxes = $detail->UnitaryPriceWithoutTaxes;
            $documentDetail->unitary_price_with_taxes = $detail->UnitaryPriceWithTaxes;
            $documentDetail->discount_percentage_1 = $detail->DiscountPercentage1;
            $documentDetail->quantity = $detail->Quantity;
            $documentDetail->total_without_taxes = $detail->TotalWithoutTaxes;
            $documentDetail->total_with_taxes = $detail->TotalWithTaxes;
            $documentDetail->remarks = $detail->Remarks;

            $documentDetail->save();
        }

        foreach ($documentResponse->Data->Taxes as $tax) {
            $documentTax = new Winmax4DocumentTax();
            $documentTax->document_id = $document->id;
            $documentTax->tax_fee_code = $tax->TaxFeeCode;
            $documentTax->percentage = $tax->Percentage;
            $documentTax->fixedAmount = $tax->FixedAmount ?? 0;
            $documentTax->total_affected = $tax->TotalAffected;
            $documentTax->total = $tax->Total;
            $documentTax->save();
        }

        return $document;
    }
}