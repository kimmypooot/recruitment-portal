<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ApplicantProfile;
use App\Models\EducationalAttainment;
use App\Models\Training;
use App\Models\User;
use App\Models\WorkExperience;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Fills an IMIS employee's applicant profile from their verified PDS and 201 file,
 * so they review it instead of typing it again. It only ever adds: a field the
 * applicant already filled is kept, a list row that already exists is not
 * duplicated, a document slot that already has a file is not replaced. Running
 * it again later just picks up what is new in IMIS.
 *
 * What an application is judged on is what is in the profile when it is
 * submitted, so IMIS changing afterwards does not change a submitted application.
 */
class ImisProfileImporter
{
    /** The 201-file types we take, and the profile document slot each one fills. */
    private const DOCUMENT_SLOTS = [
        'personal_data_sheet' => ['path' => 'pds_path', 'at' => 'pds_uploaded_at', 'label' => 'PDS'],
        'transcript_of_records' => ['path' => 'tor_path', 'at' => 'tor_uploaded_at', 'label' => 'Transcript of Records'],
        'performance_rating' => ['path' => 'ipcr_path', 'at' => 'ipcr_uploaded_at', 'label' => 'IPCR'],
    ];

    /** IMIS education level => the value the profile form uses. */
    private const EDUCATION_LEVELS = [
        'elementary' => 'Elementary',
        'secondary' => 'Secondary',
        'vocational' => 'Vocational / Trade Course',
        'college' => 'College',
        'graduate' => 'Graduate Studies',
    ];

    private const ELIGIBILITY_OPTIONS = [
        'N/A', 'Career Service Professional Eligibility', 'Career Service Subprofessional Eligibility',
        'RA 1080 (Bar/Board Exam)', 'PD 907 (Honor Graduate Eligibility)', 'RA 9418 (Eligibility for NP/EO)',
        'RA 10817', 'Contractual Service Eligibility', 'Barangay Official Eligibility',
        'Honor Graduate Eligibility', 'Skill Eligibility', 'Sanggunian Eligibility', 'Veteran Preference Rating',
    ];

    public function __construct(private readonly ImisEmployeeRecords $records) {}

    /**
     * @return array{verified: bool, fields: int, education: int, work_experience: int, trainings: int, documents: list<string>, notes: list<string>}
     *
     * @throws DomainException when the person is not linked to IMIS or the link is off
     * @throws \RuntimeException when IMIS cannot provide the records
     */
    public function import(User $user): array
    {
        if ($user->imis_user_id === null) {
            throw new DomainException('Your account is not linked to IMIS.');
        }

        if (! $this->records->enabled()) {
            throw new DomainException('Importing from IMIS is not available right now.');
        }

        $pds = $this->records->pds($user->imis_user_id);
        $documents = $this->records->documents($user->imis_user_id);

        $profile = ApplicantProfile::firstOrCreate(['user_id' => $user->id]);
        $summary = ['verified' => $pds['source'] !== null, 'fields' => 0, 'education' => 0, 'work_experience' => 0, 'trainings' => 0, 'documents' => [], 'notes' => []];

        DB::transaction(function () use ($profile, $pds, &$summary) {
            $summary['fields'] = $this->fillFields($profile, $pds);
            $summary['education'] = $this->addEducation($profile, $pds['education'] ?? []);
            $summary['work_experience'] = $this->addWorkExperience($profile, $pds['work_experience'] ?? []);
            $summary['trainings'] = $this->addTrainings($profile, $pds['learning_development'] ?? []);
        });

        $this->importDocuments($user, $profile, $documents, $summary);

        $profile->forceFill(['imis_imported_at' => now()])->save();

        if (! $summary['verified']) {
            $summary['notes'][] = 'IMIS has no verified PDS for you yet, so only your name, trainings and documents could be imported.';
        }

        return $summary;
    }

    /** @return int how many profile fields were filled */
    private function fillFields(ApplicantProfile $profile, array $pds): int
    {
        $identity = $pds['identity'] ?? [];
        $p = $pds['personal'] ?? [];
        $eligibility = $pds['eligibility'] ?? [];

        // Only columns the migrations define. The live database has more PDS columns
        // (government IDs, address parts) that no migration here creates, so a fresh
        // install lacks them; they are left out until a migration adds them.
        $values = [
            'gender' => ['male' => 'Male', 'female' => 'Female'][strtolower((string) ($identity['sex'] ?? ''))] ?? null,
            'birthday' => $identity['birthday'] ?? null,
            'civil_status' => ['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'separated' => 'Separated'][strtolower((string) ($p['civil_status'] ?? ''))] ?? null,
            'mobile_number' => $p['mobile_no'] ?? null,
            'province' => $p['residential_province'] ?? null,
            'city_municipality' => $p['residential_city'] ?? null,
            'barangay' => $p['residential_barangay'] ?? null,
        ];

        if ($eligibility !== []) {
            [$values['eligibility'], $values['eligibility_other']] = $this->eligibility((string) ($eligibility[0]['eligibility'] ?? ''));
        }

        $filled = 0;

        foreach ($values as $attribute => $value) {
            if ($value === null || $value === '' || ! $this->isBlank($profile->getAttribute($attribute))) {
                continue;
            }

            $profile->forceFill([$attribute => $value]);
            $filled++;
        }

        $profile->save();

        return $filled;
    }

    /** @return array{0: ?string, 1: ?string} the select value and, for "Others", the text */
    private function eligibility(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            return [null, null];
        }

        foreach (self::ELIGIBILITY_OPTIONS as $option) {
            if (Str::lower($option) === Str::lower($name) || Str::startsWith(Str::lower($option), Str::lower($name))) {
                return [$option, null];
            }
        }

        return ['Others', Str::limit($name, 250, '')];
    }

