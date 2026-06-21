<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeiRating extends Model
{
    // CSC standard competencies for BEI
    public const COMPETENCIES = [
        'professionalism_ethics'     => 'Professionalism and Ethics',
        'results_focus'              => 'Results Focus',
        'teamwork_cooperation'       => 'Teamwork and Cooperation',
        'creative_problem_solving'   => 'Creative Problem Solving',
        'public_service_orientation' => 'Public Service Orientation',
    ];

    protected $fillable = [
        'application_id', 'evaluator_id', 'competency_scores', 'total_rating', 'remarks', 'rated_at', 'locked_at',
    ];

    protected $casts = [
        'competency_scores' => 'array',
        'total_rating'      => 'decimal:2',
        'rated_at'          => 'datetime',
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

    public function computeTotalRating(): float
    {
        $scores = array_values(array_filter($this->competency_scores ?? []));
        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;
    }
}
