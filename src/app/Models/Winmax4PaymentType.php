<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasLicenseScope;
use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4PaymentType extends Model
{
    use HasFactory, HasWinmax4Connection, HasLicenseScope;

    protected $table = 'winmax4_payment_types';

    protected $fillable = [
        'license_id',
        'designation',
        'is_active',
        'id_winmax4',
    ];
}
