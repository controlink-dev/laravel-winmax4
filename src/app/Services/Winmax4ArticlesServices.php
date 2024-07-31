<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use GuzzleHttp\Exception\GuzzleException;

class Winmax4ArticlesServices extends Winmax4Service
{
    /**---- Articles ----*/
    /**
     * Get articles from Winmax4 API
     *
     * @return object
     * @throws GuzzleException
     */
    public function getArticles()
    {
        $url = $this->url . '/Files/Articles?IncludeTaxes=true&IncludeCategories=true&IncludeExtras=true&IncludeHolds=true&IncludeDescriptives=true&IncludeQuestions=true';

        foreach (Winmax4Currency::all() as $currency) {
            $url .= "&PriceCurrencyCode=". $currency->code;
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
     * Post articles to Winmax4 API
     *
     * @param $values
     * @return object
     * @throws GuzzleException
     */
    public function postArticles($values){
        $response = $this->client->post($this->url . '/Files/Articles', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'Code' => $values['code'],
                'Designation' => $values['designation'],
                'IsActive' => 1,
                'FamilyCode' => $values['familyCode'],
                'SubFamilyCode'	 => $values['subFamilyCode'],
                'SubSubFamilyCode' => $values['subSubFamilyCode'],
                'SubSubSubFamilyCode' => $values['subSubSubFamilyCode'],
                'StockUnitCode' => $values['stockUnitCode'],
                'ImageURLs' => $values['imageURLs'],
            ],
        ]);
    }

    /**
     * Put articles to Winmax4 API
     *
     * @param $values
     * @return object
     * @throws GuzzleException
     */
    public function putArticles($values){
        $response = $this->client->put($this->url . '/Files/Articles/?id='.$values['id_winmax4'], [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'Code' => $values['code'],
                'Designation' => $values['designation'],
                'ShortDesignation' => $values['shortDesignation'],
                'IsActive' => 1,
                'FamilyCode' => $values['familyCode'],
                'SubFamilyCode'	 => $values['subFamilyCode'],
                'SubSubFamilyCode' => $values['subSubFamilyCode'],
                'SubSubSubFamilyCode' => $values['subSubSubFamilyCode'],
                'StockUnitCode' => $values['stockUnitCode'],
                'ImageURLs' => $values['imageURLs'],
            ],
        ]);

        //TODO: Update article
    }

    /**
     * Delete articles from Winmax4 API
     *
     * @param $values
     * @return \Illuminate\Http\JsonResponse
     * @throws GuzzleException
     */
    public function deleteArticles($valueID){
        $response = $this->client->delete($this->url . '/Files/Articles/?id='.$valueID, [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        $article = json_decode($response->getBody()->getContents());

        //TODO: Delete article
    }
}