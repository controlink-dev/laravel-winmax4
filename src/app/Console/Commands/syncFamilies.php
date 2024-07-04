<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Controlink\LaravelWinmax4\app\Jobs\SyncFamiliesJob;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class syncFamilies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-families';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync families from Winmax4 API to the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $winmax4Settings = Winmax4Setting::get();

        foreach ($winmax4Settings as $winmax4Setting) {
            $this->info('Syncing families  for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4Service(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal
            );

            $families = $winmax4Service->getFamilies()->Data->Families;

            $job = [];
            foreach ($families as $family) {
                $job[] = new SyncFamiliesJob($family, $winmax4Setting->license_id);
            }

            $batch = Bus::batch([])->finally(function (Batch $batch){
                $batch->delete();
            })->name('winmax4_families')->onQueue(config('winmax4.queue'))->dispatch();

            $chunks = array_chunk($job, 100);

            foreach ($chunks as $chunk){
                $batch->add($chunk);
            }

        }
    }
}
