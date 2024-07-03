<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Winmax4Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'designation',
        'is_active',
        'article_decimals',
        'document_decimals',
    ];

    protected static function booted()
    {
        if(config('winmax4.use_license')){
            static::addGlobalScope(new LicenseScope());
        }
    }
}
