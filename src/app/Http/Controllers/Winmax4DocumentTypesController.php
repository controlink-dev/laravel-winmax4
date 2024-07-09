<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;

abstract class Winmax4DocumentTypesController
{
    protected $winmax4Service;

    /**
     * Winmax4Controller constructor.
     *
     */
    public function __construct()
    {
        $winmaxSettings = Winmax4Setting::where(config('winmax4.license_column'), session('licenseID'))->first();

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
     * Get document types from Winmax4 API
     */
    public function getDocumentTypes(){
        return response()->json(Winmax4DocumentType::get(), 200);
    }
}
