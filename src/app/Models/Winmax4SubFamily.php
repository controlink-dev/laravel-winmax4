<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4SubFamily extends Model
{
    use HasFactory;

    protected $table = 'winmax4_sub_families';

    protected $fillable = [
        'family_id',
        'code',
        'designation',
    ];

    public function family()
    {
        return $this->belongsTo(Winmax4Family::class, 'family_id');
    }

    public function subSubFamilies()
    {
        return $this->hasMany(Winmax4SubSubFamily::class, 'sub_family_id');
    }
}
