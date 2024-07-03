<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Settings;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Http\Request;

class Winmax4Controller extends Controller
{
    protected $winmax4Service;

    /**
     * Winmax4Controller constructor.
     *
     */
    public function __construct()
    {
        $winmaxSettings = Winmax4Settings::where(config('winmax4.license_column'), session('licenseID'))->first();

        if(!$winmaxSettings) {
            $this->winmax4Service = new Winmax4Service(true);
        }else{
            $this->winmax4Service = new Winmax4Service(
                false,
                $winmaxSettings->url,
                $winmaxSettings->company_code,
                $winmaxSettings->username,
                $winmaxSettings->password,
                $winmaxSettings->n_terminal
            );
        }
    }

    /**
     * Get Winmax4 settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWinmax4Settings()
    {
        $winmax4 = Winmax4Settings::where(config('winmax4.license_column'), session('licenseID'))->first();

        if ($winmax4) {
            return response()->json([
                'message' => 'Success',
                'winmax4' => $winmax4,
            ], 200);
        }
    }

    /**
     * Generate token for Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function generateToken(Request $request)
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

        $response = $this->winmax4Service->generateToken($url, $company_code, $username, $password, $n_terminal);

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

    /**
     * Get currencies from Winmax4 API
     */
    public function getCurrencies(){
        $response = $this->winmax4Service->getCurrencies();

        if ($response->Results[0]->Code === 'OK') {
            return response()->json([
                'message' => 'Success',
                'data' => $response->Data->Currencies,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->Results[0]->Message,
            ], 400);
        }
    }

}
