<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
            'invoice' => $this->type_docs_invoice,
            'invoice_receipt' => $this->type_docs_invoice_receipt,
            'credit_note' => $this->type_docs_credit_note,
            'receipt' => $this->type_docs_receipt,
        ];
    }
}
