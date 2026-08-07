<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasLicenseScope;
use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4Tax extends Model
{
    use HasFactory, HasWinmax4Connection, HasLicenseScope;

    protected $table = 'winmax4_taxes';

    protected $fillable = [
        'license_id',
        'code',
        'designation',
        'is_active',
    ];

    public function taxRates()
    {
        return $this->hasMany(Winmax4TaxRates::class, 'tax_id');
    }
}
