<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4Tax extends Model
{
    use HasFactory;

    protected $table = 'winmax4_taxes';

    protected $fillable = [
        'license_id',
        'code',
        'designation',
        'is_active',
    ];

    protected static function booted()
    {
        if(config('winmax4.use_license') && !app()->runningInConsole()){
            static::addGlobalScope(new LicenseScope());
        }
    }

    public function taxRates()
    {
        return $this->hasMany(Winmax4TaxRates::class, 'tax_id');
    }
}
