<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Console\Command;

class syncDocumentsTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-document-types';

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
        $winmax4Settings = Winmax4Setting::get();

        foreach ($winmax4Settings as $winmax4Setting) {
            $this->info('Syncing document types for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4Service(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal
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
