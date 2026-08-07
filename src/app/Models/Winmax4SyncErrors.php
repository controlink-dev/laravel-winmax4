<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4SyncErrors extends Model
{
    use HasFactory, HasWinmax4Connection;

    protected $table = 'winmax4_sync_errors';

    protected $fillable = [
        'license_id',
        'message',
    ];

    protected static function booted()
    {
        if(config('winmax4.use_license')
            && !config('winmax4.use_separated_databases')
            && !app()->runningInConsole()
        ){
            static::addGlobalScope(new LicenseScope());
        }
    }
}
