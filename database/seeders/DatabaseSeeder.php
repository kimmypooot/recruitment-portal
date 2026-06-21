<?php

namespace Database\Seeders;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ─────────────────────────────────────────────────────────────
        $admin = User::create([
            'name'              => 'Admin User',
            'email'             => 'admin@csc.gov.ph',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        $hrManager = User::create([
            'name'              => 'Maria Santos',
            'email'             => 'hr.manager@csc.gov.ph',
            'password'          => Hash::make('password'),
            'role'              => 'hr-manager',
            'email_verified_at' => now(),
        ]);

        $hrOfficer = User::create([
            'name'              => 'Jose Reyes',
            'email'             => 'hr.officer@csc.gov.ph',
            'password'          => Hash::make('password'),
            'role'              => 'hr-officer',
            'email_verified_at' => now(),
        ]);

        $applicants = collect([
            ['name' => 'Ana Dela Cruz',     'email' => 'ana.delacruz@gmail.com'],
            ['name' => 'Carlo Mendoza',     'email' => 'carlo.mendoza@gmail.com'],
            ['name' => 'Liza Reyes',        'email' => 'liza.reyes@gmail.com'],
            ['name' => 'Ramon Garcia',      'email' => 'ramon.garcia@gmail.com'],
            ['name' => 'Patricia Lim',      'email' => 'patricia.lim@gmail.com'],
            ['name' => 'Marco Fernandez',   'email' => 'marco.fernandez@gmail.com'],
        ])->map(fn ($data) => User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make('password'),
            'role'              => 'applicant',
            'email_verified_at' => now(),
        ]));

        // ── Applicant Profiles ────────────────────────────────────────────────
        $profileData = [
            ['first_name' => 'Ana',     'last_name' => 'Dela Cruz',   'middle_name' => 'Santos',    'birthdate' => '1995-03-14', 'contact_number' => '09171234567', 'address' => '12 Sampaguita St., Quezon City'],
            ['first_name' => 'Carlo',   'last_name' => 'Mendoza',     'middle_name' => 'Bautista',  'birthdate' => '1992-07-22', 'contact_number' => '09281234567', 'address' => '45 Rizal Ave., Manila'],
            ['first_name' => 'Liza',    'last_name' => 'Reyes',       'middle_name' => 'Cruz',      'birthdate' => '1997-11-08', 'contact_number' => '09391234567', 'address' => '78 Magsaysay Blvd., Davao City'],
            ['first_name' => 'Ramon',   'last_name' => 'Garcia',      'middle_name' => 'Torres',    'birthdate' => '1990-05-30', 'contact_number' => '09501234567', 'address' => '3 Bonifacio St., Cebu City'],
            ['first_name' => 'Patricia','last_name' => 'Lim',         'middle_name' => 'Ong',       'birthdate' => '1998-09-17', 'contact_number' => '09611234567', 'address' => '21 Mabini St., Makati City'],
            ['first_name' => 'Marco',   'last_name' => 'Fernandez',   'middle_name' => 'dela Vega', 'birthdate' => '1993-01-25', 'contact_number' => '09721234567', 'address' => '56 Luna St., Pasig City'],
        ];

        $profiles = collect();
        foreach ($applicants as $i => $applicant) {
            $profiles->push(ApplicantProfile::create(
                array_merge($profileData[$i], ['user_id' => $applicant->id])
            ));
        }

        // ── Vacancies ─────────────────────────────────────────────────────────
        $vacancyData = [
            [
                'position_title'   => 'Administrative Officer II',
                'item_number'      => 'CSCRO-AO2-001-2026',
                'salary_grade'     => 11,
                'plantilla_number' => 'PNTN-001',
                'place_of_assignment' => 'CSC Regional Office No. XI, Davao City',
                'education_req'    => 'Bachelor\'s Degree relevant to the job',
                'experience_req'   => '1 year of relevant experience',
                'training_req'     => '4 hours of relevant training',
                'eligibility_req'  => 'Career Service (Professional) / Second Level Eligibility',
                'status'           => 'published',
                'published_at'     => Carbon::now()->subDays(10),
                'deadline_at'      => Carbon::now()->addDays(20),
            ],
            [
                'position_title'   => 'Information Technology Officer I',
                'item_number'      => 'CSCRO-ITO1-002-2026',
                'salary_grade'     => 19,
                'plantilla_number' => 'PNTN-002',
                'place_of_assignment' => 'CSC Regional Office No. XI, Davao City',
                'education_req'    => 'Bachelor\'s Degree in Information Technology, Computer Science, or related field',
                'experience_req'   => '2 years of relevant experience',
                'training_req'     => '8 hours of relevant training',
                'eligibility_req'  => 'Career Service (Professional) / Second Level Eligibility',
                'status'           => 'published',
                'published_at'     => Carbon::now()->subDays(7),
                'deadline_at'      => Carbon::now()->addDays(23),
            ],
            [
                'position_title'   => 'Human Resource Management Officer III',
                'item_number'      => 'CSCRO-HRMO3-003-2026',
                'salary_grade'     => 18,
                'plantilla_number' => 'PNTN-003',
                'place_of_assignment' => 'CSC Regional Office No. XI, Davao City',
                'education_req'    => 'Bachelor\'s Degree in Public Administration, Human Resource Management, or related field',
                'experience_req'   => '2 years of relevant experience',
                'training_req'     => '8 hours of relevant training',
                'eligibility_req'  => 'Career Service (Professional) / Second Level Eligibility',
                'status'           => 'published',
                'published_at'     => Carbon::now()->subDays(5),
                'deadline_at'      => Carbon::now()->addDays(25),
            ],
            [
                'position_title'   => 'Attorney III',
                'item_number'      => 'CSCRO-ATY3-004-2026',
                'salary_grade'     => 21,
                'plantilla_number' => 'PNTN-004',
                'place_of_assignment' => 'CSC Regional Office No. XI, Davao City',
                'education_req'    => 'Bachelor of Laws',
                'experience_req'   => '3 years of relevant experience',
                'training_req'     => '16 hours of relevant training',
                'eligibility_req'  => 'RA 1080 (Bar)',
                'status'           => 'published',
                'published_at'     => Carbon::now()->subDays(3),
                'deadline_at'      => Carbon::now()->addDays(27),
            ],
            [
                'position_title'   => 'Administrative Aide VI',
                'item_number'      => 'CSCRO-AA6-005-2026',
                'salary_grade'     => 6,
                'plantilla_number' => 'PNTN-005',
                'place_of_assignment' => 'CSC Regional Office No. XI, Davao City',
                'education_req'    => 'Completion of 2 years studies in college',
                'experience_req'   => '1 year of relevant experience',
                'training_req'     => '4 hours of relevant training',
                'eligibility_req'  => 'Career Service (Subprofessional) / First Level Eligibility',
                'status'           => 'closed',
                'published_at'     => Carbon::now()->subDays(60),
                'deadline_at'      => Carbon::now()->subDays(5),
            ],
            [
                'position_title'   => 'Records Officer II',
                'item_number'      => 'CSCRO-RO2-006-2026',
                'salary_grade'     => 11,
                'plantilla_number' => 'PNTN-006',
                'place_of_assignment' => 'CSC Regional Office No. XI, Davao City',
                'education_req'    => 'Bachelor\'s Degree relevant to the job',
                'experience_req'   => '1 year of relevant experience',
                'training_req'     => '4 hours of relevant training',
                'eligibility_req'  => 'Career Service (Professional) / Second Level Eligibility',
                'status'           => 'draft',
                'published_at'     => null,
                'deadline_at'      => null,
            ],
        ];

        $vacancies = collect();
        foreach ($vacancyData as $data) {
            $vacancies->push(Vacancy::create(array_merge($data, ['posted_by' => $hrOfficer->id])));
        }

        // ── Applications ──────────────────────────────────────────────────────
        $applicationData = [
            // Ana applied for Admin Officer II → under review
            ['profile' => $profiles[0], 'vacancy' => $vacancies[0], 'status' => 'under_review',     'submitted_at' => Carbon::now()->subDays(8), 'reviewed_at' => Carbon::now()->subDays(6)],
            // Carlo applied for IT Officer I → exam scheduled
            ['profile' => $profiles[1], 'vacancy' => $vacancies[1], 'status' => 'exam_scheduled',   'submitted_at' => Carbon::now()->subDays(6), 'reviewed_at' => Carbon::now()->subDays(4)],
            // Liza applied for HRMO III → submitted
            ['profile' => $profiles[2], 'vacancy' => $vacancies[2], 'status' => 'submitted',        'submitted_at' => Carbon::now()->subDays(4), 'reviewed_at' => null],
            // Ramon applied for Attorney III → interviewed
            ['profile' => $profiles[3], 'vacancy' => $vacancies[3], 'status' => 'interviewed',      'submitted_at' => Carbon::now()->subDays(2), 'reviewed_at' => Carbon::now()->subDays(1)],
            // Patricia applied for Admin Aide VI (closed) → passed
            ['profile' => $profiles[4], 'vacancy' => $vacancies[4], 'status' => 'passed',           'submitted_at' => Carbon::now()->subDays(55), 'reviewed_at' => Carbon::now()->subDays(20), 'remarks' => 'Applicant met all requirements and passed the interview panel.'],
            // Marco applied for IT Officer I → submitted
            ['profile' => $profiles[5], 'vacancy' => $vacancies[1], 'status' => 'submitted',        'submitted_at' => Carbon::now()->subDays(5), 'reviewed_at' => null],
            // Ana also applied for HRMO III → failed
            ['profile' => $profiles[0], 'vacancy' => $vacancies[2], 'status' => 'failed',           'submitted_at' => Carbon::now()->subDays(30), 'reviewed_at' => Carbon::now()->subDays(15), 'remarks' => 'Did not meet the minimum eligibility requirement.'],
        ];

        $applications = collect();
        foreach ($applicationData as $data) {
            $applications->push(Application::create([
                'vacancy_id'   => $data['vacancy']->id,
                'applicant_id' => $data['profile']->id,
                'status'       => $data['status'],
                'submitted_at' => $data['submitted_at'],
                'reviewed_at'  => $data['reviewed_at'],
                'remarks'      => $data['remarks'] ?? null,
            ]));
        }

        // ── Audit Logs ────────────────────────────────────────────────────────
        $auditEntries = [
            ['user_id' => $hrOfficer->id,  'action' => 'created',   'model' => $vacancies[0], 'created_at' => Carbon::now()->subDays(10)],
            ['user_id' => $hrOfficer->id,  'action' => 'published',  'model' => $vacancies[0], 'created_at' => Carbon::now()->subDays(10)],
            ['user_id' => $hrOfficer->id,  'action' => 'created',   'model' => $vacancies[1], 'created_at' => Carbon::now()->subDays(7)],
            ['user_id' => $hrOfficer->id,  'action' => 'published',  'model' => $vacancies[1], 'created_at' => Carbon::now()->subDays(7)],
            ['user_id' => $hrOfficer->id,  'action' => 'created',   'model' => $vacancies[2], 'created_at' => Carbon::now()->subDays(5)],
            ['user_id' => $hrOfficer->id,  'action' => 'published',  'model' => $vacancies[2], 'created_at' => Carbon::now()->subDays(5)],
            ['user_id' => $hrOfficer->id,  'action' => 'created',   'model' => $vacancies[3], 'created_at' => Carbon::now()->subDays(3)],
            ['user_id' => $hrOfficer->id,  'action' => 'published',  'model' => $vacancies[3], 'created_at' => Carbon::now()->subDays(3)],
            ['user_id' => $hrManager->id,  'action' => 'updated',   'model' => $applications[0], 'created_at' => Carbon::now()->subDays(6)],
            ['user_id' => $hrManager->id,  'action' => 'updated',   'model' => $applications[1], 'created_at' => Carbon::now()->subDays(4)],
            ['user_id' => $admin->id,      'action' => 'created',   'model' => $hrOfficer, 'created_at' => Carbon::now()->subDays(15)],
        ];

        foreach ($auditEntries as $entry) {
            DB::table('audit_logs')->insert([
                'user_id'        => $entry['user_id'],
                'action'         => $entry['action'],
                'auditable_type' => get_class($entry['model']),
                'auditable_id'   => $entry['model']->id,
                'created_at'     => $entry['created_at'],
                'updated_at'     => $entry['created_at'],
            ]);
        }
    }
}
