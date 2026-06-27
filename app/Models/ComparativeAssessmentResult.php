<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComparativeAssessmentResult extends Model
{
    protected $fillable = [
        'vacancy_id', 'file_path', 'generated_by', 'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
