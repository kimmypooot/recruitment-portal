<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacancyCompetency extends Model
{
    protected $fillable = [
        'vacancy_id',
        'competency_key',
        'competency_level',
    ];

    protected $casts = [
        'competency_level' => 'integer',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class, 'competency_key', 'competency_key');
    }
}
