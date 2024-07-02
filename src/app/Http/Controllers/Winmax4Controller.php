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
        $response = $client->request('POST', $url . '/Account/Authenticate', [
            'json' => [
                'company_code' => $company_code,
                'username' => $username,
                'password' => $password,
                'n_terminal' => $n_terminal,
            ]
        ]);

        return response()->json(json_decode($response->getBody()->getContents()));
    }
}
