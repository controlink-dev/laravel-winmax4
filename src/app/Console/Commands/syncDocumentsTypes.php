<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4DocumentTypeService;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Console\Command;

class syncDocumentsTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-document-types
                            {--license_id= : If you want to sync document types for a specific license, specify the license id.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync document types from Winmax4 API';

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
            $this->info('Syncing document types for license id ' . $license_id . '...');
            $winmax4Settings = Winmax4Setting::where(config('winmax4.license_column'), $license_id)->get();
        } else {
            $this->info('Syncing document types for all licenses...');
            $winmax4Settings = Winmax4Setting::get();
        }

        foreach ($winmax4Settings as $winmax4Setting) {
            if(!$winmax4Setting->tenant){
                continue;
            }

            $this->info('Syncing document types for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4DocumentTypeService(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal,
                $winmax4Setting->license_id
            );

            $documentTypes = $winmax4Service->getDocumentTypes()->Data->DocumentTypes;

            $documentTypes = collect($documentTypes)->where('TransactionType', 0)->where('EntityType', 0);

            foreach ($documentTypes as $documentType) {
                 if(config('winmax4.use_license')){
                     Winmax4DocumentType::updateOrCreate(
                         [
                             'code' => $documentType->Code,
                             config('winmax4.license_column') => $winmax4Setting->license_id,
                         ],
                         [
                             'designation' => $documentType->Designation,
                             'is_active' => $documentType->IsActive,
                             'transaction_type' => $documentType->TransactionType,
                             'entity_type' => $documentType->EntityType,
                         ]
                     );
                 }else{
                     Winmax4DocumentType::updateOrCreate(
                         [
                             'code' => $documentType->Code,
                         ],
                         [
                             'designation' => $documentType->Designation,
                             'is_active' => $documentType->IsActive,
                             'transaction_type' => $documentType->TransactionType,
                             'entity_type' => $documentType->EntityType,
                         ]
                     );
                 }
            }

            if(config('winmax4.use_license')){
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4DocumentType::class, $winmax4Setting->license_id);
            }else{
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4DocumentType::class);
            }
        }

    }
}
