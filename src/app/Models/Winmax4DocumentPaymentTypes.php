<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4DocumentPaymentTypes extends Model
{
    use HasFactory, HasWinmax4Connection;

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
