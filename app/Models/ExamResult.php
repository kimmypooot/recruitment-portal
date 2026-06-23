<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $fillable = [
        'application_id', 'exam_type', 'raw_score', 'max_score', 'encoded_by', 'encoded_at',
    ];

    protected $casts = [
        'raw_score'  => 'decimal:2',
        'max_score'  => 'decimal:2',
        'encoded_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function encodedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function getPercentageAttribute(): float
    {
        return $this->max_score > 0 ? round(($this->raw_score / $this->max_score) * 100, 2) : 0;
    }
}
