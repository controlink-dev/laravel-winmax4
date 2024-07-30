<?php

namespace Controlink\LaravelWinmax4\app\Jobs;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
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
                    'code' => $this->article->Code,
                    config('winmax4.license_column') => $this->license_id,
                ],
                [
                    'id_winmax4' => $this->article->ID,
                    'designation' => $this->article->Designation ?? null,
                    'short_description' => $this->article->ShortDescription ?? null,
                    'is_active' => $this->article->IsActive,
                    'family_code' => $this->article->FamilyCode,
                    'sub_family_code' => $this->article->SubFamilyCode ?? null,
                    'sub_sub_family_code' => $this->article->SubSubFamilyCode ?? null,
                    'sub_sub_sub_family_code' => $this->article->SubSubSubFamilyCode ?? null,
                    'stock_unit_code' => $this->article->StockUnitCode ?? null,
                    'image_url' => $this->article->ImageUrl ?? null,
                    'extras' => $this->article->Extras ?? null,
                    'holds' => $this->article->Holds ?? null,
                    'descriptives' => $this->article->Descriptives ?? null,

                ]
            );
        }else{
            Winmax4Article::updateOrCreate(
                [
                    'code' => $this->article->Code,
                ],
                [
                    'designation' => $this->article->Designation ?? null,
                    'short_description' => $this->article->ShortDescription ?? null,
                    'is_active' => $this->article->IsActive,
                    'family_code' => $this->article->FamilyCode,
                    'sub_family_code' => $this->article->SubFamilyCode ?? null,
                    'sub_sub_family_code' => $this->article->SubSubFamilyCode ?? null,
                    'sub_sub_sub_family_code' => $this->article->SubSubSubFamilyCode ?? null,
                    'stock_unit_code' => $this->article->StockUnitCode ?? null,
                    'image_url' => $this->article->ImageUrl ?? null,
                    'extras' => $this->article->Extras ?? null,
                    'holds' => $this->article->Holds ?? null,
                    'descriptives' => $this->article->Descriptives ?? null,
                ]
            );
        }

    }
}
