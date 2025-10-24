<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Carbon\Carbon;
use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Document;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentDetail;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentDetailTax;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentPaymentTypes;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentRelation;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentTax;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4PaymentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Warehouse;
use GuzzleHttp\Exception\ConnectException;
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
    public function getDocuments($fromDate = null, $documentTypeCode = null, $documentNumber = null, $serie = null, $number = null, $externalIdentification = null, $toDate = null, $entityCode = null, $entityTaxPayerID = null, $salesPersonCode = null, $includeRemarks = 'DocumentsAndDetails', $includeCustomContent = true, $includeRelatedDocuments = true, $liquidateStatus = 'All', $order = 'DocumentDateAsc', $format = 'JSON'): object|array|null
    {
        $url = 'Transactions/Documents?DocumentTypeCode=' . $documentTypeCode .
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
            '&IncludeCustomContent=' . ($includeCustomContent ? 'true' : 'false') .
            '&IncludeRelatedDocuments='. ($includeRelatedDocuments ? 'true' : 'false') .
            '&LiquidateStatus=' . $liquidateStatus .
            '&Order=' . $order .
            '&Format=' . $format;

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

        if (is_array($responseJSONDecoded) && $responseJSONDecoded['error'] === true) {
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
    public function postDocuments(object $documentType, Winmax4Warehouse $warehouse, Winmax4Entity $entity, $paymentType, array $details, float $valueInvoice, bool $isNC = false, array $RelatedDocuments = null): object|array|null
    {
        if($isNC){
            if(count($RelatedDocuments) == 0){
                return response()->json([
                    'status' => 'error',
                    'message' => 'The related documents are required for credit notes',
                ], 404);
            }
        }

        $paymentTypeJson = [];
        if($paymentType && $paymentType->id_winmax4 != 0){
            $paymentTypeJson = [
                [
                    'ID' => $paymentType->id_winmax4,
                    'Value' => $valueInvoice,
                ],
            ];
        }

        $json = [
            'DocumentTypeCode' => $documentType->code,
            'IsPOS' => true,
            'SourceWarehouseCode' => $warehouse->code,
            'TargetWarehouseCode' => $warehouse->code,
            'Entity' => [
                'Code' => $entity->code,
                'TaxPayerID' => $entity->tax_payer_id,
            ],
            'Details' => $details,
            'Format' => 0,
        ];

        if($paymentType){
            $json['PaymentTypes'] = $paymentTypeJson;
        }
        if ($isNC){
            $json['RelatedDocuments'] = $RelatedDocuments;
        }

        try{
            $response = $this->client->post('Transactions/Documents', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
                'json' => $json
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        $documentResponse = json_decode($response->getBody()->getContents());

        if (is_array($documentResponse) && $documentResponse['error'] === true) {
            return $documentResponse;
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
        if(!$isNC && isset($paymentType)){
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

        if(isset($documentResponse->Data->Taxes)){
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
        }


        //Split the document number to get the type document and the number of the documentNumberRelation (e.g. FR A2025/1 -> FR is the type and A2025/1 is the number)

        if($isNC){
            $DocsToReicive = [];
            $totalNCPay = 0;
            foreach ($RelatedDocuments as $relatedDocument) {

                $documentRelation = Winmax4Document::where('document_number', $relatedDocument['DocumentNumber'])
                    ->where('document_type_id', Winmax4DocumentType::where('code', $relatedDocument['DocumentTypeCode'])->first()->id)
                    ->first();
                $documentRelation->delete();
                if ($documentRelation->total_liquidated == 0){
                    $DocsToReicive[] = [
                        'DocumentTypeCode' => $documentRelation->documentType->code,
                        'DocumentNumber' => $documentRelation->document_number,
                        'Serie' => $documentRelation->serie,
                        'Number' => $documentRelation->number,
                        'Year' => Carbon::parse($documentRelation->date)->year,
                        'value' => $documentRelation->total_with_taxes,
                    ];
                    $totalNCPay += $documentRelation->total_with_taxes;
                }

                //Adicionar a tabela winmax4_documents_relation
                $documentRelationLink = new Winmax4DocumentRelation();
                $documentRelationLink->document_id = $documentRelation->id;
                $documentRelationLink->related_document_id = $document->id;
                $documentRelationLink->save();
            }

            if ($DocsToReicive != []){
                //Add the NC to the DocsToReicive
                $DocsToReicive[] = [
                    'DocumentTypeCode' => $document->documentType->code,
                    'DocumentNumber' => $document->document_number,
                    'Serie' => $document->serie,
                    'Number' => $document->number,
                    'Year' => Carbon::parse($document->date)->year,
                    //'value' => $document->total_with_taxes,
                    'value' => $totalNCPay,
                ];

                $this->payDocuments($entity->code, $DocsToReicive, $totalNCPay);
            }
        }

        return $document;
    }

    /**
     * Pays documents on the Winmax4 generating a receipt.
     *
     * This method posts a payment for a document to the Winmax4 API.
     *
     * @param string $entityCode The code of the entity to pay the documents for.
     * @param array $documents An array of documents to be paid.
     * @param float|null $value The value to be paid, if applicable.
     * @param int|null $PaymentTypeID The ID of the payment type to be used, if applicable.
     * @return object|array|null Returns the API response decoded from JSON, or null on failure.
     * @throws GuzzleException If there is a problem with the HTTP request.
     */
    public function payDocuments(string $entityCode, array $documents, ?float $value = null, int $PaymentTypeID = null): object|array|null
    {
        $entity = Winmax4Entity::where('code', $entityCode)->first();

        if(!$entity){
            return response()->json([
                'status' => 'error',
                'message' => 'Entity not found',
            ], 404);
        }

        try{
            $response = $this->client->post('Transactions/DocumentsPayment', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
                'json' => [
                    'EntityCode' => $entityCode,
                    'Documents' => $documents,
                    'Value' => $value,
                    'PaymentTypeID' => $PaymentTypeID,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        $paymentResponse = json_decode($response->getBody()->getContents());

        if (is_array($paymentResponse) && $paymentResponse['error'] === true) {
            return $paymentResponse;
        }

        $result = new \stdClass();
        $result->documents = [];

        foreach($paymentResponse->Data->Documents as $document){
            $fullDocument = self::getDocuments(null, $document->DocumentTypeCode, $document->DocumentNumber, $document->Serie, $document->Number, null, null, $entityCode, null, null, 'DocumentsAndDetails', true, 'All', 'DocumentDateAsc', 'JSON');
            $winmax4Document = $fullDocument->Data->Documents[0];

            $localDocument = new Winmax4Document();
            $localDocument->document_type_id = Winmax4DocumentType::where('code', $document->DocumentTypeCode)->first()->id;
            $localDocument->document_number = $document->DocumentNumber;
            $localDocument->serie = $document->Serie;
            $localDocument->number = $document->Number;
            $localDocument->date = $document->Date;
            $localDocument->external_identification = $document->ExternalIdentification ?? null;
            $localDocument->currency_code = $winmax4Document->CurrencyCode;
            $localDocument->is_deleted = $winmax4Document->IsDeleted;
            $localDocument->user_login = $winmax4Document->UserLogin;
            $localDocument->terminal_code = $winmax4Document->TerminalCode;
            $localDocument->source_warehouse_code = $winmax4Document->SourceWarehouseCode;
            $localDocument->target_warehouse_code = $winmax4Document->TargetWarehouseCode ?? null;
            $localDocument->entity_id = Winmax4Entity::where('code', $entityCode)->first()->id;
            $localDocument->total_without_taxes = $winmax4Document->TotalWithoutTaxes;
            $localDocument->total_applied_taxes = $winmax4Document->TotalAppliedTaxes;
            $localDocument->total_with_taxes = $winmax4Document->TotalWithTaxes;
            $localDocument->total_liquidated = $winmax4Document->TotalLiquidated;
            $localDocument->load_address = $winmax4Document->LoadAddress;
            $localDocument->load_location = $winmax4Document->LoadLocation;
            $localDocument->load_zip_code = $winmax4Document->LoadZipCode;
            $localDocument->load_date_time = $winmax4Document->LoadDateTime ?? null;
            $localDocument->load_vehicle_license_plate = $winmax4Document->LoadVehicleLicensePlate ?? null;
            $localDocument->load_country_code = $winmax4Document->LoadCountryCode ?? null;
            $localDocument->unload_address = $winmax4Document->UnloadAddress ?? null;
            $localDocument->unload_location = $winmax4Document->UnloadLocation ?? null;
            $localDocument->unload_zip_code = $winmax4Document->UnloadZipCode ?? null;
            $localDocument->unload_date_time = $winmax4Document->UnloadDateTime ?? null;
            $localDocument->unload_country_code = $winmax4Document->UnloadCountryCode ?? null;
            $localDocument->hash_characters = $winmax4Document->HashCharacters ?? null;
            $localDocument->ta_doc_code_id = $winmax4Document->TADocCodeID ?? null;
            $localDocument->atcud = $winmax4Document->ATCUD ?? null;
            $localDocument->table_number = $winmax4Document->TableNumber ?? null;
            $localDocument->table_split_number = $winmax4Document->TableSplitNumber ?? null;
            $localDocument->sales_person_code = $winmax4Document->SalesPersonCode ?? null;
            $localDocument->remarks = $winmax4Document->Remarks ?? null;
            $localDocument->save();

            $result->documents[] = $localDocument;

            if(isset($fullDocument->Data->Taxes)){
                foreach ($fullDocument->Data->Taxes as $tax) {
                    $documentTax = new Winmax4DocumentTax();
                    $documentTax->document_id = $localDocument->id;
                    $documentTax->tax_fee_code = $tax->TaxFeeCode;
                    $documentTax->percentage = $tax->Percentage;
                    $documentTax->fixedAmount = $tax->FixedAmount ?? 0;
                    $documentTax->total_affected = $tax->TotalAffected;
                    $documentTax->total = $tax->Total;
                    $documentTax->save();
                }
            }

            foreach($document->RelatedDocuments as $relatedDocument){
                $relatedLocalDocument = Winmax4Document::withTrashed()
                    ->where('document_number', $relatedDocument->DocumentNumber)
                    ->where('document_type_id', Winmax4DocumentType::where('code', $relatedDocument->DocumentTypeCode)->first()->id)
                    ->where('serie', $relatedDocument->Serie)
                    ->where('number', $relatedDocument->Number)
                    ->first();

                $relatedLocalDocument->total_liquidated = $relatedDocument->Total - $relatedDocument->TotalNotLiquidated;
                $relatedLocalDocument->save();

                //Adicionar a tabela winmax4_documents_relation
                $documentRelation = new Winmax4DocumentRelation();
                $documentRelation->document_id = $relatedLocalDocument->id;
                $documentRelation->related_document_id = $localDocument->id;
                $documentRelation->save();
            }
        }

        return $result;
        //return $paymentResponse;
    }


    /**
     * Deletes a document from Winmax4 API
     *
     * This method deletes a document from the Winmax4 API using the provided document type, number, serie, number, and cancel reason.
     *
     * ### Parameters
     * @param string $documentTypeCode The code of the document type to be deleted.
     * @param string $documentNumber The number of the document to be deleted.
     * @param int $year The year of the document to be deleted.
     * @param string $serie The serie of the document to be deleted.
     * @param int $number The number of the document to be deleted.
     * @param string $CancelReason The reason for cancelling the document.
     * @return object|array|null Returns the API response decoded from JSON, or null on failure.
     * @throws GuzzleException If there is a problem with the HTTP request.
     */
    public function deleteDocuments(string $documentTypeCode, string $documentNumber, int $year,string $serie, int $number, string $CancelReason): object|array|null
    {
        try{
            $response = $this->client->delete('Transactions/Documents', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
                'json' => [
                    'DocumentTypeCode' => $documentTypeCode,
                    'DocumentNumber' => $documentNumber,
                    'Year' => $year,
                    'Serie' => $serie,
                    'Number' => $number,
                    'CancelReason' => $CancelReason,
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

        $localDocument = Winmax4Document::where('document_number', $documentNumber)
            ->where('document_type_id', Winmax4DocumentType::where('code', $documentTypeCode)->first()->id)
            ->where('serie', $serie)
            ->where('number', $number)
            ->with('documentType')
            ->first();


        $localDocument->is_deleted = true;
        $localDocument->cancel_reason = $CancelReason;
        $localDocument->save();
        $localDocument->delete();


        //Validar se o documento tem documentos relacionados
        $relatedDocuments = Winmax4DocumentRelation::where('document_id', $localDocument->id)
            ->orWhere('related_document_id', $localDocument->id)
            ->get();
        //order by id ascending
        $relatedDocuments = $relatedDocuments->sortBy('id');

        if ($relatedDocuments){
            $result = $this->getDocuments(null,
                $localDocument->documentType->code,
                $localDocument->document_number,
                $localDocument->serie,
                $localDocument->number,
                null, null, null, null, null,
                'DocumentsAndDetails',
                true, 'All', 'DocumentDateAsc', 'JSON');
            $documentData = $result->Data->Documents[0];

            foreach ($relatedDocuments as $relation) {
                // Identify the related document (it can be on either side of the relation)
                $relatedDocumentId = ($relation->document_id == $localDocument->id)
                    ? $relation->related_document_id
                    : $relation->document_id;


                $relatedDocument = Winmax4Document::withTrashed()->with('documentType')->find($relatedDocumentId);

                if (!$relatedDocument) {
                    continue;
                }

                //Se o documento anulado for uma Nota de Crédito
                if ($localDocument->documentType->code == 'NC') {
                    $relatedDocument->deleted_at = null;

                    //Validar se o $relatedDocument (FA) têm algum documento relacionado que seja um Recibo
                    $relatedDocumentRelations = Winmax4DocumentRelation::where('document_id', $relatedDocument->id)->get();
                    $hasReceipt = false;
                    foreach ($relatedDocumentRelations as $relatedRelation) {
                        $relatedDocId = $relatedRelation->related_document_id;

                        $relatedDoc = Winmax4Document::withTrashed()->find($relatedDocId);
                        if ($relatedDoc && $relatedDoc->document_type_id == Winmax4DocumentType::where('code', 'RE')->first()->id) {
                            $hasReceipt = true;
                            break;
                        }
                    }
                    if (!$hasReceipt) {
                        $relatedDocument->total_liquidated = 0;
                    }

                    $relatedDocument->save();
                }

                //Se o documento relacionado for uma Nota de Crédito
                elseif ($relatedDocument->documentType->code == 'NC') {
                    $relatedDocument->total_liquidated = 0;
                    $relatedDocument->save();
                }

                else {
                    foreach ($documentData->RelatedDocuments as $doc) {
                        //Validar se o documento relacionado é o mesmo que estamos a processar
                        if ($doc->DocumentTypeCode == $relatedDocument->documentType->code &&
                            $doc->DocumentNumber == $relatedDocument->document_number) {
                            $relatedDocument->total_liquidated = $relatedDocument->total_liquidated - $doc->TotalRelation;
                            $relatedDocument->save();

                            break;
                        }
                    }
                }

                $relation->delete();
            }
        }
        return $responseJSONDecoded;
    }
}