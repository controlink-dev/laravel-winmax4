<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Console\Command;

class syncCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-currencies
                            {--license_id=? : If you want to sync currencies for a specific license, specify the license id.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync currencies from Winmax4 API to the database.';

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
            $this->info('Syncing currencies for license id ' . $license_id . '...');
            $winmax4Settings = Winmax4Setting::where(config('winmax4.license_column'), $license_id)->get();
        } else {
            $this->info('Syncing currencies for all licenses...');
            $winmax4Settings = Winmax4Setting::get();
        }

        foreach ($winmax4Settings as $winmax4Setting) {
            $this->info('Syncing currencies for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4Service(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal
            );

            $currencies = $winmax4Service->getCurrencies()->Data->Currencies;

            foreach ($currencies as $currency) {
                if(config('winmax4.use_license')){
                    Winmax4Currency::updateOrCreate(
                        [
                            'code' => $currency->Code,
                            config('winmax4.license_column') => $winmax4Setting->license_id,
                        ],
                        [
                            'designation' => $currency->Designation,
                            'is_active' => $currency->IsActive,
                            'article_decimals' => $currency->ArticleDecimals,
                            'document_decimals' => $currency->DocumentDecimals,
                        ]
                    );
                }else{
                    Winmax4Currency::updateOrCreate(
                        [
                            'code' => $currency->Code,
                        ],
                        [
                            'designation' => $currency->Designation,
                            'is_active' => $currency->IsActive,
                            'article_decimals' => $currency->ArticleDecimals,
                            'document_decimals' => $currency->DocumentDecimals,
                        ]
                    );
                }
            }

            if(config('winmax4.use_license')){
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4Currency::class, $winmax4Setting->license_id);
            }else{
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4Currency::class);
            }
        }

    }
}
