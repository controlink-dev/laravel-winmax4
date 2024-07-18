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

    // A protected static function that adds a trait to a class if it doesn't already exist
    protected static function addTraitIfNotExists($trait)
    {
        // Get an array of all traits used by the current class and its parents
        $usedTraits = class_uses_recursive(static::class);

        // Check if the trait is not already in the list of used traits
        if (!in_array($trait, $usedTraits)) {
            // If not, add it to the list
            $usedTraits[] = $trait;

            // Create a reflection of the current class to get its file path
            $reflection = new \ReflectionClass(static::class);
            $modelPath = $reflection->getFileName();
            // Read the content of the class file
            $modelContent = file_get_contents($modelPath);

            // Get the short name of the trait (without namespace)
            $traitShortName = (new \ReflectionClass($trait))->getShortName();
            // Check if the trait is not already being used in the class file
            if (strpos($modelContent, "use $trait;") === false && strpos($modelContent, "use $traitShortName;") === false) {
                // If not, add the trait use statement after the Eloquent Model use statement
                $modelContent = str_replace("use Illuminate\Database\Eloquent\Model;", "use Illuminate\Database\Eloquent\Model;\nuse $trait;", $modelContent);
                // Write the updated content back to the class file
                file_put_contents($modelPath, $modelContent);
            }
        }
    }


}
