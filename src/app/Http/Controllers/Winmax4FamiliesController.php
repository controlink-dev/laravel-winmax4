<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubFamily;

abstract class Winmax4FamiliesController
{
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
