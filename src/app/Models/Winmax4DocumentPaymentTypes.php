<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4DocumentPaymentTypes extends Model
{
    use HasFactory;

    protected $table = 'winmax4_document_payments';

    protected $fillable = [
        'document_id',
        'payment_type_id',
        'designation',
        'value'
    ];

    public function document()
    {
        return $this->belongsTo(Winmax4Document::class, 'document_id');
    }
}
