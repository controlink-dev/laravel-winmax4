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

    public function getCompanyInfo()
    {
        $response = $this->client->get($this->url . '/Company/GetCompanyInfo', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Token,
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }
}
