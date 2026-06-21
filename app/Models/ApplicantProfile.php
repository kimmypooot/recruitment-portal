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
        'user_id', 'photo_path', 'first_name', 'last_name', 'middle_name',
        'gender', 'civil_status', 'birthday', 'religion',
        'contact_number', 'address',
        'region', 'province', 'city_municipality', 'barangay',
        'mobile_number', 'eligibility', 'eligibility_other',
        'indigenous_group', 'pwd', 'solo_parent',
        'pds_path', 'app_letter_path', 'ipcr_path', 'coe_path', 'tor_path',
        'profile_completed_at',
    ];

    protected $casts = [
        'birthday'             => 'date',
        'profile_completed_at' => 'datetime',
    ];

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
