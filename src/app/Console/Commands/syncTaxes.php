<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4Tax;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Controlink\LaravelWinmax4\app\Jobs\SyncFamiliesJob;
use Controlink\LaravelWinmax4\app\Services\Winmax4TaxService;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class syncTaxes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-taxes
                            {--license_id= : If you want to sync taxes for a specific license, specify the license id.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync taxes from Winmax4 API to the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $license_id = null;
        if(config('winmax4.use_license')){
            if($this->option('license_id') != null){
                // If the license_id option is set, use it
                $license_id = $this->option('license_id');
            }
        }

        if (!config('winmax4.use_license') && $this->option('license_id') != null) {
            $this->error('You cannot specify a license id if you are not using the use_license configuration.');
            return;
        }

        if ($license_id != null) {
            $this->info('Syncing taxes for license id ' . $license_id . '...');
            $winmax4Settings = Winmax4Setting::where(config('winmax4.license_column'), $license_id)->get();
        } else {
            $this->info('Syncing taxes for all licenses...');
            $winmax4Settings = Winmax4Setting::get();
        }

        foreach ($winmax4Settings as $winmax4Setting) {
            if(!$winmax4Setting->tenant){
                continue;
            }

            $this->info('Syncing taxes  for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4TaxService(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal,
                $winmax4Setting->license_id
            );

            $taxes = $winmax4Service->getTaxes()->Data->Taxes;

            foreach ($taxes as $tax) {
                if(config('winmax4.use_license')){
                    $newTax = Winmax4Tax::updateOrCreate(
                        [
                            'code' => $tax->Code,
                            config('winmax4.license_column') => $winmax4Setting->license_id,
                        ],
                        [
                            'designation' => $tax->Designation,
                            'is_active' => $tax->IsActive,
                        ]
                    );
                }else{
                    $newTax = Winmax4Tax::updateOrCreate(
                        [
                            'code' => $tax->Code,
                        ],
                        [
                            'designation' => $tax->Designation,
                            'is_active' => $tax->IsActive,
                        ]
                    );
                }

                if ($tax->Rates) {
                    foreach ($tax->Rates as $rate) {
                        $newTax->taxRates()->updateOrCreate(
                            [
                                'tax_id' => $newTax->id,
                                'fixedAmount' => $rate->FixedAmount,
                                'percentage' => $rate->Percentage,
                            ],
                            [
                                'fixedAmount' => $rate->FixedAmount,
                                'percentage' => $rate->Percentage,
                            ]
                        );
                    }
                }
            }

            if(config('winmax4.use_license')){
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4Tax::class, $winmax4Setting->license_id);
            }else{
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4Tax::class);
            }

        }
    }
}
