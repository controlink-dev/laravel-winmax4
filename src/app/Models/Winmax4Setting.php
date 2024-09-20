<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'company_code',
        'username',
        'password',
        'n_terminal',
        'type_docs_invoice',
        'type_docs_invoice_receipt',
        'type_docs_credit_note',
        'type_docs_receipt',
    ];

    protected static function booted()
    {
        if(config('winmax4.use_license') && !app()->runningInConsole()){
            static::addGlobalScope(new LicenseScope());

            static::creating(function ($model) {
                $model->{config('winmax4.license_column')} = session(config('winmax4.license_session_key'));
            });
        }
    }

    public function DocumentTypeCodeAttribute()
    {
        return [
            'invoice' => Winmax4DocumentType::find($this->type_docs_invoice)->code,
            'invoice_receipt' => Winmax4DocumentType::find($this->type_docs_invoice_receipt)->code,
            'credit_note' => Winmax4DocumentType::find($this->type_docs_credit_note)->code,
            'receipt' => Winmax4DocumentType::find($this->type_docs_receipt)->code,
        ];
    }
}
