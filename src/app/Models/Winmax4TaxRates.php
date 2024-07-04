<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4TaxRates extends Model
{
    use HasFactory;

    protected $table = 'winmax4_taxes_rates';

    protected $fillable = [
        'tax_id',
        'fixedAmount',
        'percentage',
    ];

    public function tax()
    {
        return $this->belongsTo(Winmax4Tax::class, 'tax_id');
    }
}
