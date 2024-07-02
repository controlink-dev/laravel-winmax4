<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Illuminate\Http\Request;

class Winmax4Controller extends Controller
{
    /**
     * Authenticate to Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'url' => 'required',
            'company_code' => 'required',
            'username' => 'required',
            'password' => 'required',
            'n_terminal' => 'required',
        ]);

        $url = $request->url;
        $company_code = $request->company_code;
        $username = $request->username;
        $password = $request->password;
        $n_terminal = $request->n_terminal;

        $client = new \GuzzleHttp\Client();
        $response = $client->post($url . '/Account/GenerateToken', [
            'verify' => config('winmax4.verify_ssl_guzzle'),
            'json' => [
                'Company' => $company_code,
                'UserLogin' => $username,
                'Password' => $password,
                'TerminalCode' => $n_terminal,
            ],
        ]);

        $response = json_decode($response->getBody()->getContents());

        dd($response);
    }
}
