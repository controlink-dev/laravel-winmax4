<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4FamilyService;
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
    protected $signature = 'winmax4:sync-families
                            {--license_id= : If you want to sync families for a specific license, specify the license id.}';

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
            $this->info('Syncing families for license id ' . $license_id . '...');
            $winmax4Settings = Winmax4Setting::where(config('winmax4.license_column'), $license_id)->get();
        } else {
            $this->info('Syncing families for all licenses...');
            $winmax4Settings = Winmax4Setting::get();
        }

        foreach ($winmax4Settings as $winmax4Setting) {
            $this->info('Syncing families  for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4FamilyService(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal
            );

            if(config('winmax4.use_license')){
                //If the license_id option is set, get all families by license_id
                $localFamilies = Winmax4Family::where('license_id', $winmax4Setting->license_id)->get();
            }else{
                //If the license_id option is not set, get all families
                $localFamilies = Winmax4Family::get();
            }

            // Get all families from Winmax4
            $families = $winmax4Service->getFamilies()->Data->Families;

            //Check if the families is_active status has changed
            foreach ($families as $family) {
                foreach ($localFamilies as $localFamily) {

                    //Check if the family is_active status has changed
                    if ($localFamily->is_active != $family->IsActive) {

                        //Update the local family
                        $localFamily->is_active = $family->IsActive ?? false;
                        $localFamily->save();
                    }
                }
            }

            $job = [];
            foreach ($families as $family) {
                if (config('winmax4.use_license')) {
                    $job[] = new SyncFamiliesJob($family, $winmax4Setting->license_id);
                }else{
                    $job[] = new SyncFamiliesJob($family);
                }

            }

            $batch = Bus::batch([])->then(function (Batch $batch) use ($winmax4Setting) {
                if(config('winmax4.use_license')){
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Family::class, $winmax4Setting->license_id);
                }else{
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Family::class);
                }

                $batch->delete();
            })->name('winmax4_families')->onQueue(config('winmax4.queue'))->dispatch();

            $chunks = array_chunk($job, 100);

            foreach ($chunks as $chunk){
                $batch->add($chunk);
            }

        }
    }
}
