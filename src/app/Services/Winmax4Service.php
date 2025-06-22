<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4SyncErrors;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;

class Winmax4Service
{
    protected $client;
    protected $token;

    const WINMAX4_RESPONSE_OK = 'OK';
    const WINMAX4_RESPONSE_EXCEPTION = 'EXCEPTION';

    public function __construct($saveMode = false, $url = '', $company_code = '', $username = '', $password = '', $n_terminal = '', $license_id = '')
    {
        $stack = HandlerStack::create();
        $stack->push(function (callable $handler) use ($license_id) {
            return function ($request, array $options) use ($license_id, $handler) {
                return $handler($request, $options)->then(
                    function ($response) use ($license_id) {
                        if ($response->getStatusCode() !== 200) {
                            // Call your custom handler
                            $this->handleNon200Response($response, $license_id);
                        }
                        return $response;
                    }
                );
            };
        });

        //Check if $url ends with a / if not add one
        if (!empty($url) && !str_ends_with($url, '/')) {
            $url .= '/';
        }

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
     * @return object | array
     */
    public function generateToken(string $company_code, string $username, string $password, string $n_terminal)
    {
        try {
            $response = $this->client->post('Account/GenerateToken', [
                'json' => [
                    'Company' => $company_code,
                    'UserLogin' => $username,
                    'Password' => $password,
                    'TerminalCode' => $n_terminal,
                ],
            ]);
        } catch (ConnectException $e) {
            // Handle timeouts, connection failures, DNS errors, etc.
            return $this->handleConnectionError($e);
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Handle non-200 responses from the Winmax4 API
     *
     * @param $response
     * @return array
     */
    private function handleNon200Response($response, $license_id): array
    {
        // Handle the non-200 response here,
        // For example, you can log the error or throw an exception
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        if ($statusCode == 401) {
            $errorMsg = 'Unauthorized access to Winmax4 API. Please check your credentials.';
            Winmax4SyncErrors::create([
                'message' => $errorMsg,
                config('winmax4.license_column') => session('licenseID') ?? $license_id,
            ]);
        }
        if($statusCode == 404){
            $errorMsg = 'Data not found.';
            Winmax4SyncErrors::create([
                'message' => $errorMsg,
                config('winmax4.license_column') => session('licenseID')  ?? $license_id,
            ]);
        }
        else
        {
            $errorMsg = $this->renderErrorMessage($body);
            Winmax4SyncErrors::create([
                'message' => "Error {$statusCode} while accessing Winmax4 API: {$body->Results['Code']} - {$errorMsg}",
                config('winmax4.license_column') => session('licenseID')
            ]);
        }

        return [
            'error' => true,
            'status' => $body?->Results['Code'] ?? 'API_ERROR',
            'message' => $errorMsg,
        ];
    }

    /**
     * Handle connection errors
     *
     * @param $exception
     * @return array
     */
    protected function handleConnectionError($exception): array
    {
        // Handle connection errors here
        Winmax4SyncErrors::create([
            'message' => 'Connection error: ' . $exception->getMessage(),
            config('winmax4.license_column') => session('licenseID')
        ]);

        return [
            'error' => true,
            'status' => 'CONNECTION_ERROR',
            'message' => 'Connection error: ' . $exception->getMessage(),
        ];
    }

    /**
     * Render an error message more user-friendly
     *
     * @param string $errorJson
     * @return string
     */
    private function renderErrorMessage($errorJson): string
    {
        if($errorJson == null){
            return 'Data not found';
        }

        switch ($errorJson['Results'][0]['Code']) {
            case 'ARTICLECODEINUSE':
                $errorJson['Results'][0]['Message'] = 'Article code is already in use';
                break;
            case 'ENTITYCODEINUSE':
                $errorJson['Results'][0]['Message'] = 'Entity code is already in use';
                break;
            case 'REQUIREDFIELDSAREMISSING':
                $errorJson['Results'][0]['Message'] = 'Required fields are missing';
                break;
            case 'ARTICLEDESIGNATIONCANTBECHANGED':
                $errorJson['Results'][0]['Message'] = 'Article designation cannot be changed';
                break;
            case 'DUPLICATEARTICLESALETAXFEES':
                $errorJson['Results'][0]['Message'] = 'Duplicate article sale tax fees';
                break;
            case 'DUPLICATEARTICLEPURCHASETAXFEES':
                $errorJson['Results'][0]['Message'] = 'Duplicate article purchase tax fees';
                break;
            case 'TAXFEECODENOTFOUND':
                $errorJson['Results'][0]['Message'] = 'Tax fee code not found';
                break;
            case 'CURRENCYNOTFOUND':
                $errorJson['Results'][0]['Message'] = 'Currency not found';
                break;
            case 'DUPLICATEARTICLEPRICECURRENCY':
                $errorJson['Results'][0]['Message'] = 'Duplicate article price currency';
                break;
            case 'COULDNTCREATEDOCUMENT':
                $errorJson['Results'][0]['Message'] = 'Could not create the document';
                break;
            case 'InvalidArticleCode':
                $errorJson['Results'][0]['Message'] = 'The article code is invalid';
                break;
            case 'ArticleIsNotActive':
                $errorJson['Results'][0]['Message'] = 'The article is not active';
                break;
            case 'InvalidArticleType':
                $errorJson['Results'][0]['Message'] = 'The article type is invalid';
                break;
            case 'InvalidUnit':
                $errorJson['Results'][0]['Message'] = 'The unit is invalid';
                break;
            case 'InvalidTax':
                $errorJson['Results'][0]['Message'] = 'The tax is invalid';
                break;
            case 'OutdatedBatch':
                $errorJson['Results'][0]['Message'] = 'The batch is outdated';
                break;
            case 'InvalidBatch':
                $errorJson['Results'][0]['Message'] = 'The batch is invalid';
                break;
            case 'ArticleWithSameSerialNumberInDocument':
                $errorJson['Results'][0]['Message'] = 'The article with the same serial number is already in the document';
                break;
            case 'InvalidComposition':
                $errorJson['Results'][0]['Message'] = 'The composition is invalid';
                break;
            case 'InvalidEntityCode':
                $errorJson['Results'][0]['Message'] = 'The entity code is invalid';
                break;
            case 'ArticleNotAvailableForCurrentServiceZone':
                $errorJson['Results'][0]['Message'] = 'The article is not available for the current service zone';
                break;
            case 'TotalIsNegative':
                $errorJson['Results'][0]['Message'] = 'The total is negative';
                break;
            case 'UnitRequiresEDICode':
                $errorJson['Results'][0]['Message'] = 'The unit requires an EDI code';
                break;
            case 'TaxRequiresEDICode':
                $errorJson['Results'][0]['Message'] = 'The tax requires an EDI code';
                break;
            case 'AlreadyInDocument':
                $errorJson['Results'][0]['Message'] = 'The article is already in the document';
                break;
            case 'InvalidTaxes':
                $errorJson['Results'][0]['Message'] = 'The taxes are invalid';
                break;
            case 'NotEnoughStock':
                $errorJson['Results'][0]['Message'] = 'There is not enough stock';
                break;
            case 'QuantityZero':
                $errorJson['Results'][0]['Message'] = 'The quantity is zero';
                break;
            case 'SkipToNextDetail':
                $errorJson['Results'][0]['Message'] = 'Skip to the next detail';
                break;
            case 'InvalidEntityInDetail':
                $errorJson['Results'][0]['Message'] = 'The entity in the detail is invalid';
                break;
            case 'NoTaxesDefined':
                $errorJson['Results'][0]['Message'] = 'No taxes are defined';
                break;
            case 'OnlyOnePercentageTaxAllowed':
                $errorJson['Results'][0]['Message'] = 'Only one percentage tax is allowed';
                break;
            case 'NotAllowedOtherTaxesOverPercentageTax':
                $errorJson['Results'][0]['Message'] = 'Not allowed other taxes over the percentage tax';
                break;
            case 'TaxRateDoesntHaveSAFTDesignation':
                $errorJson['Results'][0]['Message'] = 'The tax rate does not have a SAFT designation';
                break;
            case 'EXCEPTION':
                $errorJson['Results'][0]['Message'] = 'An exception occurred! Please contact the administrator';
                break;
            default:
                $errorJson['Results'][0]['Message'] = 'An unknown error occurred! Please contact the administrator';
                break;
        }

        return $errorJson['Results'][0]['Message'];
    }
}
