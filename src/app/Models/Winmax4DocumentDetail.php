<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4DocumentDetail extends Model
{
    use HasFactory;

    protected $table = 'winmax4_document_details';

    protected $fillable = [
        'document_id',
        'article_id',
        'unitary_price_without_taxes',
        'unitary_price_with_taxes',
        'discount_percentage_1',
        'quantity',
        'total_without_taxes',
        'total_with_taxes',
        'remarks',
        'tax_id',
        'tax_rate_id',
    ];

    public function document()
    {
        return $this->belongsTo(Winmax4Document::class, 'document_id');
    }

    public function article()
    {
        return $this->belongsTo(Winmax4Article::class, 'article_id');
    }

    public function tax()
    {
        return $this->belongsTo(Winmax4Tax::class, 'tax_id');
    }

    public function taxRate()
    {
        return $this->belongsTo(Winmax4TaxRates::class, 'tax_rate_id');
    }
}
