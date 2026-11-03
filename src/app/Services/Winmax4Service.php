<?php

namespace Controlink\LaravelWinmax4\app\Services;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4RequestLog;
use Controlink\LaravelWinmax4\app\Models\Winmax4SyncErrors;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Create;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;


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
                            return $this->handleNon200Response($response, $license_id);
                        }
                        return $response;
                    }
                );
            };
        });
        // Pushed last so it sits next to the HTTP handler: logs the request exactly as sent and the raw response.
        if (config('winmax4.log_requests')) {
            $stack->push($this->logRequests($license_id));
        }

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
    public function generateToken(string $company_code, string $username, string $password, string $n_terminal, string $url = '')
    {
        $tokenIsEmpty = (object) [
            'Data' => (object) ['AccessToken' => (object) ['Value' => '']],
        ];

        try {

            $response = $this->client->post($url . 'Account/GenerateToken', [
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

            return $tokenIsEmpty;
        }

        $token = json_decode($response->getBody()->getContents());

        return isset($token->Data->AccessToken->Value) ? $token : $tokenIsEmpty;
    }

    /**
     * Guzzle middleware that stores every request to Winmax4 and its response (or connection error)
     * in winmax4_request_logs. The password and the bearer token are masked; a failure to write
     * the log is reported and never breaks the call to Winmax4.
     */
    protected function logRequests($license_id): callable
    {
        return function (callable $handler) use ($license_id) {
            return function (RequestInterface $request, array $options) use ($handler, $license_id) {
                $startedAt = microtime(true);

                return $handler($request, $options)->then(
                    function ($response) use ($request, $startedAt, $license_id) {
                        $this->storeRequestLog($request, $startedAt, $license_id, $response);

                        return $response;
                    },
                    function ($reason) use ($request, $startedAt, $license_id) {
                        $this->storeRequestLog($request, $startedAt, $license_id, null, $reason);

                        return Create::rejectionFor($reason);
                    }
                );
            };
        };
    }

    private function storeRequestLog(RequestInterface $request, float $startedAt, $license_id, ?ResponseInterface $response = null, $reason = null): void
    {
        try {
            $headers = $request->getHeaders();
            if (isset($headers['Authorization'])) {
                $headers['Authorization'] = ['Bearer ***'];
            }

            $requestBody = $this->readBody($request);
            $decoded = json_decode((string) $requestBody, true);
            if (is_array($decoded) && array_key_exists('Password', $decoded)) {
                $decoded['Password'] = '***';
                $requestBody = json_encode($decoded);
            }

            $responseBody = $response ? $this->readBody($response) : null;
            $decoded = json_decode((string) $responseBody, true);
            foreach (['AccessToken', 'RefreshToken'] as $token) {
                if (isset($decoded['Data'][$token]['Value'])) {
                    $decoded['Data'][$token]['Value'] = '***';
                    $responseBody = json_encode($decoded);
                }
            }

            $attributes = [
                'method' => $request->getMethod(),
                'url' => (string) $request->getUri(),
                'request_headers' => $headers,
                'request_body' => $requestBody,
                'status_code' => $response?->getStatusCode(),
                'response_headers' => $response?->getHeaders(),
                'response_body' => $responseBody,
                'error' => $reason instanceof \Throwable ? get_class($reason) . ': ' . $reason->getMessage() : ($reason === null ? null : (string) $reason),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ];

            if (config('winmax4.use_license')) {
                $attributes[config('winmax4.license_column')] = session('licenseID') ?? ($license_id ?: null);
            }

            Winmax4RequestLog::create($attributes);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Reads the whole body and rewinds it, so the code that consumes the response afterwards still sees it all.
     */
    private function readBody(MessageInterface $message): ?string
    {
        $body = $message->getBody();

        if (!$body->isSeekable()) {
            return null; // shortcut: streamed bodies can't be re-read, Winmax4 never sends them
        }

        $contents = (string) $body;
        $body->rewind();

        return $contents === '' ? null : $contents;
    }

    /**
     * Handle non-200 responses from the Winmax4 API
     *
     * @param $response
     * @return ResponseInterface
     */
    private function handleNon200Response($response, $license_id): Response|ResponseInterface
    {
        // Handle the non-200 response here,
        // For example, you can log the error or throw an exception
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        if ($statusCode == 401) {
            $errorMsg = 'Unauthorized access to Winmax4 API. Please check your credentials.';
            $this->logSyncError($errorMsg, $license_id);
        }else if($statusCode == 404){
            $errorMsg = 'Data not found.';
            $this->logSyncError($errorMsg, $license_id);
        }
        else
        {
            $bodyDecoded = json_decode($body, true);
            $errorMsg = $this->renderErrorMessage($body);
            $this->logSyncError("Error {$statusCode} while accessing Winmax4 API: {$bodyDecoded['Results'][0]['Code']} - {$errorMsg}", $license_id);
        }

        if(isset($bodyDecoded) && isset($bodyDecoded['Results'])){
            $status = $bodyDecoded['Results'][0]['Code'];
        }else{
            $status = 'API_ERROR';
        }


        return new Response(
            400,
            ['Content-Type' => 'application/json'],
            json_encode([
                'error' => true,
                'status' => $status,
                'message' => $errorMsg,
            ])
        );
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
        $this->logSyncError('Connection error: ' . $exception->getMessage());

        return [
            'error' => true,
            'status' => 'CONNECTION_ERROR',
            'message' => 'Connection error: ' . $exception->getMessage(),
        ];
    }

    /**
     * Log a sync error, only attaching the license column when the package
     * is configured to use licenses — the column doesn't exist otherwise.
     *
     * @param string $message
     * @param mixed $license_id
     * @return void
     */
    private function logSyncError(string $message, $license_id = null): void
    {
        $attributes = ['message' => $message];

        if (config('winmax4.use_license')) {
            $attributes[config('winmax4.license_column')] = session('licenseID') ?? $license_id;
        }

        Winmax4SyncErrors::create($attributes);
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

        $errorJsonArray = json_decode($errorJson, true);

        switch ($errorJsonArray['Results'][0]['Code']) {
            case 'ARTICLECODEINUSE':
                $errorJsonArray['Results'][0]['Message'] = 'Article code is already in use';
                break;
            case 'ENTITYCODEINUSE':
                $errorJsonArray['Results'][0]['Message'] = 'Entity code is already in use';
                break;
            case 'REQUIREDFIELDSAREMISSING':
                $errorJsonArray['Results'][0]['Message'] = 'Required fields are missing';
                break;
            case 'ARTICLEDESIGNATIONCANTBECHANGED':
                $errorJsonArray['Results'][0]['Message'] = 'Article designation cannot be changed';
                break;
            case 'DUPLICATEARTICLESALETAXFEES':
                $errorJsonArray['Results'][0]['Message'] = 'Duplicate article sale tax fees';
                break;
            case 'DUPLICATEARTICLEPURCHASETAXFEES':
                $errorJsonArray['Results'][0]['Message'] = 'Duplicate article purchase tax fees';
                break;
            case 'TAXFEECODENOTFOUND':
                $errorJsonArray['Results'][0]['Message'] = 'Tax fee code not found';
                break;
            case 'CURRENCYNOTFOUND':
                $errorJsonArray['Results'][0]['Message'] = 'Currency not found';
                break;
            case 'DUPLICATEARTICLEPRICECURRENCY':
                $errorJsonArray['Results'][0]['Message'] = 'Duplicate article price currency';
                break;
            case 'COULDNTCREATEDOCUMENT':
                $errorJsonArray['Results'][0]['Message'] = 'Could not create the document';
                break;
            case 'InvalidArticleCode':
                $errorJsonArray['Results'][0]['Message'] = 'The article code is invalid';
                break;
            case 'ArticleIsNotActive':
                $errorJsonArray['Results'][0]['Message'] = 'The article is not active';
                break;
            case 'InvalidArticleType':
                $errorJsonArray['Results'][0]['Message'] = 'The article type is invalid';
                break;
            case 'InvalidUnit':
                $errorJsonArray['Results'][0]['Message'] = 'The unit is invalid';
                break;
            case 'InvalidTax':
                $errorJsonArray['Results'][0]['Message'] = 'The tax is invalid';
                break;
            case 'OutdatedBatch':
                $errorJsonArray['Results'][0]['Message'] = 'The batch is outdated';
                break;
            case 'InvalidBatch':
                $errorJsonArray['Results'][0]['Message'] = 'The batch is invalid';
                break;
            case 'ArticleWithSameSerialNumberInDocument':
                $errorJsonArray['Results'][0]['Message'] = 'The article with the same serial number is already in the document';
                break;
            case 'InvalidComposition':
                $errorJsonArray['Results'][0]['Message'] = 'The composition is invalid';
                break;
            case 'InvalidEntityCode':
                $errorJsonArray['Results'][0]['Message'] = 'The entity code is invalid';
                break;
            case 'ArticleNotAvailableForCurrentServiceZone':
                $errorJsonArray['Results'][0]['Message'] = 'The article is not available for the current service zone';
                break;
            case 'TotalIsNegative':
                $errorJsonArray['Results'][0]['Message'] = 'The total is negative';
                break;
            case 'UnitRequiresEDICode':
                $errorJsonArray['Results'][0]['Message'] = 'The unit requires an EDI code';
                break;
            case 'TaxRequiresEDICode':
                $errorJsonArray['Results'][0]['Message'] = 'The tax requires an EDI code';
                break;
            case 'AlreadyInDocument':
                $errorJsonArray['Results'][0]['Message'] = 'The article is already in the document';
                break;
            case 'InvalidTaxes':
                $errorJsonArray['Results'][0]['Message'] = 'The taxes are invalid';
                break;
            case 'NotEnoughStock':
                $errorJsonArray['Results'][0]['Message'] = 'There is not enough stock';
                break;
            case 'QuantityZero':
                $errorJsonArray['Results'][0]['Message'] = 'The quantity is zero';
                break;
            case 'SkipToNextDetail':
                $errorJsonArray['Results'][0]['Message'] = 'Skip to the next detail';
                break;
            case 'InvalidEntityInDetail':
                $errorJsonArray['Results'][0]['Message'] = 'The entity in the detail is invalid';
                break;
            case 'NoTaxesDefined':
                $errorJsonArray['Results'][0]['Message'] = 'No taxes are defined';
                break;
            case 'OnlyOnePercentageTaxAllowed':
                $errorJsonArray['Results'][0]['Message'] = 'Only one percentage tax is allowed';
                break;
            case 'NotAllowedOtherTaxesOverPercentageTax':
                $errorJsonArray['Results'][0]['Message'] = 'Not allowed other taxes over the percentage tax';
                break;
            case 'TaxRateDoesntHaveSAFTDesignation':
                $errorJsonArray['Results'][0]['Message'] = 'The tax rate does not have a SAFT designation';
                break;
            case 'EXCEPTION':
                $errorJsonArray['Results'][0]['Message'] = 'An exception occurred! Please contact the administrator';
                break;
            default:
                $errorJsonArray['Results'][0]['Message'] = 'An unknown error occurred! Please contact the administrator';
                break;
        }

        return $errorJsonArray['Results'][0]['Message'];
    }
}
