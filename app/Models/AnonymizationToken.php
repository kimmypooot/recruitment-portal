<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnonymizationToken extends Model
{
    protected $fillable = ['application_id', 'token', 'unmasked_at', 'unmasked_by'];

    protected $casts = [
        'unmasked_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function unmaskedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unmasked_by');
    }

    public function isUnmasked(): bool
    {
        return $this->unmasked_at !== null;
    }
}
