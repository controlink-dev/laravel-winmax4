<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Http\Request;

class Winmax4EntitiesController extends Controller
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
     * Get entities from Winmax4 API
     */
    public function getEntities(){
        return response()->json(Winmax4Entity::get(), 200);
    }

    /**
     * Post entities to Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEntities(Request $request)
    {
        return response()->json($this->winmax4Service->postEntities($request->all()), 200);
    }

    /**
     * Put entities to Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function putEntities(Request $request)
    {
        return response()->json($this->winmax4Service->putEntities($request->all()), 200);
    }

    /**
     * Delete entities from Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEntities($id)
    {
        dd($id);
        return response()->json($this->winmax4Service->deleteEntities($id), 200);
    }
}
