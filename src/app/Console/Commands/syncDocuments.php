<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4Document;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentDetail;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentDetailTax;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentTax;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4Warehouse;
use Controlink\LaravelWinmax4\app\Services\Winmax4DocumentService;
use Controlink\LaravelWinmax4\app\Services\Winmax4DocumentTypeService;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Console\Command;

class syncDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-documents
                            {--license_id= : If you want to sync document types for a specific license, specify the license id.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync document from Winmax4 API';

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
            $winmax4Service = new Winmax4DocumentService(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal,
                $winmax4Setting->license_id
            );

            $lastSyncedAt = null;
            if(config('winmax4.use_license')){
                $lastSyncedAt = (new Winmax4Controller())->getLastSyncedAt(Winmax4Document::class, $winmax4Setting->license_id);
            }else{
                $lastSyncedAt = (new Winmax4Controller())->getLastSyncedAt(Winmax4Document::class);
            }

            $documents = $winmax4Service->getDocuments($fromDate = $lastSyncedAt->format('Y-m-d'))->Data->Documents;

            foreach ($documents as $document) {
                 if(config('winmax4.use_license')){
                     $documentType = Winmax4DocumentType::where(config('winmax4.license_column'), $winmax4Setting->license_id)
                        ->where('code', $document->DocumentTypeCode)->first();

                     $savedDocument = Winmax4Document::updateOrCreate(
                         [
                             'document_type_id' => $documentType->id,
                             'document_number' => $document->DocumentNumber,
                             config('winmax4.license_column') => $winmax4Setting->license_id,
                         ],
                         [
                             'serie' => $document->Serie,
                             'number' => $document->Number,
                             'date' => $document->Date,
                             'external_identification' => $document->ExternalIdentification ?? null,
                             'currency_id' => Winmax4Currency::where('code', $document->CurrencyCode)->first()->id,
                             'is_deleted' => $document->IsDeleted,
                             'user_login' => $document->UserLogin,
                             'terminal_code' => $document->TerminalCode,
                             'source_warehouse_id' => Winmax4Warehouse::where('code', $document->SourceWarehouseCode)->first()->id,
                             'target_warehouse_id' => Winmax4Warehouse::where('code', $document->TargetWarehouseCode)->first()->id ?? null,
                             'entity_id' => Winmax4Entity::where('code', $document->Entity->Code)->first()->id,
                             'total_without_taxes' => $document->TotalWithoutTaxes,
                             'total_applied_taxes' => $document->TotalAppliedTaxes,
                             'total_with_taxes' => $document->TotalWithTaxes,
                             'total_liquidated' => $document->TotalLiquidated,
                             'load_address' => $document->LoadAddress,
                             'load_location' => $document->LoadLocation,
                             'load_zip_code' => $document->LoadZipCode,
                             'load_date_time' => $document->LoadDateTime,
                             'load_vehicle_license_plate' => $document->LoadVehicleLicensePlate ?? null,
                             'load_country_code' => $document->LoadCountryCode ?? null,
                             'unload_address' => $document->UnloadAddress,
                             'unload_location' => $document->UnloadLocation,
                             'unload_zip_code' => $document->UnloadZipCode,
                             'unload_date_time' => $document->UnloadDateTime,
                             'unload_country_code' => $document->UnloadCountryCode,
                             'hash_characters' => $document->HashCharacters,
                             'ta_doc_code_id' => $document->TADocCodeID ?? null,
                             'atcud' => $document->ATCUD ?? null,
                             'table_number' => $document->TableNumber ?? null,
                             'table_split_number' => $document->TableSplitNumber ?? null,
                             'sales_person_code' => $document->SalesPersonCode ?? null,
                             'remarks' => $document->Remarks ?? null,
                         ]
                     );
                 }else{
                     $savedDocument = Winmax4Document::updateOrCreate(
                         [
                             'document_type_id' => $documentType->id,
                             'document_number' => $document->DocumentNumber,
                         ],
                         [
                             'serie' => $document->Serie,
                             'number' => $document->Number,
                             'date' => $document->Date,
                             'external_identification' => $document->ExternalIdentification ?? null,
                             'currency_id' => Winmax4Currency::where('code', $document->CurrencyCode)->first()->id,
                             'is_deleted' => $document->IsDeleted,
                             'user_login' => $document->UserLogin,
                             'terminal_code' => $document->TerminalCode,
                             'source_warehouse_id' => Winmax4Warehouse::where('code', $document->SourceWarehouseCode)->first()->id,
                             'target_warehouse_id' => Winmax4Warehouse::where('code', $document->TargetWarehouseCode)->first()->id ?? null,
                             'entity_id' => Winmax4Entity::where('code', $document->Entity->Code)->first()->id,
                             'total_without_taxes' => $document->TotalWithoutTaxes,
                             'total_applied_taxes' => $document->TotalAppliedTaxes,
                             'total_with_taxes' => $document->TotalWithTaxes,
                             'total_liquidated' => $document->TotalLiquidated,
                             'load_address' => $document->LoadAddress,
                             'load_location' => $document->LoadLocation,
                             'load_zip_code' => $document->LoadZipCode,
                             'load_date_time' => $document->LoadDateTime,
                             'load_vehicle_license_plate' => $document->LoadVehicleLicensePlate ?? null,
                             'load_country_code' => $document->LoadCountryCode ?? null,
                             'unload_address' => $document->UnloadAddress,
                             'unload_location' => $document->UnloadLocation,
                             'unload_zip_code' => $document->UnloadZipCode,
                             'unload_date_time' => $document->UnloadDateTime,
                             'unload_country_code' => $document->UnloadCountryCode,
                             'hash_characters' => $document->HashCharacters,
                             'ta_doc_code_id' => $document->TADocCodeID ?? null,
                             'atcud' => $document->ATCUD ?? null,
                             'table_number' => $document->TableNumber ?? null,
                             'table_split_number' => $document->TableSplitNumber ?? null,
                             'sales_person_code' => $document->SalesPersonCode ?? null,
                             'remarks' => $document->Remarks ?? null,
                         ]
                     );
                 }

                foreach ($document->Details as $detail) {
                    $documentDetail = Winmax4DocumentDetail::updateOrCreate([
                        'document_id' => $savedDocument->id,
                        'article_id' => Winmax4Article::where('code', $detail->ArticleCode)->first()->id,
                    ],
                    [
                        'unitary_price_without_taxes' => $detail->UnitaryPriceWithoutTaxes,
                        'unitary_price_with_taxes' => $detail->UnitaryPriceWithTaxes,
                        'discount_percentage_1' => $detail->DiscountPercentage1,
                        'quantity' => $detail->Quantity,
                        'total_without_taxes' => $detail->TotalWithoutTaxes,
                        'total_with_taxes' => $detail->TotalWithTaxes,
                        'remarks' => $detail->Remarks ?? null,
                    ]);

                    foreach ($detail->Taxes as $tax) {
                        Winmax4DocumentDetailTax::updateOrCreate([
                            'document_detail_id' => $documentDetail->id,
                        ],
                        [
                            'tax_fee_code' => $tax->TaxFeeCode,
                            'percentage' => $tax->Percentage,
                        ]);
                    }
                }

                foreach ($document->Taxes as $tax) {
                    Winmax4DocumentTax::updateOrCreate([
                        'document_id' => $savedDocument->id,
                    ],
                    [
                        'tax_fee_code' => $tax->TaxFeeCode,
                        'percentage' => $tax->Percentage,
                        'fixedAmount' => $tax->FixedAmount ?? null,
                        'total_affected' => $tax->TotalAffected,
                        'total' => $tax->Total,
                    ]);
                }

            }

            if(config('winmax4.use_license')){
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4Document::class, $winmax4Setting->license_id);
            }else{
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4Document::class);
            }
        }

    }
}
