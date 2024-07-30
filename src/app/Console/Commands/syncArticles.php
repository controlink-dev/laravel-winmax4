<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Jobs\SyncArticlesJob;
use Controlink\LaravelWinmax4\app\Jobs\SyncEntitiesJob;
use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
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
    protected $signature = 'winmax4:sync-articles';

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
        $winmax4Settings = Winmax4Setting::get();

        foreach ($winmax4Settings as $winmax4Setting) {
            $this->info('Syncing articles  for ' . $winmax4Setting->company_code . '...');
            $winmax4Service = new Winmax4Service(
                false,
                $winmax4Setting->url,
                $winmax4Setting->company_code,
                $winmax4Setting->username,
                $winmax4Setting->password,
                $winmax4Setting->n_terminal
            );

            $localArticles = Winmax4Article::where('license_id', $winmax4Setting->license_id)->get();

            $articles = $winmax4Service->getArticles()->Data->Articles;

            dd($articles);

            //Delete all local entities that don't exist in Winmax4
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
