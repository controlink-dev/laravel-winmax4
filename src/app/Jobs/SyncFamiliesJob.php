<?php

namespace Controlink\LaravelWinmax4\app\Jobs;

use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubFamily;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubSubFamily;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncFamiliesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $family;
    protected $license_id;

    /**
     * Create a new job instance.
     */
    public function __construct($family, $license_id)
    {
        $this->family = $family;
        $this->license_id = $license_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $newFamily = Winmax4Family::updateOrCreate(
            [
                'code' => $this->family->Code
            ],
            [
                'license_id' => $this->license_id,
                'designation' => $this->family->Designation,
                'is_active' => $this->family->IsActive,
            ]
        );

        dump($newFamily);

        if (isset($this->family->SubFamilies)) {
            foreach ($this->family->SubFamilies as $subFamily) {
                $newSubFamily = Winmax4SubFamily::updateOrCreate(
                    [
                        'family_id' => $newFamily->id,
                        'code' => $subFamily->Code,
                        'designation' => $subFamily->Designation,
                    ],
                    [
                        'family_id' => $this->family->id,
                        'code' => $subFamily->Code,
                        'designation' => $subFamily->Designation,
                    ]
                );

                if (isset($subFamily->SubSubFamilies)) {
                    foreach ($subFamily->SubSubFamilies as $subSubFamily) {
                        $newSubSubFamily = Winmax4SubSubFamily::updateOrCreate(
                            [
                                'sub_family_id' => $newSubFamily->id,
                                'code' => $subSubFamily->Code,
                                'designation' => $subSubFamily->Designation,
                            ],
                            [
                                'sub_family_id' => $newSubFamily->id,
                                'code' => $subSubFamily->Code,
                                'designation' => $subSubFamily->Designation,
                            ]
                        );
                    }
                }
            }
        }
    }
}
