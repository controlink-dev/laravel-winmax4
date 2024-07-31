<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4Family extends Model
{
    use HasFactory;

    protected $table = 'winmax4_families';

    protected $fillable = [
        'license_id',
        'code',
        'designation',
        'is_active',
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

    public function subFamilies()
    {
        return $this->hasMany(Winmax4SubFamily::class, 'family_id');
    }
}
