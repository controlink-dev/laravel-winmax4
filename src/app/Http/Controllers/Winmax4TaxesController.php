<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Tax;

abstract class Winmax4TaxesController
{
    /**
     * Get taxes from Winmax4 API
     */
    public function getTaxes(){
        return response()->json(Winmax4Tax::with('taxRates')->get(), 200);
    }
}
