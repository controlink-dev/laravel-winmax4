<?php

namespace Controlink\LaravelWinmax4\app\Models;

use Controlink\LaravelWinmax4\app\Models\Scopes\LicenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winmax4DocumentRelation extends Model
{
    use HasFactory;

    protected $table = 'winmax4_documents_relation';

    protected $fillable = [
        'document_id',
        'related_document_id',
    ];

    public function document()
    {
        return $this->belongsTo(Winmax4Document::class, 'document_id');
    }
    public function relatedDocument()
    {
        return $this->belongsTo(Winmax4Document::class, 'related_document_id');
    }
}
