<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;

abstract class Winmax4CurrenciesController
{
    /**
     * Get currencies from Winmax4 API
     */
    public function getCurrencies(){
        return response()->json(Winmax4Currency::get(), 200);
    }
}
