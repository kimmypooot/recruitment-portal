<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QsEvaluation extends Model
{
    protected $table = 'qs_evaluations';

    protected $fillable = [
        'application_id', 'evaluator_id',
        'education_meets', 'experience_meets', 'training_meets', 'eligibility_meets',
        'overall_qualified', 'remarks', 'evaluated_at', 'locked_at',
    ];

    protected $casts = [
        'education_meets'   => 'boolean',
        'experience_meets'  => 'boolean',
        'training_meets'    => 'boolean',
        'eligibility_meets' => 'boolean',
        'overall_qualified' => 'boolean',
        'evaluated_at'      => 'datetime',
        'locked_at'         => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function computeOverallQualified(): bool
    {
        return (bool) $this->education_meets
            && (bool) $this->experience_meets
            && (bool) $this->training_meets
            && (bool) $this->eligibility_meets;
    }
}
