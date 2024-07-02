<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Winmax4Settings extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'url',
        'company_code',
        'username',
        'password',
        'n_terminal',
    ];
}
