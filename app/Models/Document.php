<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'application_id', 'document_type', 'original_filename',
        'stored_filename', 'file_path', 'file_size', 'mime_type', 'verified_at',
    ];

    protected $casts = ['verified_at' => 'datetime'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
