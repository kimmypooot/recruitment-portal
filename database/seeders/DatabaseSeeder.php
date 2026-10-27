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

        // ── HRMPSB Members (role = 'hrmpsb') ─────────────────────────────────
        $hrmpsb = collect([
            ['first_name' => 'Rodrigo',   'last_name' => 'Dela Peña',  'middle_name' => 'Viernes',    'email' => 'rodrigo.delapena@csc.gov.ph',   'suffix' => null],
            ['first_name' => 'Carmela',   'last_name' => 'Villanueva', 'middle_name' => 'Ramos',      'email' => 'carmela.villanueva@csc.gov.ph', 'suffix' => null],
            ['first_name' => 'Benjamin',  'last_name' => 'Oquendo',    'middle_name' => 'Tan',        'email' => 'benjamin.oquendo@csc.gov.ph',   'suffix' => 'Jr.'],
            ['first_name' => 'Luzviminda', 'last_name' => 'Cabrera',    'middle_name' => 'Ferrer',     'email' => 'luzviminda.cabrera@csc.gov.ph', 'suffix' => null],
            ['first_name' => 'Arsenio',   'last_name' => 'Macaraig',   'middle_name' => 'Quiambao',   'email' => 'arsenio.macaraig@csc.gov.ph',   'suffix' => null],
            ['first_name' => 'Florencia', 'last_name' => 'Espiritu',   'middle_name' => 'Laurel',     'email' => 'florencia.espiritu@csc.gov.ph', 'suffix' => null],
            ['first_name' => 'Eduardo',   'last_name' => 'Bautista',   'middle_name' => 'Chua',       'email' => 'eduardo.bautista@csc.gov.ph',   'suffix' => 'Sr.'],
            ['first_name' => 'Natividad', 'last_name' => 'Soriano',    'middle_name' => 'Aguilar',    'email' => 'natividad.soriano@csc.gov.ph',  'suffix' => null],
            ['first_name' => 'Renato',    'last_name' => 'Perez',      'middle_name' => 'Dizon',      'email' => 'renato.perez@csc.gov.ph',       'suffix' => null],
        ])->map(fn ($d) => User::create([
            'first_name' => $d['first_name'],
            'last_name' => $d['last_name'],
            'middle_name' => $d['middle_name'],
            'suffix' => $d['suffix'],
            'email' => $d['email'],
            'password' => Hash::make('Password123'),
            'role' => 'hrmpsb',
            'email_verified_at' => now(),
        ]));

        // ── HRMPSB Compositions ───────────────────────────────────────────────
        // $hrmpsb[0] = Chairperson (Dela Peña)
        // $hrmpsb[1] = Secretariat (Villanueva) — can access admin module
        // $hrmpsb[2] = Appointing Authority (Oquendo)
        // $hrmpsb[3] = Head of Unit — MSD/PALD (Cabrera) — set via place_of_assignment_heads (dynamic)
        // $hrmpsb[4] = Head of Unit — PSED/ESD (Macaraig) — set via place_of_assignment_heads (dynamic)
        // $hrmpsb[5] = HR Chief (Espiritu) — can access admin module
        // $hrmpsb[6] = Head of Unit — Field Offices/LSD (Bautista) — set via place_of_assignment_heads (dynamic)
        // $hrmpsb[7] = PINTIG Rep 1st Level (Soriano)
        // $hrmpsb[8] = PINTIG Rep 2nd Level (Perez)

        // NOTE: the Director II Representative and Division Chief Representative
        // roles have been retired — the Head of the Division/Unit where the
        // vacancy exists now fills that seat, resolved dynamically per-vacancy
        // via place_of_assignment_heads rather than seeded as a global composition.
        $staticRoles = [
            ['user' => $hrmpsb[0], 'role' => 'chairperson',                   'type' => 'principal'],
            ['user' => $hrmpsb[1], 'role' => 'secretariat',                   'type' => 'principal'],
            ['user' => $hrmpsb[2], 'role' => 'appointing-authority',          'type' => 'principal'],
            ['user' => $hrmpsb[5], 'role' => 'hr-chief',                      'type' => 'principal'],
            ['user' => $hrmpsb[7], 'role' => 'pintig-representative-1st',     'type' => 'principal'],
            ['user' => $hrmpsb[8], 'role' => 'pintig-representative-2nd',     'type' => 'principal'],
        ];

        foreach ($staticRoles as $comp) {
            HrmpsbComposition::create([
                'user_id' => $comp['user']->id,
                'hrmpsb_role' => $comp['role'],
                'member_type' => $comp['type'],
                'is_active' => true,
                'assigned_by' => $admin->id,
                'assigned_at' => now()->subDays(30),
            ]);
        }

        // ── Place of Assignment Heads (dynamic Head of Unit) ─────────────────
        // Eduardo Bautista ($hrmpsb[6]) is designated as Head of Unit for
        // all CSC Field Offices.
        $fieldOffices = Vacancy::CSC_FIELD_OFFICES;
        foreach ($fieldOffices as $office) {
            PlaceOfAssignmentHead::create([
                'place_of_assignment' => $office,
                'user_id' => $hrmpsb[6]->id,
                'assigned_by' => $admin->id,
            ]);
        }

        // Regional Support Units (excluding HRD — the HR Chief is already a
        // mandatory HRMPSB member — and ORD, whose head is the Appointing
        // Authority, for whom a Head of Unit designation does not apply).
        $rsuHeads = [
            'Management Resource Division (MSD)' => $hrmpsb[3]->id, // Cabrera
            'Public Assistance and Liaison Division (PALD)' => $hrmpsb[3]->id, // Cabrera
            'Policies and Systems Evaluation Division (PSED)' => $hrmpsb[4]->id, // Macaraig
            'Examination Services Division (ESD)' => $hrmpsb[4]->id, // Macaraig
            'Legal Services Division (LSD)' => $hrmpsb[6]->id, // Bautista
        ];

        foreach ($rsuHeads as $rsu => $userId) {
            PlaceOfAssignmentHead::create([
                'place_of_assignment' => $rsu,
                'user_id' => $userId,
                'assigned_by' => $admin->id,
            ]);
        }

        $secretariat = $hrmpsb[1]; // Villanueva — encodes most assessments
        $chairperson = $hrmpsb[0]; // Dela Peña
        $pintig1 = $hrmpsb[7]; // Soriano
        $pintig2 = $hrmpsb[8]; // Perez
        $hrChief = $hrmpsb[5]; // Espiritu
        $headOfUnit = $hrmpsb[6]; // Bautista

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
