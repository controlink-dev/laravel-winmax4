<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
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
        $winmax4Settings = (new Winmax4Controller)->getWinmax4Settings();
        dd($winmax4Settings);
        $winmax4Service = new Winmax4Service(
            false,
            $winmax4Settings->url,
            $winmax4Settings->company_code,
            $winmax4Settings->username,
            $winmax4Settings->password,
            $winmax4Settings->n_terminal
        );
    }
}
