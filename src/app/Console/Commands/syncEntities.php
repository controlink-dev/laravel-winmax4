<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Jobs\SyncEntitiesJob;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class syncEntities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-entities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync entities from Winmax4 API to the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $winmax4Settings = Winmax4Setting::get();

        foreach ($winmax4Settings as $winmax4Setting) {
            $this->info('Syncing entities  for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4Service(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal
            );

            $localEntities = Winmax4Entity::where('license_id', $winmax4Setting->license_id)->get();

            $entities = $winmax4Service->getEntities()->Data->Entities;

            //Delete all local entities that don't exist in Winmax4
            foreach ($localEntities as $localEntity) {
                $found = false;
                foreach ($entities as $entity) {
                    if ($localEntity->id_winmax4 == $entity->ID) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $localEntity->delete();
                }
            }

            $job = [];
            foreach ($entities as $entity) {
                if(config('winmax4.use_license')){
                    $job[] = new SyncEntitiesJob($entity, $winmax4Setting->license_id);
                }else{
                    $job[] = new SyncEntitiesJob($entity);
                }
            }

            $batch = Bus::batch([])->then(function (Batch $batch) use ($winmax4Setting) {
                if(config('winmax4.use_license')){
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Entity::class, $winmax4Setting->license_id);
                }else{
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Entity::class);
                }

                $batch->delete();
            })->name('winmax4_entities')->onQueue(config('winmax4.queue'))->dispatch();

            $chunks = array_chunk($job, 100);

            foreach ($chunks as $chunk){
                $batch->add($chunk);
            }

        }
    }
}