    private function addEducation(ApplicantProfile $profile, array $rows): int
    {
        $have = $profile->educationalAttainments()->get()->map(fn ($e) => $this->key($e->level, $e->school_name))->all();
        $added = 0;

        foreach ($rows as $row) {
            $level = self::EDUCATION_LEVELS[strtolower((string) ($row['level'] ?? ''))] ?? null;
            $school = trim((string) ($row['school'] ?? ''));

            if ($level === null || $school === '' || in_array($key = $this->key($level, $school), $have, true)) {
                continue;
            }

            EducationalAttainment::create([
                'applicant_profile_id' => $profile->id,
                'level' => $level,
                'school_name' => $school,
                'degree_course' => $row['degree'] ?? null,
                'period_from' => $row['attendance_from'] ?? null,
                'period_to' => $row['attendance_to'] ?? null,
                'units_earned' => $row['highest_level_units'] ?? null,
                'year_graduated' => $row['year_graduated'] ?? null,
                'honors' => $row['honors'] ?? null,
            ]);
            $have[] = $key;
            $added++;
        }

        return $added;
    }

    private function addWorkExperience(ApplicantProfile $profile, array $rows): int
    {
        $have = $profile->workExperiences()->get()->map(fn ($w) => $this->key($w->position_title, $w->department_agency, $w->date_from?->toDateString()))->all();
        $added = 0;

        foreach ($rows as $row) {
            $title = trim((string) ($row['position_title'] ?? ''));
            $agency = trim((string) ($row['agency'] ?? ''));

            if ($title === '' || $agency === '' || in_array($key = $this->key($title, $agency, $row['date_from'] ?? null), $have, true)) {
                continue;
            }

            WorkExperience::create([
                'applicant_profile_id' => $profile->id,
                'position_title' => $title,
                'department_agency' => $agency,
                'monthly_salary' => $row['monthly_salary'] ?? null,
                'salary_grade' => $row['salary_grade'] ?? null,
                'appointment_status' => $row['appointment_status'] ?? null,
                'government_service' => (bool) ($row['is_government'] ?? false),
                'date_from' => $row['date_from'] ?? null,
                'date_to' => $row['date_to'] ?? null,
                'is_present' => ($row['date_to'] ?? null) === null,
            ]);
            $have[] = $key;
            $added++;
        }

        return $added;
    }

    private function addTrainings(ApplicantProfile $profile, array $rows): int
    {
        $have = $profile->trainings()->get()->map(fn ($t) => $this->key($t->title, $t->date_from?->toDateString()))->all();
        $added = 0;

        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));

            if ($title === '' || in_array($key = $this->key($title, $row['date_from'] ?? null), $have, true)) {
                continue;
            }

            Training::create([
                'applicant_profile_id' => $profile->id,
                'title' => Str::limit($title, 250, ''),
                'date_from' => $row['date_from'] ?? null,
                'date_to' => $row['date_to'] ?? null,
                'hours' => $row['hours'] ?? null,
                'ld_type' => isset($row['ld_type']) ? ucfirst((string) $row['ld_type']) : null,
                'conducted_by' => $row['sponsor'] ?? null,
            ]);
            $have[] = $key;
            $added++;
        }

        return $added;
    }

    /**
     * Copies each 201-file document into an empty profile slot. A copy, so
     * reviewers (who are not IMIS users) can open it and it stays as submitted
     * if the 201 file changes. Only PDFs, which is all the profile accepts, and
     * only if the bytes match the hash IMIS recorded.
     */
    private function importDocuments(User $user, ApplicantProfile $profile, array $documents, array &$summary): void
    {
        foreach (self::DOCUMENT_SLOTS as $type => $slot) {
            if (! $this->isBlank($profile->getAttribute($slot['path']))) {
                continue;
            }

            $document = collect($documents)->firstWhere('document_type', $type);

            if ($document === null) {
                continue;
            }

            if (($document['mime_type'] ?? null) !== 'application/pdf') {
                $summary['notes'][] = "{$slot['label']} in your 201 file is not a PDF, so please upload it yourself.";

                continue;
            }

            try {
                $bytes = $this->records->file($user->imis_user_id, (int) $document['id']);
            } catch (Throwable $e) {
                Log::warning('IMIS 201-file download failed', ['type' => $type, 'message' => $e->getMessage()]);
                $summary['notes'][] = "{$slot['label']} could not be copied from your 201 file.";

                continue;
            }

            if (! hash_equals((string) ($document['sha256'] ?? ''), hash('sha256', $bytes))) {
                $summary['notes'][] = "{$slot['label']} from your 201 file failed its integrity check, so it was not imported.";

                continue;
            }

            $path = "profile-documents/{$user->id}/".Str::uuid().'.pdf';
            Storage::disk('local')->put($path, $bytes);
            $profile->forceFill([$slot['path'] => $path, $slot['at'] => now()])->save();
            $summary['documents'][] = $slot['label'];
        }
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    private function key(mixed ...$parts): string
    {
        return Str::lower(implode('|', array_map(fn ($p) => trim((string) $p), $parts)));
    }
}
