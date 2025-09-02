<?php

namespace Controlink\LaravelWinmax4\app\Jobs;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEntitiesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $entity;
    protected $license_id;

    /**
     * Create a new job instance.
     */
    public function __construct($entity, $license_id = null)
    {
        $this->entity = $entity;
        $this->license_id = $license_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (config('winmax4.use_license')) {

            $entity = Winmax4Entity::withTrashed()
                ->where('id_winmax4', $this->entity->ID)
                ->where(config('winmax4.license_column'), $this->license_id)
                ->first();

            if ($entity) {

                $entity->update([
                    'code'         => $this->entity->Code,
                    'name'         => $this->entity->Name,
                    'address'      => $this->entity->Address,
                    'country_code' => $this->entity->CountryCode,
                    'email'        => $this->entity->Email,
                    'entity_type'  => $this->entity->EntityType,
                    'fax'          => $this->entity->Fax,
                    'is_active'    => $this->entity->IsActive,
                    'location'     => $this->entity->Location,
                    'mobile_phone' => $this->entity->MobilePhone,
                    'phone'        => $this->entity->Phone,
                    'tax_payer_id' => $this->entity->TaxPayerID,
                    'zip_code'     => $this->entity->ZipCode,
                ]);
            } else {
                Winmax4Entity::create([
                    'id_winmax4'                        => $this->entity->ID,
                    config('winmax4.license_column')    => $this->license_id,
                    'code'                              => $this->entity->Code,
                    'name'                              => $this->entity->Name,
                    'address'                           => $this->entity->Address,
                    'country_code'                      => $this->entity->CountryCode,
                    'email'                             => $this->entity->Email,
                    'entity_type'                       => $this->entity->EntityType,
                    'fax'                               => $this->entity->Fax,
                    'is_active'                         => $this->entity->IsActive,
                    'location'                          => $this->entity->Location,
                    'mobile_phone'                      => $this->entity->MobilePhone,
                    'phone'                             => $this->entity->Phone,
                    'tax_payer_id'                      => $this->entity->TaxPayerID,
                    'zip_code'                          => $this->entity->ZipCode,
                ]);
            }

        } else {

            $entity = Winmax4Entity::withTrashed()
                ->where('id_winmax4', $this->entity->ID)
                ->first();

            if ($entity) {

                $entity->update([
                    'code'         => $this->entity->Code,
                    'name'         => $this->entity->Name,
                    'address'      => $this->entity->Address,
                    'country_code' => $this->entity->CountryCode,
                    'email'        => $this->entity->Email,
                    'entity_type'  => $this->entity->EntityType,
                    'fax'          => $this->entity->Fax,
                    'is_active'    => $this->entity->IsActive,
                    'location'     => $this->entity->Location,
                    'mobile_phone' => $this->entity->MobilePhone,
                    'phone'        => $this->entity->Phone,
                    'tax_payer_id' => $this->entity->TaxPayerID,
                    'zip_code'     => $this->entity->ZipCode,
                ]);
            } else {
                Winmax4Entity::create([
                    'id_winmax4'   => $this->entity->ID,
                    'code'         => $this->entity->Code,
                    'name'         => $this->entity->Name,
                    'address'      => $this->entity->Address,
                    'country_code' => $this->entity->CountryCode,
                    'email'        => $this->entity->Email,
                    'entity_type'  => $this->entity->EntityType,
                    'fax'          => $this->entity->Fax,
                    'is_active'    => $this->entity->IsActive,
                    'location'     => $this->entity->Location,
                    'mobile_phone' => $this->entity->MobilePhone,
                    'phone'        => $this->entity->Phone,
                    'tax_payer_id' => $this->entity->TaxPayerID,
                    'zip_code'     => $this->entity->ZipCode,
                ]);
            }
        }
    }
}
