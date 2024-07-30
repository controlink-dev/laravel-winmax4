<?php

namespace Controlink\LaravelWinmax4\app\Jobs;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
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

class SyncArticlesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $article;
    protected $license_id;

    /**
     * Create a new job instance.
     */
    public function __construct($article, $license_id = null)
    {
        $this->article = $article;
        $this->license_id = $license_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(config('winmax4.use_license')){
            Winmax4Article::updateOrCreate(
                [

                    config('winmax4.license_column') => $this->license_id,
                ],
                [
                    'id_winmax4' => $this->entity->ID,

                ]
            );
        }else{
            Winmax4Article::updateOrCreate(
                [

                ],
                [
                    'id_winmax4' => $this->entity->ID,

                ]
            );
        }

    }
}
