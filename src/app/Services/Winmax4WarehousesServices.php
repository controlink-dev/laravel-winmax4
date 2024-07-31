<?php

namespace Controlink\LaravelWinmax4\app\Services;

use GuzzleHttp\Exception\GuzzleException;

class Winmax4WarehousesServices extends Winmax4Service
{
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