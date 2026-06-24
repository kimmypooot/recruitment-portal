<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    // Every column that is safe to mass-assign (Application::create([...]))
    protected $fillable = [
        'vacancy_id',
        'applicant_id',
        'status',
        'submitted_at',
        'reviewed_at',
        'remarks',
    ];

    // Tell Laravel which columns to auto-cast to PHP types
    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    // The vacancy this application was submitted for
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    // The applicant's profile (name, birthdate, education, etc.)
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(ApplicantProfile::class, 'applicant_id');
    }

    // All uploaded documents (PDS, certificates, diploma, etc.)
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // Exam schedule slot, if one has been assigned
    public function examSchedule(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }

    // Interview schedule slot, if one has been assigned
    public function interviewSchedule(): HasMany
    {
        return $this->hasMany(InterviewSchedule::class);
    }

    public function anonymizationToken(): HasOne
    {
        return $this->hasOne(AnonymizationToken::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function beiRatings(): HasMany
    {
        return $this->hasMany(BeiRating::class);
    }

    public function deliberationResults(): HasMany
    {
        return $this->hasMany(DeliberationResult::class);
    }

    public function qsEvaluations(): HasMany
    {
        return $this->hasMany(QsEvaluation::class);
    }

    public function csforms(): HasMany
    {
        return $this->hasMany(CsForm::class);
    }

    public function submissionTracking(): HasOne
    {
        return $this->hasOne(SubmissionTracking::class);
    }

    public function preAssessment(): HasOne
    {
        return $this->hasOne(PreAssessment::class);
    }

    // ── Query Scopes (reusable filters) ───────────────────────────────────

    // Application::status('submitted')->get()
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Application::pendingReview()->get()
    public function scopePendingReview($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }
}