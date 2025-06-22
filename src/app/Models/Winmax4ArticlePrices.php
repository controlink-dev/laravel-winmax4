<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4ArticlePrices extends Model
{
    use HasFactory;

    protected $table = 'winmax4_articles_prices';

    protected $fillable = [
        'article_id',
        'currency_code',
        'sales_price1_without_taxes',
        'sales_price1_with_taxes',
        'sales_price2_without_taxes',
        'sales_price2_with_taxes',
        'sales_price3_without_taxes',
        'sales_price3_with_taxes',
        'sales_price4_without_taxes',
        'sales_price4_with_taxes',
        'sales_price5_without_taxes',
        'sales_price5_with_taxes',
        'sales_price6_without_taxes',
        'sales_price6_with_taxes',
        'sales_price7_without_taxes',
        'sales_price7_with_taxes',
        'sales_price8_without_taxes',
        'sales_price8_with_taxes',
        'sales_price9_without_taxes',
        'sales_price9_with_taxes',
        'sales_price_extra_without_taxes',
        'sales_price_extra_with_taxes',
        'sales_price_hold_without_taxes',
        'sales_price_hold_with_taxes',
    ];


    public function article()
    {
        return $this->belongsTo(Winmax4Article::class, 'article_id', 'id');
    }
}
