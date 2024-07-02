<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Settings;
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
        $request->use_license = config('winmax4.use_license');

        $request->validate([
            'url' => 'required',
            'company_code' => 'required',
            'username' => 'required',
            'password' => 'required',
            'n_terminal' => 'required',
            'license_id' => 'required_if:use_license,true',
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

        if($response->Results[0]->Code === 'OK'){
            $winmax4 = new Winmax4Settings();
            $winmax4->url = $url;
            $winmax4->company_code = $company_code;
            $winmax4->username = $username;
            $winmax4->password = $password;
            $winmax4->n_terminal = $n_terminal;

            if(config('winmax4.use_license')){
                $winmax4->{config('winmax4.license_column')} = $request->license_id;
            }

            $winmax4->save();

            return response()->json([
                'message' => 'Success',
                'data' => $response->Results[0]->Message,
            ], 201);

        }else{

            return response()->json([
                'message' => 'Error',
                'error' => $response->Results[0]->Message,
            ], 400);
        }
    }
}
