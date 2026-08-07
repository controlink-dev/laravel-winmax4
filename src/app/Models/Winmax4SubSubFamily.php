<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4SubSubFamily extends Model
{
    use HasFactory, HasWinmax4Connection;

    protected $table = 'winmax4_sub_sub_families';

    protected $fillable = [
        'sub_family_id',
        'code',
        'designation',
    ];

    public function subFamily()
    {
        return $this->belongsTo(Winmax4SubFamily::class, 'sub_family_id');
    }
}
