<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Settings;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Http\Request;

class Winmax4Controller extends Controller
{
    protected $winmax4Service;

    public function __construct(Winmax4Service $winmax4Service)
    {
        $this->winmax4Service = $winmax4Service;
    }

    /**
     * Authenticate to Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function generateToken(Request $request)
    {
        $validatedData = $request->validate([
            'url' => 'required',
            'company_code' => 'required',
            'username' => 'required',
            'password' => 'required',
            'n_terminal' => 'required',
        ]);

        $url = $validatedData['url'];
        $company_code = $validatedData['company_code'];
        $username = $validatedData['username'];
        $password = $validatedData['password'];
        $n_terminal = $validatedData['n_terminal'];

        $response = $this->winmax4Service->authenticate($url, $company_code, $username, $password, $n_terminal);

        if ($response->Results[0]->Code === 'OK') {
            $winmax4 = new Winmax4Settings();
            $winmax4->url = $url;
            $winmax4->company_code = $company_code;
            $winmax4->username = $username;
            $winmax4->password = $password;
            $winmax4->n_terminal = $n_terminal;

            if (config('winmax4.use_license')) {
                $winmax4->{config('winmax4.license_column')} = $request->sessionID;
            }

            $winmax4->save();

            return response()->json([
                'message' => 'Success',
                'data' => $response->Results[0]->Message,
            ], 201);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->Results[0]->Message,
            ], 400);
        }
    }
}
