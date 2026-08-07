<?php

namespace Controlink\LaravelWinmax4\app\Models\Concerns;

trait HasWinmax4Connection
{
    /**
     * Falls back to the model's normal connection resolution when
     * separated databases are off, so packages/apps that don't opt in
     * see no behavior change. When separated databases are on, a
     * connection name is required: misconfiguration fails fast instead
     * of silently falling back to the default connection.
     */
    public function getConnectionName()
    {
        if (config('winmax4.use_separated_databases')) {
            $connection = config('winmax4.connection_name');

            if (!$connection) {
                throw new \RuntimeException('winmax4.use_separated_databases is enabled but winmax4.connection_name is not set.');
            }

            return $connection;
        }

        return parent::getConnectionName();
    }
}
