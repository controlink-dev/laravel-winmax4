<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasLicenseScope;
use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4Terminal extends Model
{
    use HasFactory, HasWinmax4Connection, HasLicenseScope;

    protected $table = 'winmax4_terminals';

    protected $fillable = [
        'license_id',
        'code',
        'designation',
        'is_active',
        'service_zone_code',
        'price_type',
        'ask_entity_info',
        'pos_default_table_number',
        'pos_printer_is_local',
    ];
}
