<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4Tax;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Controlink\LaravelWinmax4\app\Jobs\SyncFamiliesJob;
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
    protected $signature = 'winmax4:sync-taxes';

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
        $winmax4Settings = Winmax4Setting::get();

        foreach ($winmax4Settings as $winmax4Setting) {
            $this->info('Syncing taxes  for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4Service(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal
            );

            $taxes = $winmax4Service->getTaxes()->Data->Taxes;

            foreach ($taxes as $tax) {
                $tax = Winmax4Tax::updateOrCreate(
                    [
                        'code' => $tax->Code,
                    ],
                    [
                        'license_id' => $winmax4Setting->license_id,
                        'designation' => $tax->Designation,
                        'is_active' => $tax->IsActive,
                    ]
                );

                foreach ($tax->Rates as $rate) {
                    $tax->taxRates()->updateOrCreate(
                        [
                            'tax_id' => $tax->id,
                        ],
                        [
                            'fixedAmount' => $rate->FixedAmount,
                            'percentage' => $rate->Percentage,
                        ]
                    );
                }
            }

        }
    }
}