<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4PaymentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4Warehouse;
use Controlink\LaravelWinmax4\app\Services\Winmax4PaymentTypeService;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Controlink\LaravelWinmax4\app\Services\Winmax4WarehouseService;
use Illuminate\Console\Command;

class syncPaymentTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-payment-types
                            {--license_id= : If you want to sync payment types for a specific license, specify the license id.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync payment types from Winmax4 API';

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

            $this->info('Syncing warehouses for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4PaymentTypeService(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal
            );

            $paymentTypes = $winmax4Service->getPaymentTypes()->Data->PaymentTypes;

            dd($paymentTypes);
            foreach ($paymentTypes as $paymentType) {
                 if(config('winmax4.use_license')){
                     Winmax4PaymentType::updateOrCreate(
                         [
                            'designation' => $paymentType->Designation,
                            config('winmax4.license_column') => $winmax4Setting->license_id,
                         ],
                         [
                            'designation' => $paymentType->Designation,
                            'is_active' => $paymentType->IsActive,
                            'id_winmax4' => $paymentType->ID,
                         ]
                     );
                 }else{
                     Winmax4PaymentType::updateOrCreate(
                         [
                             'designation' => $paymentType->Designation,
                         ],
                         [
                             'designation' => $paymentType->Designation,
                             'is_active' => $paymentType->IsActive,
                             'id_winmax4' => $paymentType->ID
                         ]
                     );
                 }
            }

            if(config('winmax4.use_license')){
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4PaymentType::class, $winmax4Setting->license_id);
            }else{
                (new Winmax4Controller())->updateLastSyncedAt(Winmax4PaymentType::class);
            }
        }

    }
}
