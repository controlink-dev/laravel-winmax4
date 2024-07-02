<?php

namespace Controlink\LaravelWinmax4;

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

    }
}