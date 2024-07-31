<?php

namespace Controlink\LaravelWinmax4\app\Services;

use GuzzleHttp\Exception\GuzzleException;

class Winmax4DocumentTypesServices extends Winmax4Service
{
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
}