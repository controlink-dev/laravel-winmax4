<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Winmax4DocumentType extends Model
{
    use HasFactory;

    protected $table = 'winmax4_currencies';

    protected $fillable = [
        'license_id',
        'code',
        'designation',
        'is_active',
        'transaction_type',
        'entity_type',
    ];

    protected static function booted()
    {
        if(config('winmax4.use_license') && !app()->runningInConsole()){
            static::addGlobalScope(new LicenseScope());
        }
    }
}