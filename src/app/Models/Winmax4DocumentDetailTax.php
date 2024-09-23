<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4DocumentDetailTax extends Model
{
    use HasFactory;

    protected $table = 'winmax4_document_details_taxes';

    protected $fillable = [
        'document_detail_id',
        'tax_fee_code',
        'percentage',
    ];
}
