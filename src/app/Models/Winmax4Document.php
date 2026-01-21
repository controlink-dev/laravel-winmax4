<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Winmax4Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'winmax4_documents';

    protected $fillable = [
        'license_id',
        'document_type_id',
        'document_number',
        'serie',
        'number',
        'date',
        'external_identification',
        'currency_id',
        'is_deleted',
        'user_login',
        'terminal_code',
        'source_warehouse_id',
        'target_warehouse_id',
        'entity_id',
        'total_without_taxes',
        'total_applied_taxes',
        'total_with_taxes',
        'total_liquidated',
        'load_address',
        'load_location',
        'load_zip_code',
        'load_date_time',
        'load_vehicle_license_plate',
        'load_country_code',
        'unload_address',
        'unload_location',
        'unload_zip_code',
        'unload_date_time',
        'unload_country_code',
        'hash_characters',
        'ta_doc_code_id',
        'atcud',
        'table_number',
        'table_split_number',
        'sales_person_code',
        'remarks',
        'document_tax_id',
        'url',
        'cancel_reason'
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

    public function documentType()
    {
        return $this->belongsTo(Winmax4DocumentType::class, 'document_type_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Winmax4Currency::class, 'currency_id', 'id');
    }

    public function sourceWarehouse()
    {
        return $this->belongsTo(Winmax4Warehouse::class, 'source_warehouse_id', 'id');
    }

    public function targetWarehouse()
    {
        return $this->belongsTo(Winmax4Warehouse::class, 'target_warehouse_id', 'id');
    }

    public function entity()
    {
        return $this->belongsTo(Winmax4Entity::class, 'entity_id', 'id');
    }

    public function documentTax()
    {
        return $this->belongsTo(Winmax4DocumentTax::class, 'document_tax_id', 'id');
    }

    public function paymentTypes()
    {
        return $this->belongsTo(Winmax4PaymentType::class, 'payment_type_id');
    }
}
