<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Dflydev\DotAccessData\Data;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class Winmax4Service
{
    protected $client;
    protected $url;
    protected $settings;
    protected $token;

    const WINMAX4_RESPONSE_OK = 'OK';
    const WINMAX4_RESPONSE_EXCEPTION = 'EXCEPTION';

    public function __construct($saveMode = false, $url = '', $company_code = '', $username = '', $password = '', $n_terminal = '')
    {
        $this->client = new Client();
        $this->settings = config('winmax4');
        $this->url = $url;

        if (!$saveMode) {
            $this->token = $this->generateToken($url, $company_code, $username, $password, $n_terminal);
        }

    }

    /**
     * Authenticate to Winmax4 API
     *
     * @param string $url
     * @param string $company_code
     * @param string $username
     * @param string $password
     * @param string $n_terminal
     * @return object
     * @throws GuzzleException
     */
    public function generateToken($url, $company_code, $username, $password, $n_terminal)
    {
        $response = $this->client->post($url . '/Account/GenerateToken', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'json' => [
                'Company' => $company_code,
                'UserLogin' => $username,
                'Password' => $password,
                'TerminalCode' => $n_terminal,
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**---- Currencies ----*/
    /**
     * Get currencies from Winmax4 API
     *
     * @return object
     * @throws GuzzleException
     */
    public function getCurrencies()
    {
        $response = $this->client->get($this->url . '/Files/Currencies', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**---- Document Types ----*/
    /**
     * Get document types from Winmax4 API
     *
     * @return object
     * @throws GuzzleException
     */
    public function getDocumentTypes()
    {
        $response = $this->client->get($this->url . '/Files/DocumentTypes', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**---- Families and SubFamilies ----*/
    /**
     * Get families from Winmax4 API
     *
     * @return object
     * @throws GuzzleException
     */
    public function getFamilies()
    {
        $response = $this->client->get($this->url . '/Files/Families?IncludeSubFamilies=true', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get sub families from Winmax4 API
     *
     * @param $family_id
     * @return object
     * @throws GuzzleException
     */
    public function getTaxes()
    {
        $response = $this->client->get($this->url . '/Files/Taxes', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

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



    /**---- Warehouses ----*/
    /**
     * Get warehouses from Winmax4 API
     *
     * @return object
     * @throws GuzzleException
     */
    public function getWarehouses(){
        $response = $this->client->get($this->url . '/Files/Warehouses', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }
}
