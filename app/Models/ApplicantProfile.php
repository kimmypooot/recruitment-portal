<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicantProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'photo_path',
        'gender', 'civil_status', 'birthday', 'religion',
        'region', 'province', 'city_municipality', 'barangay',
        'mobile_number', 'eligibility', 'eligibility_other',
        'indigenous_group', 'pwd', 'solo_parent',
        'pds_path', 'pds_uploaded_at',
        'app_letter_path', 'app_letter_uploaded_at',
        'ipcr_path', 'ipcr_uploaded_at',
        'coe_path', 'coe_uploaded_at',
        'tor_path', 'tor_uploaded_at',
        'profile_completed_at',
    ];

    protected $appends = ['first_name', 'last_name', 'middle_name', 'suffix'];

    protected $casts = [
        'birthday'               => 'date',
        'profile_completed_at'   => 'datetime',
        'pds_uploaded_at'        => 'datetime',
        'app_letter_uploaded_at' => 'datetime',
        'ipcr_uploaded_at'       => 'datetime',
        'coe_uploaded_at'        => 'datetime',
        'tor_uploaded_at'        => 'datetime',
    ];

    public function getFirstNameAttribute(): ?string { return $this->user?->first_name; }
    public function getLastNameAttribute(): ?string  { return $this->user?->last_name; }
    public function getMiddleNameAttribute(): ?string { return $this->user?->middle_name; }
    public function getSuffixAttribute(): ?string    { return $this->user?->suffix; }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'applicant_id');
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function educationalAttainments(): HasMany
    {
        return $this->hasMany(EducationalAttainment::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    public function isComplete(): bool
    {
        return $this->gender !== null
            && $this->civil_status !== null
            && $this->birthday !== null
            && $this->mobile_number !== null
            && $this->region !== null
            && $this->eligibility !== null;
    }
}
