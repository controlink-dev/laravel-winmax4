<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Closure;
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

        if (config('winmax4.use_license') && !app()->runningInConsole()) {
            static::addGlobalScope(new LicenseScope());
        }
    }

    protected static function addTraitIfNotExists($trait)
    {
        if (!in_array($trait, class_uses(static::class))) {
            static::addDynamicTrait($trait);
        }
    }

    protected static function addDynamicTrait($trait)
    {
        // Create a new class extending the current class with the added trait
        $currentClass = static::class;
        $newClass = eval('return new class extends ' . $currentClass . ' { use ' . $trait . '; };');

        // Copy the new class properties and methods to the current class
        foreach (get_class_vars(get_class($newClass)) as $name => $value) {
            static::${$name} = $value;
        }

        foreach (get_class_methods(get_class($newClass)) as $name) {
            if ($name !== '__construct') {
                static::${$name} = Closure::fromCallable([$newClass, $name]);
            }
        }
    }
}