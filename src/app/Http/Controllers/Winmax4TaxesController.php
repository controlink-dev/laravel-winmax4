<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4Tax;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;

class Winmax4TaxesController extends Controller
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
     * Get taxes from Winmax4 API
     */
    public function getTaxes(){

        $taxes = Winmax4Tax::with('taxRates')->get()->map(function ($tax){

            $value = 0;
            $is_percentage = true;
            $rates = [];

            foreach($tax->taxRates as $rate){
                $is_percentage = true;
                if($rate->percentage == 0){
                    if($rate->fixedAmount != 0){
                        $value = $rate->fixedAmount;
                        $is_percentage = false;
                    }else{
                        $value = 0;
                    }
                }else{
                    $value = $rate->percentage;
                }


                $rates[] = [
                    'is_percentage' => $is_percentage,
                    'tax_rate' => $value,
                ];
            }

            return [
                'tax_code' => $tax->code,
                'tax_name' => $tax->designation,
                'is_active' => $tax->is_active,
                'tax_rates' => $rates,
            ];
        });

        return response()->json($taxes, 200);
    }
}
