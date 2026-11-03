<?php

namespace Database\Seeders;

use App\Models\AnonymizationToken;
use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\AppointingAuthorityDecision;
use App\Models\BeiRating;
use App\Models\CbweRating;
use App\Models\DeliberationResult;
use App\Models\ExamResult;
use App\Models\HrmpsbComposition;
use App\Models\PlaceOfAssignmentHead;
use App\Models\PreAssessment;
use App\Models\QsEvaluation;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyCompetency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CompetencySeeder::class);
        $this->call(EmailTemplateSeeder::class);

        // ── Staff Users ───────────────────────────────────────────────────────
        $admin = User::create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@csc.gov.ph',
            'password' => Hash::make('Password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $hrOfficer = User::create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'middle_name' => 'Buenaventura',
            'email' => 'maria.santos@csc.gov.ph',
            'password' => Hash::make('Password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // ── HRMPSB ────────────────────────────────────────────────────────────
        // No board is seeded. The members are CSC employees, signed in from IMIS
        // (role `hrmpsb`); an administrator then gives each one a board role
        // under Admin → HRMPSB (hrmpsb_compositions), and sets the Head of Unit
        // of each place of assignment (place_of_assignment_heads).

        // ── Applicant Users ───────────────────────────────────────────────────
        $applicantData = [
            ['first_name' => 'Ana',        'last_name' => 'Dela Cruz',   'middle_name' => 'Santos',     'email' => 'ana.delacruz@gmail.com',     'gender' => 'Female', 'civil_status' => 'Single',  'birthday' => '1995-03-14', 'eligibility' => 'Career Service Professional',           'mobile_number' => '09171234567'],
            ['first_name' => 'Carlo',      'last_name' => 'Mendoza',     'middle_name' => 'Bautista',   'email' => 'carlo.mendoza@gmail.com',    'gender' => 'Male',   'civil_status' => 'Married', 'birthday' => '1992-07-22', 'eligibility' => 'Career Service Professional',           'mobile_number' => '09281234568'],
            ['first_name' => 'Liza',       'last_name' => 'Reyes',       'middle_name' => 'Cruz',       'email' => 'liza.reyes@gmail.com',       'gender' => 'Female', 'civil_status' => 'Single',  'birthday' => '1997-11-08', 'eligibility' => 'Career Service Professional',           'mobile_number' => '09391234569'],
            ['first_name' => 'Ramon',      'last_name' => 'Garcia',      'middle_name' => 'Torres',     'email' => 'ramon.garcia@gmail.com',     'gender' => 'Male',   'civil_status' => 'Married', 'birthday' => '1990-05-30', 'eligibility' => 'Career Service Professional',           'mobile_number' => '09501234560'],
            ['first_name' => 'Patricia',   'last_name' => 'Lim',         'middle_name' => 'Ong',        'email' => 'patricia.lim@gmail.com',     'gender' => 'Female', 'civil_status' => 'Single',  'birthday' => '1998-09-17', 'eligibility' => 'Career Service Professional',           'mobile_number' => '09611234561'],
            ['first_name' => 'Marco',      'last_name' => 'Fernandez',   'middle_name' => 'Dela Vega',  'email' => 'marco.fernandez@gmail.com',  'gender' => 'Male',   'civil_status' => 'Single',  'birthday' => '1993-01-25', 'eligibility' => 'Career Service Sub-Professional',       'mobile_number' => '09721234562'],
            ['first_name' => 'Josephine',  'last_name' => 'Aquino',      'middle_name' => 'Mercado',    'email' => 'josephine.aquino@gmail.com', 'gender' => 'Female', 'civil_status' => 'Married', 'birthday' => '1988-06-12', 'eligibility' => 'Career Service Professional',           'mobile_number' => '09831234563'],
        ];

        $applicants = collect();
        $profiles = collect();

        foreach ($applicantData as $d) {
            $user = User::create([
                'first_name' => $d['first_name'],
                'last_name' => $d['last_name'],
                'middle_name' => $d['middle_name'],
                'email' => $d['email'],
                'password' => Hash::make('Password123'),
                'role' => 'applicant',
                'email_verified_at' => now(),
            ]);
            $applicants->push($user);

            $profiles->push(ApplicantProfile::create([
                'user_id' => $user->id,
                'gender' => $d['gender'],
                'civil_status' => $d['civil_status'],
                'birthday' => $d['birthday'],
                'religion' => 'Roman Catholic',
                'region' => 'Region VIII',
                'province' => 'Leyte',
                'city_municipality' => 'Tacloban City',
                'barangay' => 'Barangay 1',
                'mobile_number' => $d['mobile_number'],
                'eligibility' => $d['eligibility'],
                'indigenous_group' => 'No',
                'pwd' => 'No',
                'solo_parent' => 'No',
                'profile_completed_at' => now()->subDays(20),
            ]));
        }

        // ── Audit Logs ────────────────────────────────────────────────────────
        $auditEntries = [
            [$admin->id,     'created',   $hrOfficer, Carbon::now()->subDays(30)],
        ];

        foreach ($auditEntries as [$userId, $action, $model, $createdAt]) {
            DB::table('audit_logs')->insert([
                'user_id' => $userId,
                'action' => $action,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
