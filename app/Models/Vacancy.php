<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacancy extends Model
{
    use HasFactory, SoftDeletes;

    // Keep in sync with resources/js/constants/officesOfAssignment.js
    public const CSC_FIELD_OFFICES = [
        'CSC Field Office - Leyte I',
        'CSC Field Office - Leyte II',
        'CSC Field Office - Southern Leyte',
        'CSC Field Office - Biliran',
        'CSC Satellite Office - Western Leyte',
        'CSC Field Office - Samar',
        'CSC Field Office - Eastern Samar',
        'CSC Field Office - Northern Samar',
    ];

    public const REGIONAL_SUPPORT_UNITS = [
        'Office of the Regional Director (ORD)',
        'Human Resource Division (HRD)',
        'Management Resource Division (MSD)',
        'Public Assistance and Liaison Division (PALD)',
        'Policies and Systems Evaluation Division (PSED)',
        'Examination Services Division (ESD)',
        'Legal Services Division (LSD)',
    ];

    public static function placesOfAssignment(): array
    {
        return [...self::CSC_FIELD_OFFICES, ...self::REGIONAL_SUPPORT_UNITS];
    }

    protected $fillable = [
        'position_title',
        'plantilla_no',
        'salary_grade',
        'monthly_salary',
        'position_level',
        'is_anticipated_vacancy',
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

    protected $casts = [
        'salary_grade' => 'integer',
        'monthly_salary' => 'decimal:2',
        'is_anticipated_vacancy' => 'boolean',
        'published_at' => 'datetime',
        'deadline_at' => 'datetime',
        'deleted_at' => 'datetime',
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

    // Final deliberation results for this vacancy
    public function deliberationResults(): HasMany
    {
        return $this->hasMany(DeliberationResult::class);
    }

    public function competencies(): HasMany
    {
        return $this->hasMany(VacancyCompetency::class)->with('competency');
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
