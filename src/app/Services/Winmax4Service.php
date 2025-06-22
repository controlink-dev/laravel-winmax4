<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4SyncErrors;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;

class Winmax4Service
{
    protected $client;
    protected $token;

    public function __construct($saveMode = false, $url = '', $company_code = '', $username = '', $password = '', $n_terminal = '')
    {
        $stack = HandlerStack::create();
        $stack->push(function (callable $handler){
            return function ($request, array $options) use ($handler) {
                return $handler($request, $options)->then(
                    function ($response) {
                        if ($response->getStatusCode() !== 200) {
                            // Call your custom handler
                            $this->handleNon200Response($response);
                        }
                        return $response;
                    }
                );
            };
        });

        $this->client = new Client([
            'base_uri' => $url,
            'timeout' => config('winmax4.timeout_guzzle', 30),
            'connect_timeout' => config('winmax4.connect_timeout_guzzle', 30),
            'http_errors' => false,
            'handler' => $stack,
            'verify' => config('winmax4.verify_ssl_guzzle', true),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        if (!$saveMode) {
            $this->token = $this->generateToken($company_code, $username, $password, $n_terminal);
        }

    }

    /**
     * Authenticate to Winmax4 API
     *
     * @param string $company_code
     * @param string $username
     * @param string $password
     * @param string $n_terminal
     * @return object
     */
    public function generateToken(string $company_code, string $username, string $password, string $n_terminal)
    {
        try {
            $response = $this->client->post('/Account/GenerateToken', [
                'json' => [
                    'Company' => $company_code,
                    'UserLogin' => $username,
                    'Password' => $password,
                    'TerminalCode' => $n_terminal,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            $this->handleConnectionError($e);
            return null;
        }

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
        try {
            $response = $this->client->get('/Files/Currencies', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            $this->handleConnectionError($e);
            return null;
        }

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
        try{
            $response = $this->client->get('/Files/DocumentTypes', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            $this->handleConnectionError($e);
            return null;
        }

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
        try{
            $response = $this->client->get('/Files/Families?IncludeSubFamilies=true', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            $this->handleConnectionError($e);
            return null;
        }

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
        try{
            $response = $this->client->get('/Files/Taxes', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);

        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            $this->handleConnectionError($e);
            return null;
        }

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
        try{
            $response = $this->client->get('/Files/Articles?IncludeCategories=true&IncludeExtras=true&IncludeHolds=true&IncludeDescriptives=true&IncludeQuestions=true', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            $this->handleConnectionError($e);
            return null;
        }

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
        try{
            $response = $this->client->get('/Files/Entities', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            $this->handleConnectionError($e);
            return null;
        }

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
        try{
            $response = $this->client->post('/Files/Entities', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
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
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            $this->handleConnectionError($e);
            return null;
        }


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
        try{
            $response = $this->client->post('/Files/Entities/?id='.$values['id_winmax4'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token->Data->AccessToken->Value,
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
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            $this->handleConnectionError($e);
            return null;
        }

        $entity = json_decode($response->getBody()->getContents());

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

    /**
     * Handle non-200 responses from the Winmax4 API
     *
     * @param $response
     */
    private function handleNon200Response($response)
    {
        // Handle the non-200 response here
        // For example, you can log the error or throw an exception
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();



        if ($statusCode == 401) {
            Winmax4SyncErrors::create([
                'message' => 'Unauthorized access to Winmax4 API. Please check your credentials.',
                config('winmax4.license_column') => session('licenseID')
            ]);
        }
        else
        {
            Winmax4SyncErrors::create([
                'message' => "Error {$statusCode} while accessing Winmax4 API: {$body->Results['Code']} {$body->Results['Message']}",
                config('winmax4.license_column') => session('licenseID')
            ]);
        }
    }

    /**
     * Handle connection errors
     *
     * @param $exception
     */
    private function handleConnectionError($exception)
    {
        // Handle connection errors here
        Winmax4SyncErrors::create([
            'message' => 'Connection error: ' . $exception->getMessage(),
            config('winmax4.license_column') => session('licenseID')
        ]);
    }
}
