<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApplicantProfile extends Model
{
    use HasFactory;

    /** Every relation the CS Form No. 212 generator needs, for eager loading. */
    public const PDS_RELATIONS = [
        'user',
        'educationalAttainments',
        'workExperiences',
        'trainings',
        'familyBackground',
        'children',
        'civilServiceEligibilities',
        'voluntaryWorks',
        'otherInfos',
        'references',
        'declaration',
    ];

    protected $fillable = [
        'user_id', 'photo_path',
        'gender', 'civil_status', 'civil_status_other', 'birthday', 'religion',
        'region', 'province', 'city_municipality', 'barangay',
        'mobile_number', 'eligibility', 'eligibility_other',
        'indigenous_group', 'pwd', 'solo_parent',
        'pds_path', 'pds_uploaded_at', 'pds_generated_path',
        'app_letter_path', 'app_letter_uploaded_at',
        'ipcr_path', 'ipcr_uploaded_at',
        'coe_path', 'coe_uploaded_at',
        'tor_path', 'tor_uploaded_at',
        'profile_completed_at',

        // CS Form No. 212 (Revised 2026), Section I
        'place_of_birth', 'height_m', 'weight_kg', 'blood_type',
        'citizenship', 'citizenship_basis', 'dual_citizenship_country',
        'gsis_id_no', 'pagibig_id_no', 'philhealth_no', 'sss_no', 'tin_no',
        'umid_id_no', 'philsys_number', 'agency_employee_no',
        'telephone_no',
        'res_house_block_lot', 'res_street', 'res_subdivision', 'res_barangay',
        'res_city_municipality', 'res_province', 'res_zip_code',
        'perm_house_block_lot', 'perm_street', 'perm_subdivision', 'perm_barangay',
        'perm_city_municipality', 'perm_province', 'perm_zip_code',
        'pds_generated_at',
    ];

    protected $appends = ['first_name', 'last_name', 'middle_name', 'suffix'];

    protected $casts = [
        'birthday'               => 'date',
        'height_m'               => 'decimal:2',
        'weight_kg'              => 'decimal:2',
        'pds_generated_at'       => 'datetime',
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

    // --- CS Form No. 212 (Revised 2026) -------------------------------------

    /** Section II items 22, 24, 25. */
    public function familyBackground(): HasOne
    {
        return $this->hasOne(PdsFamilyBackground::class);
    }

    /** Section II item 23. */
    public function children(): HasMany
    {
        return $this->hasMany(PdsChild::class)->orderBy('sort_order');
    }

    /** Section IV item 27. */
    public function civilServiceEligibilities(): HasMany
    {
        return $this->hasMany(CivilServiceEligibility::class)->orderBy('sort_order');
    }

    /** Section VI item 29. */
    public function voluntaryWorks(): HasMany
    {
        return $this->hasMany(VoluntaryWork::class)->orderBy('sort_order');
    }

    /** Section VIII items 31-33. */
    public function otherInfos(): HasMany
    {
        return $this->hasMany(PdsOtherInfo::class)->orderBy('sort_order');
    }

    /** Page 4 item 41. */
    public function references(): HasMany
    {
        return $this->hasMany(PdsReference::class)->orderBy('sort_order');
    }

    /** Page 4 items 34-42. */
    public function declaration(): HasOne
    {
        return $this->hasOne(PdsDeclaration::class);
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

    /**
     * Required documents to submit an application: PDS, Application Letter,
     * Certificate of Eligibility, and Transcript of Records. IPCR is optional
     * (mirrors the required flags in CompleteProfile.vue's docFields).
     *
     * The PDS is satisfied either by a copy the applicant uploaded or by one
     * generated from their profile — generation is gated on the form being
     * complete, so a generated PDS is at least as trustworthy as an upload.
     */
    public function hasRequiredDocuments(): bool
    {
        return $this->hasPds()
            && $this->app_letter_path !== null
            && $this->coe_path !== null
            && $this->tor_path !== null;
    }

    /** True when a PDS is on file, however it got there. */
    public function hasPds(): bool
    {
        return $this->pds_path !== null || $this->pds_generated_path !== null;
    }
}
