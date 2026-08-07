<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasLicenseScope;
use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Winmax4Article extends Model
{
    use HasFactory, SoftDeletes, HasWinmax4Connection, HasLicenseScope;

    const TYPE_PRODUCT = 1;
    const TYPE_SERVICE = 2;

    const NAMES = [
        self::TYPE_PRODUCT => 'Produto',
        self::TYPE_SERVICE => 'Serviço',
    ];

    protected $table = 'winmax4_articles';

    protected $fillable = [
        'code',
        'designation',
        'short_description',
        'is_active',
        'family_id',
        'sub_family_id',
        'sub_sub_family_id',
        'sub_sub_sub_family_id',
        'stock_unit_code',
        'image_url',
        'extras',
        'holds',
        'descriptives',
        'license_id',
        'id_winmax4',
    ];

    public function family()
    {
        return $this->belongsTo(Winmax4Family::class, 'family_id', 'id');
    }

    public function subFamily()
    {
        return $this->belongsTo(Winmax4SubFamily::class, 'sub_family_id', 'id');
    }

    public function subSubFamily()
    {
        return $this->belongsTo(Winmax4SubSubFamily::class, 'sub_sub_family_id', 'id');
    }

    public function saleTaxes()
    {
        return $this->hasMany(Winmax4ArticleSaleTaxes::class, 'article_id', 'id');
    }

    public function purchaseTaxes()
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

    public function details()
    {
        return $this->hasMany(Winmax4DocumentDetail::class, 'article_id', 'id');
    }
}
