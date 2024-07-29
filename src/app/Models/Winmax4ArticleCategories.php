<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4ArticleCategories extends Model
{
    use HasFactory;

    protected $table = 'winmax4_articles_categories';

    protected $fillable = [
        'article_id',
        'code',
        'sub_categories',
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
