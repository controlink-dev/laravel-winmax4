<?php

namespace Controlink\LaravelWinmax4;

use Controlink\LaravelWinmax4\app\Console\Commands\syncCurrencies;
use Controlink\LaravelWinmax4\app\Console\Commands\syncDocumentsTypes;
use Controlink\LaravelWinmax4\app\Console\Commands\syncFamilies;
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

        // Publish migrations if running in console
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../src/database/migrations' => database_path('migrations'),
            ], 'winmax4-migrations');

            // Publish config file
            $this->publishes([
                __DIR__.'/../src/config/winmax4.php' => config_path('winmax4.php'),
            ], 'winmax4-config');
        }

        // Register the command
        $this->commands([
            syncCurrencies::class,
            syncDocumentsTypes::class,
            syncFamilies::class,
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

        // Schedule the command
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('winmax4:sync-currencies')->daily();
            $schedule->command('winmax4:sync-document-types')->daily();
            $schedule->command('winmax4:sync-families')->everyFifteenMinutes();
        });
    }
}