<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4DocumentDetailTax extends Model
{
    use HasFactory, HasWinmax4Connection;

    protected $table = 'winmax4_document_details_taxes';

    protected $fillable = [
        'document_detail_id',
        'tax_fee_code',
        'percentage',
    ];

    public function documentDetail()
    {
        return $this->belongsTo(Winmax4DocumentDetail::class, 'document_detail_id');
    }
}
