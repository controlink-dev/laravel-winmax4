<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Warehouse;
use Decimal\Decimal;
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
    public function getArticles(): object|array|null
    {
        $url = $this->url . '/Files/Articles?IncludeTaxes=true&IncludeCategories=true&IncludeExtras=true&IncludeHolds=true&IncludeDescriptives=true&IncludeQuestions=true';

        foreach (Winmax4Currency::all() as $currency) {
            $url .= "&PriceCurrencyCode=". $currency->code;
        }

        foreach (Winmax4Warehouse::all() as $warehouse) {
            $url .= "&StockWarehouseCode=". $warehouse->code;
        }

        $response = $this->client->get($url, [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
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
     * @param string $firstPrice First price of the article.
     * @param string $secondPrice Second price of the article.
     * @param string|null $subFamilyCode Code of the article's subfamily (optional).
     * @param string|null $subSubFamilyCode Code of the article's sub-subfamily (optional).
     * @param int|null $stock Stock quantity (optional, if applicable).
     * @param int|null $is_active Indicates if the article is active
     * @return object|array|null Decoded JSON response from the API.
     */
    public function postArticles(string $code, string $designation, string $familyCode, string $vatCode, string $vatRate, string $firstPrice, string $secondPrice, string $subFamilyCode = null, string $subSubFamilyCode = null, ?int $stock = 0, ?int $is_active = 1): object|array|null
    {
        $url = $this->url . '/files/articles';

        $response = $this->client->post($url, [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'Code' => $code,
                'Designation' => $designation,
                'FamilyCode' => $familyCode,
                'SubFamilyCode' => $subFamilyCode,
                'SubSubFamilyCode' => $subSubFamilyCode,
                'SaleTaxFees' => [
                    'TaxFeeCode' => $vatCode,
                    'FixedAmount' => $vatRate,
                ],
                'ArticlePrices' => [
                    'PricesIncludeTaxes' => true,
                    'SalesPrice1' => $firstPrice,
                    'SalesPrice2' => $secondPrice,
                ],
                'IsActive' => $is_active,
            ],
        ]);

        if(config('winmax4.use_soft_deletes')) {
            $builder = Winmax4Article::withTrashed();
        } else {
            $builder = new Winmax4Article();
        }

        $responseDecoded = json_decode($response->getBody()->getContents());

        if($responseDecoded->Results[0]->Code !== self::WINMAX4_RESPONSE_OK){
            $idWinmax4 = $builder->where('code', $code)->first()->id_winmax4;
            $this->putEntities($idWinmax4, $code, $designation, $familyCode, $vatCode, $vatRate, $firstPrice, $secondPrice, $subFamilyCode, $subSubFamilyCode, $stock, $is_active);

            return $builder->where('code', $code)->first();
        }

        $articleData = $responseDecoded->Data->Article;
        $subFamilyCode = property_exists($articleData, 'SubFamilyCode') ? $articleData->SubFamilyCode : null;
        $subSubFamilyCode = property_exists($articleData, 'SubSubFamilyCode') ? $articleData->SubSubFamilyCode : null;
        $stock = property_exists($articleData, 'Stock') ? $articleData->Stock : 0;

        return $builder->updateOrCreate(
            [
                'id_winmax4' => $articleData->ID,
            ],
            [
                'id_winmax4' => $articleData->ID,
                'code' => $articleData->Code,
                'designation' => $articleData->Designation,
                'family_code' => $articleData->FamilyCode,
                'sub_family_code' => $subFamilyCode,
                'sub_sub_family_code' => $subSubFamilyCode,
                'vat_code' => $articleData->VatCode,
                'vat_rate' => $articleData->VatRate,
                'first_price' => $articleData->First_price ?? null,
                'second_price' => $articleData->Second_price ?? null,
                'stock' => $stock,
                'is_active' => $is_active,
            ]
        );
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
     * @return Winmax4Article Returns the updated article object.
     */
    public function putArticles(int $idWinmax4, string $code, string $designation, string $familyCode, string $vatCode, string $vatRate, string $firstPrice, string $secondPrice, string $subFamilyCode = null, string $subSubFamilyCode = null, ?int $stock = 0, ?int $is_active = 1): Winmax4Article {
        $response = $this->client->put($this->url . '/files/articles/?id=' . $idWinmax4, [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
                'http_errors' => false,
            ],
            'json' => [
                'Code' => $code,
                'Designation' => $designation,
                'FamilyCode' => $familyCode,
                'SubFamilyCode' => $subFamilyCode,
                'SubSubFamilyCode' => $subSubFamilyCode,
                'VatCode' => $vatCode,
                'VatRate' => $vatRate,
                'First_price' => $firstPrice,
                'Second_price' => $secondPrice,
                'Stock' => $stock,
                'IsActive' => $is_active,
            ],
        ]);

        $article = json_decode($response->getBody()->getContents());

        Winmax4Article::where('id_winmax4', $idWinmax4)->update([
            'code' => $article->Data->Article->Code,
            'designation' => $article->Data->Article->Designation,
            'family_code' => $article->Data->Article->FamilyCode,
            'sub_family_code' => $article->Data->Article->SubFamilyCode,
            'sub_sub_family_code' => $article->Data->Article->SubSubFamilyCode,
            'vat_code' => $article->Data->Article->VatCode,
            'vat_rate' => $article->Data->Article->VatRate,
            'first_price' => $article->Data->Article->First_price,
            'second_price' => $article->Data->Article->Second_price,
            'has_stock' => $article->Data->Article->Has_stock,
            'stock' => $article->Data->Article->Stock,
            'is_active' => $article->Data->Article->IsActive,
        ]);

        return Winmax4Article::where('id_winmax4', $idWinmax4)->first();
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
     * @return JsonResponse|Winmax4Article JSON response or deleted article object.
     * @throws GuzzleException
     */
    public function deleteArticles(int $idWinmax4): Winmax4Article|JsonResponse
    {
        $localArticle = Winmax4Article::where('id_winmax4', $idWinmax4)->first();

        $response = $this->client->delete($this->url . '/files/articles/?id=' . $idWinmax4, [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
                'http_errors' => false,
            ],
        ]);

        $article = json_decode($response->getBody()->getContents());

        if ($article->Results[0]->Code !== self::WINMAX4_RESPONSE_OK) {

            // If the result is not OK, we will disable the article
            $article = $this->putArticles($idWinmax4, $localArticle->code, $localArticle->designation, $localArticle->family_code, $localArticle->sub_family_code, $localArticle->sub_sub_family_code, $localArticle->vat_code, $localArticle->vat_rate, $localArticle->first_price, $localArticle->second_price, $article->stock, 0);

            return $article;

        } else {

            $localArticle->forceDelete();

            return response()->json([
                'message' => 'Article deleted successfully',
            ]);
        }
    }

}