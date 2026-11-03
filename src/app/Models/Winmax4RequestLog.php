<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasLicenseScope;
use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Model;

class Winmax4RequestLog extends Model
{
    use HasWinmax4Connection, HasLicenseScope;

    protected $table = 'winmax4_request_logs';

    protected $guarded = ['id'];

    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
    ];
}
