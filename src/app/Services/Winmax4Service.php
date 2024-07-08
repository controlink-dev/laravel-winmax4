<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

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

    /**
     * Get articles from Winmax4 API
     *
     * @return object
     * @throws GuzzleException
     */
    public function getArticles()
    {
        $response = $this->client->get($this->url . '/Files/Articles?IncludeCategories=true&IncludeExtras=true&IncludeHolds=true&IncludeDescriptives=true&IncludeQuestions=true', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get entities from Winmax4 API
     *
     * @return object
     * @throws GuzzleException
     */
    public function getEntities()
    {
        $response = $this->client->get($this->url . '/Files/Entities', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Post entities to Winmax4 API
     *
     * @param $values
     * @return object
     * @throws GuzzleException
     */
    public function postEntities($values)
    {
        $response = $this->client->post($this->url . '/Files/Entities', [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'Code' => $values['code'],
                'Name' => $values['name'],
                'IsActive' => 1,
                'EntityType' => 0,
                'TaxPayerID' => $values['nif'],
                'Address' => $values['address'],
                'ZipCode' => $values['zipCode'],
                'Phone' => $values['phone'],
                'Fax' => null,
                'MobilePhone' => null,
                'Email' => $values['email'],
                'Location' => $values['locality'],
                'Country' => 'PT',
            ],
        ]);

        $entity = json_decode($response->getBody()->getContents());

        Winmax4Entity::create([
            'license_id' => session('licenseID'),
            'id_winmax4' => $entity->Data->Entity->ID,
            'name' => $entity->Data->Entity->Name,
            'address' => $entity->Data->Entity->Address,
            'code' => $entity->Data->Entity->Code,
            'country_code' => $entity->Data->Entity->CountryCode,
            'email' => $entity->Data->Entity->Email,
            'entity_type' => $entity->Data->Entity->EntityType,
            'fax' => $entity->Data->Entity->Fax,
            'is_active' => $entity->Data->Entity->IsActive,
            'location' => $entity->Data->Entity->Location,
            'mobile_phone' => $entity->Data->Entity->MobilePhone,
            'phone' => $entity->Data->Entity->Phone,
            'tax_payer_id' => $entity->Data->Entity->TaxPayerID,
            'zip_code' => $entity->Data->Entity->ZipCode,
        ]);

        return $entity->Data->Entity;
    }

    /**
     * Update entities to Winmax4 API
     *
     * @param $values
     * @return object
     * @throws GuzzleException
     */
    public function putEntities($values)
    {
        $response = $this->client->put($this->url . '/Files/Entities/' . $values['id_winmax4'], [
            'verify' => $this->settings['verify_ssl_guzzle'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'Code' => $values['code'],
                'Name' => $values['name'],
                'IsActive' => 1,
                'EntityType' => 0,
                'TaxPayerID' => $values['nif'],
                'Address' => $values['address'],
                'ZipCode' => $values['zipCode'],
                'Phone' => $values['phone'],
                'Fax' => null,
                'MobilePhone' => null,
                'Email' => $values['email'],
                'Location' => $values['locality'],
                'Country' => 'PT',
            ],
        ]);

        $entity = json_decode($response->getBody()->getContents());

        dd($entity);

        Winmax4Entity::where('code', $values['code'])->update([
            'license_id' => session('licenseID'),
            'name' => $entity->Data->Entity->Name,
            'address' => $entity->Data->Entity->Address,
            'country_code' => $entity->Data->Entity->CountryCode,
            'email' => $entity->Data->Entity->Email,
            'entity_type' => $entity->Data->Entity->EntityType,
            'fax' => $entity->Data->Entity->Fax,
            'is_active' => $entity->Data->Entity->IsActive,
            'location' => $entity->Data->Entity->Location,
            'mobile_phone' => $entity->Data->Entity->MobilePhone,
            'phone' => $entity->Data->Entity->Phone,
            'tax_payer_id' => $entity->Data->Entity->TaxPayerID,
            'zip_code' => $entity->Data->Entity->ZipCode,
        ]);

        return $entity->Data->Entity;
    }
}
