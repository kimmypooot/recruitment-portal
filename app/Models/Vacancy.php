<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacancy extends Model
{
    use HasFactory, SoftDeletes;

    // Every column that is safe to mass-assign (Vacancy::create([...]))
    protected $fillable = [
        'position_title',
        'item_number',
        'salary_grade',
        'plantilla_number',
        'place_of_assignment',
        'education_req',
        'experience_req',
        'training_req',
        'eligibility_req',
        'status',
        'posted_by',
        'published_at',
        'deadline_at',
    ];

    // Tell Laravel which columns to auto-cast to PHP types
    protected $casts = [
        'salary_grade' => 'integer',
        'published_at' => 'datetime',
        'deadline_at'  => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    // The HR user who created this vacancy
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // All applications submitted for this vacancy
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // Compliance deadlines tracked for this vacancy
    public function complianceDeadlines(): HasMany
    {
        return $this->hasMany(ComplianceDeadline::class);
    }

    // Final deliberation results for this vacancy
    public function deliberationResults(): HasMany
    {
        return $this->hasMany(DeliberationResult::class);
    }

    // ── Query Scopes (reusable filters) ───────────────────────────────────

    // Vacancy::published()->get()
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Vacancy::open()->get()  — published AND deadline not yet passed
    public function scopeOpen($query)
    {
        return $query->where('status', 'published')
                     ->where('deadline_at', '>=', now());
    }
}