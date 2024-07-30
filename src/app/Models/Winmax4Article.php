<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Winmax4Article extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_PRODUCT = 0;
    const TYPE_SERVICE = 1;

    protected $table = 'winmax4_articles';

    protected $fillable = [
        'code',
        'designation',
        'short_description',
        'is_active',
        'family_code',
        'sub_family_code',
        'sub_sub_family_code',
        'sub_sub_sub_family_code',
        'stock_unit_code',
        'image_url',
        'extras',
        'holds',
        'descriptives',
        'license_id',
        'id_winmax4',
    ];

    protected static function booted()
    {
        if(config('winmax4.use_license') && !app()->runningInConsole()){
            static::addGlobalScope(new LicenseScope());
        }
    }

    public function family()
    {
        return $this->belongsTo(Winmax4Family::class, 'family_code', 'code');
    }

    public function subFamily()
    {
        return $this->belongsTo(Winmax4SubFamily::class, 'sub_family_code', 'code');
    }

    public function subSubFamily()
    {
        return $this->belongsTo(Winmax4SubSubFamily::class, 'sub_sub_family_code', 'code');
    }

    public function saleTaxes()
    {
        return $this->belongsToMany(Winmax4ArticleSaleTaxes::class, 'article_id', 'id');
    }

    public function purchaseTaxes()
    {
        return $this->belongsToMany(Winmax4ArticlePurchaseTaxes::class, 'article_id', 'id');
    }

    public function prices()
    {
        return $this->belongsToMany(Winmax4ArticlePrices::class, 'article_id', 'id');
    }

    public function stocks()
    {
        return $this->belongsToMany(Winmax4ArticleStocks::class, 'article_id', 'id');
    }
}
