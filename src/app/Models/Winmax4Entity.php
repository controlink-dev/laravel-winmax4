<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Winmax4Entity extends Model
{
    use HasFactory;

    protected $table = 'winmax4_entities';

    protected $fillable = [
        'license_id',
        'id_winmax4',
        'name',
        'address',
        'code',
        'country_code',
        'email',
        'entity_type',
        'fax',
        'is_active',
        'location',
        'mobile_phone',
        'phone',
        'tax_payer_id',
        'zip_code',
    ];

    protected static function booted()
    {
        parent::boot();

        if (config('winmax4.use_soft_deletes')) {
            static::addTraitIfNotExists(SoftDeletes::class);
        }

        if(config('winmax4.use_license') && !app()->runningInConsole()){
            static::addGlobalScope(new LicenseScope());
        }
    }

    protected static function addTraitIfNotExists($trait)
    {
        $usedTraits = class_uses(static::class);

        if (!in_array($trait, $usedTraits)) {
            // Add the trait
            $usedTraits[] = $trait;

            // Update the model
            $reflection = new \ReflectionClass(static::class);
            $namespace = $reflection->getNamespaceName();
            $model = $reflection->getShortName();
            $modelPath = $reflection->getFileName();
            $modelContent = file_get_contents($modelPath);
            $modelContent = str_replace("use Illuminate\Database\Eloquent\Model;", "use Illuminate\Database\Eloquent\Model;\nuse $trait;", $modelContent);
            file_put_contents($modelPath, $modelContent);
        }
    }
}
