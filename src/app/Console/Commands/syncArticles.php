<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Jobs\SyncArticlesJob;
use Controlink\LaravelWinmax4\app\Jobs\SyncEntitiesJob;
use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4ArticleService;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class syncArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winmax4:sync-articles
                            {--license_id= : If you want to sync articles for a specific license, specify the license id.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync articles from Winmax4 API to the database.';

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
            $this->info('Syncing articles for license id ' . $license_id . '...');
            $winmax4Settings = Winmax4Setting::where(config('winmax4.license_column'), $license_id)->get();
        } else {
            $this->info('Syncing articles for all licenses...');
            $winmax4Settings = Winmax4Setting::get();
        }

        foreach ($winmax4Settings as $winmax4Setting) {
            $this->info('Syncing articles  for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4ArticleService(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal
            );

            $localArticles = Winmax4Article::where('license_id', $winmax4Setting->license_id)->get();

            $articles = $winmax4Service->getArticles()->Data->Articles;

            //Delete all local articles that don't exist in Winmax4
            foreach ($localArticles as $localArticle) {
                $found = false;
                foreach ($articles as $article) {
                    if ($localArticle->id_winmax4 == $article->ID) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    if(config('winmax4.use_soft_deletes')){
                        $localArticle->delete();
                    }else{
                        $localArticle->forceDelete();
                    }
                }
            }

            $job = [];
            foreach ($articles as $article) {
                if(config('winmax4.use_license')){
                    $job[] = new SyncArticlesJob($article, $winmax4Setting->license_id);
                }else{
                    $job[] = new SyncArticlesJob($article);
                }
            }

            $batch = Bus::batch([])->then(function (Batch $batch) use ($winmax4Setting) {
                if(config('winmax4.use_license')){
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Article::class, $winmax4Setting->license_id);
                }else{
                    (new Winmax4Controller())->updateLastSyncedAt(Winmax4Article::class);
                }

                $batch->delete();
            })->name('winmax4_articles')->onQueue(config('winmax4.queue'))->dispatch();

            $chunks = array_chunk($job, 100);

            foreach ($chunks as $chunk){
                $batch->add($chunk);
            }

        }
    }
}
