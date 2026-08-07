<?php

namespace Controlink\LaravelWinmax4\app\Models\Concerns;

trait HasWinmax4Connection
{
    /**
     * Falls back to the model's normal connection resolution when
     * separated databases are off or no connection name is configured,
     * so packages/apps that don't opt in see no behavior change.
     */
    public function getConnectionName()
    {
        if (config('winmax4.use_separated_databases') && config('winmax4.connection_name')) {
            return config('winmax4.connection_name');
        }

        return parent::getConnectionName();
    }
}
