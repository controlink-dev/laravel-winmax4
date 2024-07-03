<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Console\Command;

class SyncCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-currencies';

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
        $winmax4Settings = Winmax4Setting::get();

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
                // Save currency to the database
                 Winmax4Currency::updateOrCreate(
                    [
                        'code' => $currency->Code
                    ],
                    [
                        'license_id' => $winmax4Setting->license_id,
                        'designation' => $currency->Designation,
                        'is_active' => $currency->IsActive,
                        'article_decimals' => $currency->ArticleDecimals,
                        'document_decimals' => $currency->DocumentDecimals,
                    ]
                );
            }
        }

    }
}
