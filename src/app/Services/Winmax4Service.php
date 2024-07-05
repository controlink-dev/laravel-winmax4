<?php

namespace Controlink\LaravelWinmax4\app\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Winmax4Service
{
    protected $client;
    protected $url;
    protected $settings;
    protected $token;

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

    public function getArticles()
    {
        $response = $this->client->get($this->url . '/Files/Articles?IncludeCategories=true?IncludeExtras=true?IncludeHolds=true?IncludeDescriptives=true?IncludeQuestions=true', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

}
