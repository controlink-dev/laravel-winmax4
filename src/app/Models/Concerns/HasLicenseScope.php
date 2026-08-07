<?php

namespace Controlink\LaravelWinmax4\app\Models\Concerns;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;

trait HasLicenseScope
{
    /**
     * Eloquent auto-calls boot{TraitName}() for traits used on a model,
     * so this replaces the booted() method that used to be duplicated
     * across every license-scoped model.
     */
    public static function bootHasLicenseScope(): void
    {
        if (config('winmax4.use_license')
            && !config('winmax4.use_separated_databases')
            && !app()->runningInConsole()
        ) {
            static::addGlobalScope(new LicenseScope());

            static::creating(function ($model) {
                $model->{config('winmax4.license_column')} = session(config('winmax4.license_session_key'));
            });
        }
    }
}
