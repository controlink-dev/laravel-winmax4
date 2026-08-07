<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasLicenseScope;
use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4Setting extends Model
{
    use HasFactory, HasWinmax4Connection, HasLicenseScope;

    protected $fillable = [
        'url',
        'company_code',
        'username',
        'password',
        'n_terminal',
        'warehouse_code',
        'type_docs_invoice',
        'type_docs_invoice_receipt',
        'type_docs_credit_note',
        'type_docs_receipt',
    ];

    public function DocumentTypeCodeAttribute()
    {
        return [
            'invoice' => Winmax4DocumentType::find($this->type_docs_invoice),
            'invoice_receipt' => Winmax4DocumentType::find($this->type_docs_invoice_receipt),
            'credit_note' => Winmax4DocumentType::find($this->type_docs_credit_note),
            'receipt' => Winmax4DocumentType::find($this->type_docs_receipt),
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(config('winmax4.licenses_model'), config('winmax4.license_column'));
    }
}
