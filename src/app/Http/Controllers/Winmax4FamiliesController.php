<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubFamily;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;

class Winmax4FamiliesController extends Controller
{
    protected $winmax4Service;

    /**
     * Winmax4Controller constructor.
     *
     */
    public function __construct()
    {
        $winmaxSettings = Winmax4Setting::where(config('winmax4.license_column'), session(config('winmax4.license_session_key')))->first();

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
     * @param $family_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubFamilies($family_code){
        $family = Winmax4Family::with('subFamilies')->where('code', $family_code)->first();
        return response()->json($family->subFamilies, 200);
    }

    /**
     * Get sub sub families from Winmax4 API
     * @param $sub_family_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubSubFamilies($sub_family_code){
        $sub_family = Winmax4SubFamily::with('subSubFamilies')->where('code', $sub_family_code)->first();
        return response()->json($sub_family->subSubFamilies, 200);
    }
}
