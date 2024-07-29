<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4Article extends Model
{
    use HasFactory;

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
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new LicenseScope);
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

    public function saleTax()
    {
        return $this->hasMany(Winmax4ArticleSaleTaxes::class, 'article_id', 'id');
    }

    public function purchaseTax()
    {
        return $this->hasMany(Winmax4ArticlePurchaseTaxes::class, 'article_id', 'id');
    }

    public function prices()
    {
        return $this->hasMany(Winmax4ArticlePrices::class, 'article_id', 'id');
    }

    public function stocks()
    {
        return $this->hasMany(Winmax4ArticleStocks::class, 'article_id', 'id');
    }

    public function categories()
    {
        return $this->hasMany(Winmax4ArticleCategories::class, 'article_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(Winmax4ArticleQuestions::class, 'article_id', 'id');
    }

    public function idioms()
    {
        return $this->hasMany(Winmax4ArticleIdioms::class, 'article_id', 'id');
    }

}