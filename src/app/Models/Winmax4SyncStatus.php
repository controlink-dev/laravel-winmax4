<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasLicenseScope;
use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Winmax4SyncStatus extends Model
{
    use HasFactory, HasWinmax4Connection, HasLicenseScope;

    public $timestamps = false;

    protected $fillable = [
        'model',
        'last_synced_at',
        'license_id',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];
}
