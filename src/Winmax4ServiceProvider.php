<?php

namespace Controlink\LaravelWinmax4;

use Controlink\LaravelWinmax4\app\Console\Commands\syncArticles;
use Controlink\LaravelWinmax4\app\Console\Commands\syncCurrencies;
use Controlink\LaravelWinmax4\app\Console\Commands\syncDocuments;
use Controlink\LaravelWinmax4\app\Console\Commands\syncDocumentsTypes;
use Controlink\LaravelWinmax4\app\Console\Commands\syncEntities;
use Controlink\LaravelWinmax4\app\Console\Commands\syncFamilies;
use Controlink\LaravelWinmax4\app\Console\Commands\syncTaxes;
use Controlink\LaravelWinmax4\app\Console\Commands\syncWarehouses;
use Controlink\LaravelWinmax4\app\Console\Commands\updateNamespace;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class Winmax4ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @throws \Exception
     */
    public function boot()
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../src/database/migrations');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../src/resources/lang', 'laravel-winmax4');

        // Publish migrations if running in console
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../src/app/Models' => app_path('Models/Winmax4'),
            ], 'winmax4-models');

            $this->publishes([
                __DIR__.'/../src/database/migrations' => database_path('migrations'),
            ], 'winmax4-migrations');

            // Publish config file
            $this->publishes([
                __DIR__.'/../src/config/winmax4.php' => config_path('winmax4.php'),
            ], 'winmax4-config');

            // Publish the language files
            $this->publishes([
                __DIR__.'/../src/resources/lang' => resource_path('lang'),
            ], 'winmax4-lang');
        }

        // Register the command
        $this->commands([
            updateNamespace::class,
            syncCurrencies::class,
            syncDocumentsTypes::class,
            syncTaxes::class,
            syncWarehouses::class,
            syncFamilies::class,
            syncArticles::class,
            syncEntities::class,
            syncDocuments::class,
        ]);

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Load config file
        $this->mergeConfigFrom(__DIR__.'/../src/config/winmax4.php', 'winmax4');
    }
}