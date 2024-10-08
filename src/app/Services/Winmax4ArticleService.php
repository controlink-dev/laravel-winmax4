<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4Warehouse;
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
}