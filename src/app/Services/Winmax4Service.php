<?php

namespace Controlink\LaravelWinmax4\app\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Winmax4Service
{
    protected $client;
    protected $settings;

    public function __construct()
    {
        $this->client = new Client();
        $this->settings = config('winmax4');
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
}
