<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Warehouse;
use Decimal\Decimal;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;

class Winmax4ArticleService extends Winmax4Service
{
    /**
     * Get articles from Winmax4 API
     *
     * This method sends a GET request to the specified URL endpoint to fetch a
     * list of articles. It uses the Guzzle HTTP client for making the request and
     * requires an authorization token to access the API.
     *
     * ### Headers
     *
     * | Header           | Value                               |
     * |------------------|-------------------------------------|
     * | Authorization    | Bearer {AccessToken}                |
     * | Content-Type     | application/json                    |
     *
     * The method fetches data from the endpoint `/Files/Articles` and expects a
     * JSON response which is then decoded into an object or array.
     *
     * The endpoint also includes taxes, categories, extras, holds, descriptives,
     * and questions in the response, which can be included or excluded.
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
    public function getArticles($lastChangeDateAfter = null): object|array|null
    {
        $url = 'Files/Articles?IncludeTaxes=true&IncludeCategories=true&IncludeExtras=true&IncludeHolds=true&IncludeDescriptives=true&IncludeQuestions=true';

        if($lastChangeDateAfter){
            $url .= "&LastChangeDateAfter=". $lastChangeDateAfter;
        }

        foreach (Winmax4Currency::all() as $currency) {
            $url .= "&PriceCurrencyCode=". $currency->code;
        }

        foreach (Winmax4Warehouse::all() as $warehouse) {
            $url .= "&StockWarehouseCode=". $warehouse->code;
        }

        try {
            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);

            $responseJSONDecoded = json_decode($response->getBody()->getContents());

            if (is_array($responseJSONDecoded) && $responseJSONDecoded['error'] === true) {
                return $responseJSONDecoded;
            }

            if(is_null($responseJSONDecoded)){
                return null;
            }

            if($responseJSONDecoded->Data->Filter->TotalPages > 1){
                for($i = 2; $i <= $responseJSONDecoded->Data->Filter->TotalPages; $i++){
                    $response = $this->client->get($url . '&PageNumber=' . $i, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                        ],
                    ]);

                    $responseJSONDecoded->Data->Articles = array_merge($responseJSONDecoded->Data->Articles, json_decode($response->getBody()->getContents())->Data->Articles);
                }
            }

            return $responseJSONDecoded;

        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }
    }

    /**
     * Send article data to the Winmax4 API
     *
     * This method sends a POST request to the `/Files/Articles` endpoint to create
     * or update an article. It uses the Guzzle HTTP client for the request and
     * requires a valid authorization token.
     *
     * ### Headers
     *
     * | Header           | Value                               |
     * |------------------|-------------------------------------|
     * | Authorization    | Bearer {AccessToken}                |
     * | Content-Type     | application/json                    |
     *
     * ### Payload
     *
     * The request body must be a JSON object containing the following fields:
     *
     * | Parameter         | Type      | Description                                   |
     * |-------------------|-----------|-----------------------------------------------|
     * | `Code`            | `string`  | Unique article code.                         |
     * | `Designation`     | `string`  | Article name or designation.                 |
     * | `FamilyCode`      | `string`  | Code of the article's family.                |
     * | `SubFamilyCode`   | `string`  | Code of the article's subfamily (optional).  |
     * | `SubSubFamilyCode`| `string`  | Code of the article's sub-subfamily (optional). |
     * | `VatCode`         | `string`  | VAT code for the article.                    |
     * | `VatRate`         | `string`  | VAT rate as a percentage (e.g., "23").       |
     * | `First_price`     | `string`  | First price of the article.                  |
     * | `Second_price`    | `string`  | Second price of the article.                 |
     * | `Stock`           | `int|null`| Stock quantity (optional, if applicable).    |
     *
     * ### Return
     *
     * | Type         | Description                                  |
     * |--------------|----------------------------------------------|
     * | `object`     | Decoded JSON response object from the API.   |
     * | `array`      | Decoded JSON response array if applicable.   |
     * | `null`       | Returns null if the response is empty or invalid. |
     *
     * ### Exceptions
     *
     * | Exception                              | Condition                                         |
     * |----------------------------------------|---------------------------------------------------|
     * | `GuzzleHttp\Exception\GuzzleException` | Thrown when the HTTP request fails for any reason.|
     *
     * @param string $code Unique article code
     * @param string $designation Article name or designation.
     * @param string $familyCode Code of the article's family.
     * @param string $vatCode VAT code for the article.
     * @param string $vatRate VAT rate as a percentage (e.g., "23").
     * @param string $priceWithoutVat Price without VAT.
     * @param string $priceWithVat Price with VAT.
     * @param string|null $subFamilyCode Code of the article's subfamily (optional).
     * @param string|null $subSubFamilyCode Code of the article's sub-subfamily (optional).
     * @param int|null $stock Stock quantity (optional, if applicable).
     * @param int|null $is_active Indicates if the article is active
     * @return object|array|null Decoded JSON response from the API.
     */
    public function postArticles(string $code, string $designation, string $familyCode, string $vatCode, string $vatRate, string $priceWithoutVat, string $priceWithVat, string $subFamilyCode = null, string $subSubFamilyCode = null, ?int $stock = 0, ?int $is_active = 1): object|array|null
    {
        try {
            $response = $this->client->post('Files/Articles', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
                'json' => [
                    'Code' => $code,
                    'Designation' => $designation,
                    'FamilyCode' => $familyCode,
                    'SubFamilies' => [
                        'SubFamilyCode' => $subFamilyCode,
                        'SubSubFamilyCode' => $subSubFamilyCode,
                    ],
                    'IsActive' => $is_active,
                    'ArticlePrices' => [
                        [
                            'CurrencyCode' => 'EUR',
                            'PricesIncludeTaxes' => true,
                            'SalesPrice1' => $priceWithVat,
                        ]
                    ],
                    'SaleTaxFees' => [[
                        'TaxFeeCode' => $vatCode,
                        'Percentage' => $vatRate,
                        'FixedAmount' => 0,
                    ]],
                    'PurchaseTaxFees' => [[
                        'TaxFeeCode' => $vatCode,
                        'Percentage' => $vatRate,
                        'FixedAmount' => 0,
                    ]],
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        if(config('winmax4.use_soft_deletes')) {
            $builder = Winmax4Article::withTrashed();
        } else {
            $builder = new Winmax4Article();
        }

        $responseDecoded = json_decode($response->getBody()->getContents());

        if (isset($responseDecoded) && isset($responseDecoded->error) && $responseDecoded->error === true) {
            return $responseDecoded;
        }

        $articleData = $responseDecoded->Data->Article;
        $subFamilyCode = property_exists($articleData, 'SubFamilyCode') ? $articleData->SubFamilyCode : null;
        $subSubFamilyCode = property_exists($articleData, 'SubSubFamilyCode') ? $articleData->SubSubFamilyCode : null;
        $stock = property_exists($articleData, 'Stock') ? $articleData->Stock : 0;

        $article = $builder->updateOrCreate(
            [
                'code' => $articleData->Code,
            ],
            [
                'id_winmax4' => $articleData->ID,
                'code' => $articleData->Code,
                'designation' => $articleData->Designation,
                'family_code' => $articleData->FamilyCode,
                'sub_family_code' => $subFamilyCode,
                'sub_sub_family_code' => $subSubFamilyCode,
                'is_active' => $articleData->IsActive,
            ]
        );

        if (isset($articleData->Prices) && is_array($articleData->Prices)) {
            foreach ($articleData->Prices as $price) {
                $article->prices()->updateOrCreate(
                    [
                        'article_id' => $article->id,
                    ],
                    [
                        'article_id' => $article->id,
                        'currency_code' => $price->CurrencyCode,
                        'sales_price1_without_taxes' => $price->SalesPrice1WithoutTaxes ?? 0,
                        'sales_price1_with_taxes' => $price->SalesPrice1WithTaxes ?? 0,
                        'sales_price2_without_taxes' => $price->SalesPrice2WithoutTaxes ?? 0,
                        'sales_price2_with_taxes' => $price->SalesPrice2WithTaxes ?? 0,
                        'sales_price3_without_taxes' => $price->SalesPrice3WithoutTaxes ?? 0,
                        'sales_price3_with_taxes' => $price->SalesPrice3WithTaxes ?? 0,
                        'sales_price4_without_taxes' => $price->SalesPrice4WithoutTaxes ?? 0,
                        'sales_price4_with_taxes' => $price->SalesPrice4WithTaxes ?? 0,
                        'sales_price5_without_taxes' => $price->SalesPrice5WithoutTaxes ?? 0,
                        'sales_price5_with_taxes' => $price->SalesPrice5WithTaxes ?? 0,
                        'sales_price6_without_taxes' => $price->SalesPrice6WithoutTaxes ?? 0,
                        'sales_price6_with_taxes' => $price->SalesPrice6WithTaxes ?? 0,
                        'sales_price7_without_taxes' => $price->SalesPrice7WithoutTaxes ?? 0,
                        'sales_price7_with_taxes' => $price->SalesPrice7WithTaxes ?? 0,
                        'sales_price8_without_taxes' => $price->SalesPrice8WithoutTaxes ?? 0,
                        'sales_price8_with_taxes' => $price->SalesPrice8WithTaxes ?? 0,
                        'sales_price9_without_taxes' => $price->SalesPrice9WithoutTaxes ?? 0,
                        'sales_price9_with_taxes' => $price->SalesPrice9WithTaxes ?? 0,
                        'sales_price_extra_without_taxes' => $price->SalesPriceExtraWithoutTaxes ?? 0,
                        'sales_price_extra_with_taxes' => $price->SalesPriceExtraWithTaxes ?? 0,
                        'sales_price_hold_without_taxes' => $price->SalesPriceHoldWithoutTaxes ?? 0,
                        'sales_price_hold_with_taxes' => $price->SalesPriceHoldWithTaxes ?? 0,
                    ]
                );
            }
        }

        if (isset($articleData->SaleTaxes) && is_array($articleData->SaleTaxes)) {
            foreach ($articleData->SaleTaxes as $saleTax) {
                $article->saleTaxes()->updateOrCreate(
                    [
                        'article_id' => $article->id,
                    ],
                    [
                        'article_id' => $article->id,
                        'tax_fee_code' => $saleTax->TaxFeeCode,
                        'percentage' => $saleTax->Percentage,
                        'fixedAmount' => $saleTax->FixedAmount ?? 0,
                    ]
                );
            }
        }

        if (isset($articleData->PurchaseTaxes) && is_array($articleData->PurchaseTaxes)) {
            foreach ($articleData->PurchaseTaxes as $purchaseTax) {
                $article->purchaseTaxes()->updateOrCreate(
                    [
                        'article_id' => $article->id,
                    ],
                    [
                        'article_id' => $article->id,
                        'tax_fee_code' => $purchaseTax->TaxFeeCode,
                        'percentage' => $purchaseTax->Percentage,
                        'fixedAmount' => $purchaseTax->FixedAmount ?? 0,
                    ]
                );
            }
        }

        return $article->toArray();
    }
    
    /**
     * Update articles in the Winmax4 API
     *
     * This method updates an article in the Winmax4 API using the provided article details.
     *
     * ### Parameters
     *
     * | Parameter         | Type    | Description                                   | Default |
     * |-------------------|---------|-----------------------------------------------|---------|
     * | `$idWinmax4`      | `int`   | The article ID in Winmax4.                    | N/A     |
     * | `$code`           | `string`| Unique code for the article.                  | N/A     |
     * | `$designation`    | `string`| The article designation (name).               | N/A     |
     * | `$familyCode`     | `string`| Code of the article's family.                 | N/A     |
     * | `$subFamilyCode`  | `string`| Code of the article's sub-family (optional).  | `null`  |
     * | `$subSubFamilyCode`| `string`| Code of the article's sub-sub-family (optional). | `null`  |
     * | `$vatCode`        | `string`| VAT code associated with the article.         | N/A     |
     * | `$vatRate`        | `string`| VAT rate as a percentage.                     | N/A     |
     * | `$firstPrice`     | `string`| The primary price of the article.             | N/A     |
     * | `$secondPrice`    | `string`| The secondary price of the article.           | N/A     |
     * | `$stock`          | `int`   | The stock quantity (optional if applicable).  | `null`  |
     * | `$is_active`      | `int`   | Indicates if the article is active.           | `1`     |
     *
     * ### Return
     *
     * | Type         | Description                                  |
     * |--------------|----------------------------------------------|
     * | `Winmax4Article` | Returns the updated article object from the database. |
     *
     * ### Exceptions
     *
     * | Exception         | Condition                                   |
     * |-------------------|---------------------------------------------|
     * | `GuzzleException` | Throws when there is an HTTP client error.  |
     *
     * @param int $idWinmax4 The article ID in Winmax4.
     * @param string $code Unique code for the article.
     * @param string $designation The article designation (name).
     * @param string $familyCode Code of the article's family.
     * @param string|null $subFamilyCode Code of the article's sub-family (optional).
     * @param string|null $subSubFamilyCode Code of the article's sub-sub-family (optional).
     * @param string $vatCode VAT code associated with the article.
     * @param string $vatRate VAT rate as a percentage.
     * @param string $firstPrice The primary price of the article.
     * @param string $secondPrice The secondary price of the article.
     * @param int|null $stock The stock quantity (optional if applicable).
     * @param int|null $is_active Indicates if the article is active.
     * @return array Returns the updated article object.
     */
    public function putArticles(int $idWinmax4, string $code, string $familyCode, string $vatCode, string $vatRate, string $priceWithoutVat, string $priceWithVat, string $subFamilyCode = null, string $subSubFamilyCode = null, ?int $stock = 0, ?int $is_active = 1): array
    {
        try {
            $response = $this->client->put('Files/Articles/?id=' . $idWinmax4, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
                'json' => [
                    'FamilyCode' => $familyCode,
                    'SubFamilies' => [
                        'SubFamilyCode' => $subFamilyCode,
                        'SubSubFamilyCode' => $subSubFamilyCode,
                    ],
                    'IsActive' => $is_active,
                    'ArticlePrices' => [
                        [
                            'CurrencyCode' => 'EUR',
                            'PricesIncludeTaxes' => true,
                            'SalesPrice1' => $priceWithVat,
                        ]
                    ],
                    'SaleTaxFees' => [[
                        'TaxFeeCode' => $vatCode,
                        'Percentage' => $vatRate,
                        'FixedAmount' => 0,
                    ]],
                    'PurchaseTaxFees' => [[
                        'TaxFeeCode' => $vatCode,
                        'Percentage' => $vatRate,
                        'FixedAmount' => 0,
                    ]],
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        $responseDecoded = json_decode($response->getBody()->getContents());

        if (isset($responseDecoded) && isset($responseDecoded->error) && $responseDecoded->error === true) {
            return $responseDecoded;
        }

        if (!isset($responseDecoded->Data)) {
            $errorMessage = isset($responseDecoded->Results[0])
                ? json_encode($responseDecoded->Results[0])
                : 'Unknown error: Data not found in response.';
            throw new \Exception($errorMessage);
        }

        $articleData = $responseDecoded->Data->Article;
        dd($articleData);
        $subFamilyCode = property_exists($articleData, 'SubFamilyCode') ? $articleData->SubFamilyCode : null;
        $subSubFamilyCode = property_exists($articleData, 'SubSubFamilyCode') ? $articleData->SubSubFamilyCode : null;
        $stock = property_exists($articleData, 'Stock') ? $articleData->Stock : 0;

        Winmax4Article::where('id_winmax4', $idWinmax4)->update([
            'code' => $articleData->Code,
            'designation' => $articleData->Designation,
            'family_code' => $articleData->FamilyCode,
            'sub_family_code' => $subFamilyCode,
            'sub_sub_family_code' => $subSubFamilyCode,
            'is_active' => $articleData->IsActive,
        ]);

        $article = Winmax4Article::where('id_winmax4', $idWinmax4)->first();

        if (isset($articleData->Prices) && is_array($articleData->Prices)) {
            foreach ($articleData->Prices as $price) {
                $article->prices()->updateOrCreate(
                    [
                        'article_id' => $article->id,
                    ],
                    [
                        'article_id' => $article->id,
                        'currency_code' => $price->CurrencyCode,
                        'sales_price1_without_taxes' => $price->SalesPrice1WithoutTaxes ?? 0,
                        'sales_price1_with_taxes' => $price->SalesPrice1WithTaxes ?? 0,
                        'sales_price2_without_taxes' => $price->SalesPrice2WithoutTaxes ?? 0,
                        'sales_price2_with_taxes' => $price->SalesPrice2WithTaxes ?? 0,
                        'sales_price3_without_taxes' => $price->SalesPrice3WithoutTaxes ?? 0,
                        'sales_price3_with_taxes' => $price->SalesPrice3WithTaxes ?? 0,
                        'sales_price4_without_taxes' => $price->SalesPrice4WithoutTaxes ?? 0,
                        'sales_price4_with_taxes' => $price->SalesPrice4WithTaxes ?? 0,
                        'sales_price5_without_taxes' => $price->SalesPrice5WithoutTaxes ?? 0,
                        'sales_price5_with_taxes' => $price->SalesPrice5WithTaxes ?? 0,
                        'sales_price6_without_taxes' => $price->SalesPrice6WithoutTaxes ?? 0,
                        'sales_price6_with_taxes' => $price->SalesPrice6WithTaxes ?? 0,
                        'sales_price7_without_taxes' => $price->SalesPrice7WithoutTaxes ?? 0,
                        'sales_price7_with_taxes' => $price->SalesPrice7WithTaxes ?? 0,
                        'sales_price8_without_taxes' => $price->SalesPrice8WithoutTaxes ?? 0,
                        'sales_price8_with_taxes' => $price->SalesPrice8WithTaxes ?? 0,
                        'sales_price9_without_taxes' => $price->SalesPrice9WithoutTaxes ?? 0,
                        'sales_price9_with_taxes' => $price->SalesPrice9WithTaxes ?? 0,
                        'sales_price_extra_without_taxes' => $price->SalesPriceExtraWithoutTaxes ?? 0,
                        'sales_price_extra_with_taxes' => $price->SalesPriceExtraWithTaxes ?? 0,
                        'sales_price_hold_without_taxes' => $price->SalesPriceHoldWithoutTaxes ?? 0,
                        'sales_price_hold_with_taxes' => $price->SalesPriceHoldWithTaxes ?? 0,
                    ]
                );
            }
        }

        if (isset($articleData->SaleTaxes) && is_array($articleData->SaleTaxes)) {
            foreach ($articleData->SaleTaxes as $saleTax) {
                $article->saleTaxes()->updateOrCreate(
                    [
                        'article_id' => $article->id,
                    ],
                    [
                        'article_id' => $article->id,
                        'tax_fee_code' => $saleTax->TaxFeeCode,
                        'percentage' => $saleTax->Percentage,
                        'fixedAmount' => $saleTax->FixedAmount ?? 0,
                    ]
                );
            }
        }

        if (isset($articleData->PurchaseTaxes) && is_array($articleData->PurchaseTaxes)) {
            foreach ($articleData->PurchaseTaxes as $purchaseTax) {
                $article->purchaseTaxes()->updateOrCreate(
                    [
                        'article_id' => $article->id,
                    ],
                    [
                        'article_id' => $article->id,
                        'tax_fee_code' => $purchaseTax->TaxFeeCode,
                        'percentage' => $purchaseTax->Percentage,
                        'fixedAmount' => $purchaseTax->FixedAmount ?? 0,
                    ]
                );
            }
        }

        return $article->toArray();
    }

    /**
     * Delete articles from Winmax4 API
     *
     * This method attempts to delete an article from the Winmax4 system using its API.
     * It sends a DELETE request to the API, which returns a response indicating the
     * success or failure of the operation. Depending on the response, the article is
     * either disabled locally in the database or deleted.
     *
     * ### API Response Handling
     *
     * The API responds with a JSON object containing a `Results` array. The method
     * checks the first result's `Code` to determine the success of the deletion.
     *
     * | Response Code           | Description                             |
     * |-------------------------|-----------------------------------------|
     * | `WINMAX4_RESPONSE_OK`   | Article deleted successfully on API side |
     * | `other`                 | API deletion failed; article is disabled locally |
     *
     * ### Soft Deletes
     *
     * The method supports soft deletes based on the application's configuration.
     * When soft deletes are enabled (`winmax4.use_soft_deletes`), the article is
     * marked as inactive in the local database without removing it completely.
     * Otherwise, a hard delete (force delete) is performed.
     *
     * ### Parameters
     *
     * | Parameter      | Type    | Description                           |
     * |----------------|---------|---------------------------------------|
     * | `$idWinmax4`   | `int`   | The ID of the article to be deleted.  |
     *
     * ### Return
     *
     * | Type             | Description                                                           |
     * |------------------|-----------------------------------------------------------------------|
     * | `JsonResponse`   | Returns a JSON response if the article is disabled locally.           |
     * | `Winmax4Article` | Returns the article object if deleted successfully.                   |
     *
     * ### Exceptions
     *
     * | Exception                                  | Condition                                         |
     * |--------------------------------------------|---------------------------------------------------|
     * | `GuzzleHttp\Exception\GuzzleException`     | Throws when there is an HTTP client error during the DELETE request. |
     *
     * @param int $idWinmax4 The ID of the Winmax4 article to delete.
     * @param bool $forceDelete Indicates whether to force delete the article.
     * @return array JSON response or deleted article object.
     * @throws GuzzleException
     */
    public function deleteArticles(int $idWinmax4, bool $forceDelete): array
    {
        $localArticle = Winmax4Article::where('id_winmax4', $idWinmax4)
            ->with('details')
            ->first();

        try{
            $response = $this->client->delete('Files/Articles/?id=' . $idWinmax4, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }


        $article = json_decode($response->getBody()->getContents(), true);

        if (is_array($article) && isset($article['error']) && $article['error'] === true) {
            return $article;
        }

        if ($article['Results'][0]['Code'] !== self::WINMAX4_RESPONSE_OK) {
            $article = $this->putArticles(
                $idWinmax4,
                $localArticle->code,
                $localArticle->family_code,
                $localArticle->saleTaxes[0]->tax_fee_code,
                $localArticle->saleTaxes[0]->percentage,
                $localArticle->prices[0]->sales_price1_without_taxes,
                $localArticle->prices[0]->sales_price1_with_taxes,
                $localArticle->sub_family_code,
                $localArticle->sub_sub_family_code,
                $localArticle->stock,
                0
            );
        } else {

            if (!$localArticle->details()->exists() && $forceDelete) {
                if(config('winmax4.use_soft_deletes')) {
                    $localArticle->forceDelete();
                    return $article;
                } else {
                    $localArticle->delete();
                    return $article;
                }
            }else{
                $localArticle->is_active = 0;
                $localArticle->deleted_at = now();
                $localArticle->save();
            }
        }

        return $article;
    }

}