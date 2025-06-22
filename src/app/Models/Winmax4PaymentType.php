<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4PaymentType extends Model
{
    use HasFactory;

    protected $table = 'winmax4_payment_types';

    protected $fillable = [
        'license_id',
        'designation',
        'is_active',
        'id_winmax4',
    ];

    protected static function booted()
    {
        if(config('winmax4.use_license') && !app()->runningInConsole()){
            static::addGlobalScope(new LicenseScope());

            static::creating(function ($model) {
                $model->{config('winmax4.license_column')} = session(config('winmax4.license_session_key'));
            });
        }
    }
}
