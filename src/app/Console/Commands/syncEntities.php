<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Jobs\SyncEntitiesJob;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4EntityService;
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
    protected $signature = 'winmax4:sync-entities
                            {--license_id= : If you want to sync entities for a specific license, specify the license id.}
                            {--fullSync : If you want to do a full sync, specify this option.}';


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
            $this->info('Syncing entities for license id ' . $license_id . '...');
            $winmax4Settings = Winmax4Setting::where(config('winmax4.license_column'), $license_id)->get();
        } else {
            $this->info('Syncing entities for all licenses...');
            $winmax4Settings = Winmax4Setting::get();
        }

        foreach ($winmax4Settings as $winmax4Setting) {
            if(!$winmax4Setting->tenant){
                continue;
            }

            $this->info('Syncing entities  for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4EntityService(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal,
                $winmax4Setting->license_id
            );

            if(config('winmax4.use_license')){

                if(config('winmax4.use_soft_deletes')){
                    //If the license_id option is set and soft deletes are enabled, get all entities including the deleted ones
                    $localEntities = Winmax4Entity::withTrashed()->where('license_id', $winmax4Setting->license_id)->get();
                }else{
                    //If the license_id option is set, get all entities by license_id
                    $localEntities = Winmax4Entity::where('license_id', $winmax4Setting->license_id)->get();
                }
            }else{
                //If the license_id option is not set, get all entities
                $localEntities = Winmax4Entity::get();
            }

            //If getEntities returns bad response, skip the sync
            $lastSyncedAt = null;
            if(!$this->option('fullSync')){
                if(config('winmax4.use_license')){
                    $lastSyncedAt = (new Winmax4Controller())->getLastSyncedAt(Winmax4Entity::class, $winmax4Setting->license_id)->format('Y-m-d');
                }else{
                    $lastSyncedAt = (new Winmax4Controller())->getLastSyncedAt(Winmax4Entity::class)->format('Y-m-d');
                }
            }

            $apiEntities = $winmax4Service->getEntities($lastSyncedAt);
            if ($apiEntities == null || is_array($apiEntities) && isset($apiEntities['error'])) {
                if($apiEntities != null && !$apiEntities['error'] && $apiEntities['status'] != 404){
                    $this->error('An error occurred while syncing articles for ' . $winmax4Setting->company_code. '.');
                    return;
                }

                if(config('winmax4.use_license')){
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Entity::class, $winmax4Setting->license_id);
                }else{
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Entity::class);
                }
            }else if($apiEntities != null && isset($apiEntities->error) && $apiEntities->error) {
                $this->error($apiEntities->error);
                return;
            } else {
                $entities = $apiEntities->Data->Entities;

                //Delete all local entities that don't exist in Winmax4
                if($this->option('fullSync')){
                    dump(count($entities), count($localEntities));
                    foreach ($localEntities as $localEntity) {
                        $found = false;
                        foreach ($entities as $entity) {

                            if ($localEntity->id_winmax4 == $entity->ID) {
                                $found = true;

                                //Check if the entities is_active status has changed
                                if ($localEntity->is_active != $entity->IsActive) {

                                    // Check if the entity is soft deleted
                                    if(config('winmax4.use_soft_deletes') && !$entity->IsActive && $localEntity->deleted_at == null){
                                        //If the entity is soft deleted, set the deleted_at timestamp
                                        $localEntity->deleted_at = now();
                                    }else{
                                        //If the entity is not soft deleted, clear the deleted_at timestamp
                                        $localEntity->deleted_at = null;
                                    }

                                    //If has changed, update the entity
                                    $localEntity->is_active = $entity->IsActive;
                                    $localEntity->save();
                                }

                                break;
                            }
                        }

                        if (!$found) {
                            if(config('winmax4.use_soft_deletes')){
                                //If the entity is not found in Winmax4, deactivate it
                                $localEntity->is_active = false;
                                $localEntity->deleted_at = now();
                                $localEntity->save();
                            }else{

                                //If the entity is not found in Winmax4, delete it
                                $localEntity->forceDelete();
                            }
                        }
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
                    $batch->delete();
                })->name('winmax4_entities')->onQueue(config('winmax4.queue'))->dispatch();

                $chunks = array_chunk($job, 100);

                foreach ($chunks as $chunk){
                    $batch->add($chunk);
                }

                if(config('winmax4.use_license')){
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Entity::class, $winmax4Setting->license_id);
                }else{
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Entity::class);
                }
            }

        }
    }
}