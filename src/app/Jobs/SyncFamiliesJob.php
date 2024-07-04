<?php

namespace Controlink\LaravelWinmax4\app\Jobs;

use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
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
        Winmax4Family::updateOrCreate(
            [
                'code' => $this->family->Code
            ],
            [
                'license_id' => $this->license_id,
                'designation' => $this->family->Designation,
                'is_active' => $this->family->IsActive,
            ]
        );
    }
}
