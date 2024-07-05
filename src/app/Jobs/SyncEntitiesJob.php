<?php

namespace Controlink\LaravelWinmax4\app\Jobs;

use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubFamily;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubSubFamily;
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
    public function __construct($entity, $license_id)
    {
        $this->entity = $entity;
        $this->license_id = $license_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Winmax4Entity::updateOrCreate(
            [
                'code' => $this->entity->Code,
                'tax_payer_id' => $this->entity->TaxPayerID,
            ],
            [
                'license_id' => $this->license_id,
                'name' => $this->entity->Name,
                'address' => $this->entity->Address,
                'country_code' => $this->entity->CountryCode,
                'email' => $this->entity->Email,
                'entity_type' => $this->entity->EntityType,
                'fax' => $this->entity->Fax,
                'is_active' => $this->entity->IsActive,
                'location' => $this->entity->Location,
                'mobile_phone' => $this->entity->MobilePhone,
                'phone' => $this->entity->Phone,
                'tax_payer_id' => $this->entity->TaxPayerID,
                'zip_code' => $this->entity->ZipCode,
            ]
        );
    }
}
