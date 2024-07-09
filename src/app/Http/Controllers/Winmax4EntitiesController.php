<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Illuminate\Http\Request;

abstract class Winmax4EntitiesController
{
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
}
