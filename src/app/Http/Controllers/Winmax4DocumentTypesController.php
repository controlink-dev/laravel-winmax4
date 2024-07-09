<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;

abstract class Winmax4DocumentTypesController
{
    /**
     * Get document types from Winmax4 API
     */
    public function getDocumentTypes(){
        return response()->json(Winmax4DocumentType::get(), 200);
    }
}
