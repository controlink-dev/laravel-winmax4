<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubFamily;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;

abstract class Winmax4FamiliesController
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
     * Get families from Winmax4 API
     */
    public function getFamilies(){
        return response()->json(Winmax4Family::with('subFamilies.subSubFamilies')->get(), 200);
    }

    /**
     * Get sub families from Winmax4 API
     * @param $family_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubFamilies($family_id){
        return response()->json(Winmax4Family::find($family_id)->subFamilies, 200);
    }

    /**
     * Get sub sub families from Winmax4 API
     * @param $sub_family_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubSubFamilies($sub_family_id){
        return response()->json(Winmax4SubFamily::find($sub_family_id)->subSubFamilies, 200);
    }
}
