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
     * | `Has_stock`       | `bool`    | Indicates if the article has stock.          |
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
     * @param string $code Unique article code.
     * @param string $designation Article name or designation.
     * @param string $familyCode Code of the article's family.
     * @param string|null $subFamilyCode Code of the article's subfamily (optional).
     * @param string|null $subSubFamilyCode Code of the article's sub-subfamily (optional).
     * @param string $vatCode VAT code for the article.
     * @param string $vatRate VAT rate as a percentage (e.g., "23").
     * @param string $firstPrice First price of the article.
     * @param string $secondPrice Second price of the article.
     * @param bool $hasStock Indicates if the article has stock.
     * @param int|null $stock Stock quantity (optional, if applicable).
     * @return object|array|null Decoded JSON response from the API.
     * @throws GuzzleException If there is a problem with the HTTP request.
     */
    public function postArticles(string $code, string $designation, string $familyCode, string|null $subFamilyCode, string|null $subSubFamilyCode, string $vatCode, string $vatRate, string $firstPrice, string $secondPrice, bool $hasStock, int|null $stock): object|array|null
    {
        $url = $this->url . '/Files/Articles';

        dd($this->token->Data->AccessToken->Value);
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
                'VatCode' => $vatCode,
                'VatRate' => $vatRate,
                'First_price' => $firstPrice,
                'Second_price' => $secondPrice,
                'Has_stock' => $hasStock,
                'Stock' => $stock,
            ],
        ]);

        if(config('winmax4.use_soft_deletes')) {
            $builder = Winmax4Article::withTrashed();
        } else {
            $builder = new Winmax4Entity();
        }

        $responseDecoded = json_decode($response->getBody()->getContents());

        return false;
    }
}