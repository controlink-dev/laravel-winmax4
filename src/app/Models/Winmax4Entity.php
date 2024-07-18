<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Winmax4Entity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'winmax4_entities';

    protected $fillable = [
        'license_id',
        'id_winmax4',
        'name',
        'address',
        'code',
        'country_code',
        'email',
        'entity_type',
        'fax',
        'is_active',
        'location',
        'mobile_phone',
        'phone',
        'tax_payer_id',
        'zip_code',
    ];

    protected static function booted()
    {
        if(config('winmax4.use_license') && !app()->runningInConsole()){
            static::addGlobalScope(new LicenseScope());
        }
    }
}
