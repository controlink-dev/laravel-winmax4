<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Document;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentDetail;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentDetailTax;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentPaymentTypes;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentTax;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4PaymentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Warehouse;
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
    public function getDocuments($fromDate = null, $documentTypeCode = null, $documentNumber = null, $serie = null, $number = null, $externalIdentification = null, $toDate = null, $entityCode = null, $entityTaxPayerID = null, $salesPersonCode = null, $includeRemarks = 'DocumentsAndDetails', $includeCustomContent = true, $liquidateStatus = 'All', $order = 'DocumentDateAsc', $format = 'JSON'): object|array|null
    {
        $url = $this->url . '/Transactions/Documents?DocumentTypeCode=' . $documentTypeCode .
            '&DocumentNumber=' . $documentNumber .
            '&Serie=' . $serie .
            '&Number=' . $number .
            '&ExternalIdentification=' . $externalIdentification .
            '&FromDate=' . $fromDate .
            '&ToDate=' . $toDate .
            '&EntityCode=' . $entityCode .
            '&EntityTaxPayerID=' . $entityTaxPayerID .
            '&SalesPersonCode=' . $salesPersonCode .
            '&IncludeRemarks=' . $includeRemarks .
            '&IncludeCustomContent=' . $includeCustomContent .
            '&LiquidateStatus=' . $liquidateStatus .
            '&Order=' . $order .
            '&Format=' . $format;

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

                $responseJSONDecoded->Data->Documents = array_merge($responseJSONDecoded->Data->Documents, json_decode($response->getBody()->getContents())->Data->Documents);
            }
        }

        return $responseJSONDecoded;
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
     * | `$warehouse`     | `string`                | Warehouse associated with the document                        | N/A     |
     * | `$entity`        | `string`                | Entity associated with the document                           | N/A     |
     * | `$paymentType`   | `string`                | Payment type associated with the document                     | N/A     |
     * | `$details`       | `array`                 | Array of document details                                     | N/A     |
     * | `$valueInvoice`  | `float`                 | Value of the invoice                                          | N/A     |
     * | `$isNC`          | `bool`                  | Indicates if the document is a credit note                    | false   |
     * | `$documentNumberRelation` | `string`      | Document number relation for credit notes                     | null    |
     *
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
    public function postDocuments(object $documentType, Winmax4Warehouse $warehouse, Winmax4Entity $entity, Winmax4PaymentType $paymentType, array $details, float $valueInvoice, bool $isNC = false, string $documentNumberRelation = null): object|array|null
    {
        try {
            $ExternalDocumentsRelation = '';
            if($isNC){
                if(is_null($documentNumberRelation)){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'The document number relation is required for credit notes',
                    ], 404);
                }

                $ExternalDocumentsRelation = $documentNumberRelation;
            }

            $response = $this->client->post($this->url . '/Transactions/Documents', [
                'verify' => $this->settings['verify_ssl_guzzle'],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'DocumentTypeCode' => $documentType->code,
                    'IsPOS' => true,
                    'SourceWarehouseCode' => $warehouse->code,
                    'TargetWarehouseCode' => $warehouse->code,
                    'ExternalDocumentsRelation' => $ExternalDocumentsRelation,
                    'Entity' => [
                        'Code' => $entity->code,
                        'TaxPayerID' => $entity->tax_payer_id,
                    ],
                    'PaymentTypes' => [
                        [
                            'ID' => $paymentType->id,
                            'Value' => $valueInvoice,
                        ],
                    ],
                    'Details' => $details,
                    'Format' => 0,
                ],
            ]);

            $documentResponse = json_decode($response->getBody()->getContents());

            if($documentResponse->Results[0]->Code == "COULDNTCREATEDOCUMENT"){
                return response()->json([
                    'status' => 'error',
                    'message' => $this->renderErrorMessage($documentResponse),
                ], 404);
            }

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
            $document->entity_id = Winmax4Entity::where('code', $documentResponse->Data->Entity->Code)->first()->id;
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
            $document->ta_doc_code_id = $documentResponse->Data->TADocCodeID ?? null;
            $document->atcud = $documentResponse->Data->ATCUD ?? null;
            $document->table_number = $documentResponse->Data->TableNumber ?? null;
            $document->table_split_number = $documentResponse->Data->TableSplitNumber ?? null;
            $document->sales_person_code = $documentResponse->Data->SalesPersonCode ?? null;
            $document->remarks = $documentResponse->Data->Remarks ?? null;
            $document->save();

            /** TODO: Get the $documentResponse and save the paymentsTypes with the returned values from the API
            * The API does not return the payment types, so we need to save the payment types that we sent to the API
            */
            if(!$isNC){
                $documentPaymentType = new Winmax4DocumentPaymentTypes();
                $documentPaymentType->document_id = $document->id;
                $documentPaymentType->payment_type_id = $paymentType->id;
                $documentPaymentType->designation = $paymentType->designation;
                $documentPaymentType->value = $valueInvoice;
                $documentPaymentType->save();
            }

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
                $documentDetail->remarks = $detail->Remarks ?? null;
                $documentDetail->save();

                foreach ($detail->Taxes as $tax) {
                    $documentDetailTax = new Winmax4DocumentDetailTax();
                    $documentDetailTax->document_detail_id = $documentDetail->id;
                    $documentDetailTax->tax_fee_code = $tax->TaxFeeCode;
                    $documentDetailTax->percentage = $tax->Percentage;
                    $documentDetailTax->save();
                }
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

            //Split the document number to get the type document and the number of the documentNumberRelation (e.g. FR A2025/1 -> FR is the type and A2025/1 is the number)

            if($isNC){
                $documentNumberRelation = explode(' ', $documentNumberRelation);

                $documentRelation = Winmax4Document::where('document_number', $documentNumberRelation[1])
                    ->where('document_type_id', Winmax4DocumentType::where('code', $documentNumberRelation[0])->first()->id)
                    ->first();
                $documentRelation->delete();
            }

            return $document;
        }catch (\GuzzleHttp\Exception\RequestException $e){
            // Log or handle the error response
            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();
                $errorJson = json_decode($errorResponse->getBody()->getContents(), true);

                // Return the error JSON or handle it as needed
                If($errorJson['Results'][0]['Code'] == 'COULDNTCREATEDOCUMENT'){
                    return [
                        'error' => true,
                        'status' => $errorResponse->getStatusCode(),
                        'message' => $this->renderErrorMessage($errorJson),
                    ];
                }else{
                    return [
                        'error' => true,
                        'status' => $errorResponse->getStatusCode(),
                        'message' => 'The code is unknown:' . $errorJson['Results'][0]['Code'] . '<br> The message is:' . $errorJson['Results'][0]['Message'],
                    ];
                }
            }

            // If no response is available
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }


    }

    public function renderErrorMessage($errorJson){
        switch ($errorJson->Results[0]->Fields[0]) {
            case 'InvalidArticleCode':
                $errorJson->Results[0]->Message = 'The article code is invalid';
                break;
            case 'ArticleIsNotActive':
                $errorJson->Results[0]->Message = 'The article is not active';
                break;
            case 'InvalidArticleType':
                $errorJson->Results[0]->Message = 'The article type is invalid';
                break;
            case 'InvalidUnit':
                $errorJson->Results[0]->Message = 'The unit is invalid';
                break;
            case 'InvalidTax':
                $errorJson->Results[0]->Message = 'The tax is invalid';
                break;
            case 'OutdatedBatch':
                $errorJson->Results[0]->Message = 'The batch is outdated';
                break;
            case 'InvalidBatch':
                $errorJson->Results[0]->Message = 'The batch is invalid';
                break;
            case 'ArticleWithSameSerialNumberInDocument':
                $errorJson->Results[0]->Message = 'The article with the same serial number is already in the document';
                break;
            case 'InvalidComposition':
                $errorJson->Results[0]->Message = 'The composition is invalid';
                break;
            case 'InvalidEntityCode':
                $errorJson->Results[0]->Message = 'The entity code is invalid';
                break;
            case 'ArticleNotAvailableForCurrentServiceZone':
                $errorJson->Results[0]->Message = 'The article is not available for the current service zone';
                break;
            case 'TotalIsNegative':
                $errorJson->Results[0]->Message = 'The total is negative';
                break;
            case 'UnitRequiresEDICode':
                $errorJson->Results[0]->Message = 'The unit requires an EDI code';
                break;
            case 'TaxRequiresEDICode':
                $errorJson->Results[0]->Message = 'The tax requires an EDI code';
                break;
            case 'AlreadyInDocument':
                $errorJson->Results[0]->Message = 'The article is already in the document';
                break;
            case 'InvalidTaxes':
                $errorJson->Results[0]->Message = 'The taxes are invalid';
                break;
            case 'NotEnoughStock':
                $errorJson->Results[0]->Message = 'There is not enough stock';
                break;
            case 'QuantityZero':
                $errorJson->Results[0]->Message = 'The quantity is zero';
                break;
            case 'SkipToNextDetail':
                $errorJson->Results[0]->Message = 'Skip to the next detail';
                break;
            case 'InvalidEntityInDetail':
                $errorJson->Results[0]->Message = 'The entity in the detail is invalid';
                break;
            case 'NoTaxesDefined':
                $errorJson->Results[0]->Message = 'No taxes are defined';
                break;
            case 'OnlyOnePercentageTaxAllowed':
                $errorJson->Results[0]->Message = 'Only one percentage tax is allowed';
                break;
            case 'NotAllowedOtherTaxesOverPercentageTax':
                $errorJson->Results[0]->Message = 'Not allowed other taxes over the percentage tax';
                break;
            case 'TaxRateDoesntHaveSAFTDesignation':
                $errorJson->Results[0]->Message = 'The tax rate does not have a SAFT designation';
                break;
            case 'EXCEPTION':
                $errorJson->Results[0]->Message = 'An exception occurred! Please contact the administrator';
                break;
            default:
                $errorJson->Results[0]->Message = 'An unknown error occurred! Please contact the administrator <br><br>
                The unknown code is: ' . $errorJson->Results[0]->Code . '<br> 
                The message is: ' . $errorJson->Results[0]->Message . '<br>
                The field is: ' . $errorJson->Results[0]->Fields[0];
                break;
        }

        return $errorJson;
    }
}