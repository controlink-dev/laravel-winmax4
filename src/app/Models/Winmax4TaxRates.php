<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4TaxRates extends Model
{
    use HasFactory, HasWinmax4Connection;

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
