<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4ArticleSaleTaxes extends Model
{
    use HasFactory;

    protected $table = 'winmax4_articles_sale_taxes';

    protected $fillable = [
        'article_id',
        'tax_fee_code',
        'percentage',
        'fixedAmount',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new LicenseScope);
    }

    public function article()
    {
        return $this->belongsTo(Winmax4Article::class, 'article_id', 'id');
    }
}
