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
        'article_id',
        'tax_fee_code',
        'percentage',
        'fixedAmount',
        'total_affected',
        'total',
    ];
}
