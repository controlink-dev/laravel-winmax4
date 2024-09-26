<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4DocumentTax extends Model
{
    use HasFactory;

    protected $table = 'winmax4_document_taxes';

    protected $fillable = [
        'document_id',
        'tax_fee_code',
        'percentage',
        'fixedAmount',
        'total_affected',
        'total',
    ];

    public function document()
    {
        return $this->belongsTo(Winmax4Document::class, 'document_id');
    }
}
