<?php

namespace Database\Seeders;

use App\Models\AnonymizationToken;
use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\BeiRating;
use App\Models\CbweRating;
use App\Models\ExamResult;
use App\Models\HrmbsboardComposition;
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

        // ── Staff Users ───────────────────────────────────────────────────────
        $admin = User::create([
            'first_name'        => 'System',
            'last_name'         => 'Administrator',
            'email'             => 'admin@csc8.gov.ph',
            'password'          => Hash::make('Password123'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        $hrOfficer = User::create([
            'first_name'        => 'Maria',
            'last_name'         => 'Santos',
            'middle_name'       => 'Buenaventura',
            'email'             => 'maria.santos@csc8.gov.ph',
            'password'          => Hash::make('Password123'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        // ── HRMPSB Members (role = 'hrmpsb') ─────────────────────────────────
        $hrmpsb = collect([
            ['first_name' => 'Rodrigo',   'last_name' => 'Dela Peña',  'middle_name' => 'Viernes',    'email' => 'rodrigo.delapena@csc8.gov.ph',   'suffix' => null],
            ['first_name' => 'Carmela',   'last_name' => 'Villanueva', 'middle_name' => 'Ramos',      'email' => 'carmela.villanueva@csc8.gov.ph', 'suffix' => null],
            ['first_name' => 'Benjamin',  'last_name' => 'Oquendo',    'middle_name' => 'Tan',        'email' => 'benjamin.oquendo@csc8.gov.ph',   'suffix' => 'Jr.'],
            ['first_name' => 'Luzviminda','last_name' => 'Cabrera',    'middle_name' => 'Ferrer',     'email' => 'luzviminda.cabrera@csc8.gov.ph', 'suffix' => null],
            ['first_name' => 'Arsenio',   'last_name' => 'Macaraig',   'middle_name' => 'Quiambao',   'email' => 'arsenio.macaraig@csc8.gov.ph',   'suffix' => null],
            ['first_name' => 'Florencia', 'last_name' => 'Espiritu',   'middle_name' => 'Laurel',     'email' => 'florencia.espiritu@csc8.gov.ph', 'suffix' => null],
            ['first_name' => 'Eduardo',   'last_name' => 'Bautista',   'middle_name' => 'Chua',       'email' => 'eduardo.bautista@csc8.gov.ph',   'suffix' => 'Sr.'],
            ['first_name' => 'Natividad', 'last_name' => 'Soriano',    'middle_name' => 'Aguilar',    'email' => 'natividad.soriano@csc8.gov.ph',  'suffix' => null],
            ['first_name' => 'Renato',    'last_name' => 'Perez',      'middle_name' => 'Dizon',      'email' => 'renato.perez@csc8.gov.ph',       'suffix' => null],
        ])->map(fn ($d) => User::create([
            'first_name'        => $d['first_name'],
            'last_name'         => $d['last_name'],
            'middle_name'       => $d['middle_name'],
            'suffix'            => $d['suffix'],
            'email'             => $d['email'],
            'password'          => Hash::make('Password123'),
            'role'              => 'hrmpsb',
            'email_verified_at' => now(),
        ]));

        // ── HRMPSB Compositions ───────────────────────────────────────────────
        // $hrmpsb[0] = Chairperson (Dela Peña)
        // $hrmpsb[1] = Secretariat (Villanueva) — can access admin module
        // $hrmpsb[2] = Appointing Authority (Oquendo)
        // $hrmpsb[3] = Director II Representative (Cabrera)
        // $hrmpsb[4] = Division Chief Representative (Macaraig)
        // $hrmpsb[5] = HR Chief (Espiritu) — can access admin module
        // $hrmpsb[6] = Head of Unit (Bautista)
        // $hrmpsb[7] = PINTIG Rep 1st Level (Soriano)
        // $hrmpsb[8] = PINTIG Rep 2nd Level (Perez)

        $compositionData = [
            ['user' => $hrmpsb[0], 'role' => 'chairperson',                   'type' => 'principal'],
            ['user' => $hrmpsb[1], 'role' => 'secretariat',                   'type' => 'principal'],
            ['user' => $hrmpsb[2], 'role' => 'appointing-authority',          'type' => 'principal'],
            ['user' => $hrmpsb[3], 'role' => 'director-representative',       'type' => 'principal'],
            ['user' => $hrmpsb[4], 'role' => 'division-chief-representative', 'type' => 'principal'],
            ['user' => $hrmpsb[5], 'role' => 'hr-chief',                      'type' => 'principal'],
            ['user' => $hrmpsb[6], 'role' => 'head-of-unit',                  'type' => 'principal'],
            ['user' => $hrmpsb[7], 'role' => 'pintig-representative-1st',     'type' => 'principal'],
            ['user' => $hrmpsb[8], 'role' => 'pintig-representative-2nd',     'type' => 'principal'],
        ];

        foreach ($compositionData as $comp) {
            HrmbsboardComposition::create([
                'user_id'     => $comp['user']->id,
                'hrmpsb_role' => $comp['role'],
                'member_type' => $comp['type'],
                'is_active'   => true,
                'assigned_by' => $admin->id,
                'assigned_at' => now()->subDays(30),
            ]);
        }

        $secretariat  = $hrmpsb[1]; // Villanueva — encodes most assessments
        $chairperson  = $hrmpsb[0]; // Dela Peña
        $dirRep       = $hrmpsb[3]; // Cabrera
        $divChief     = $hrmpsb[4]; // Macaraig
        $hrChief      = $hrmpsb[5]; // Espiritu
        $headOfUnit   = $hrmpsb[6]; // Bautista

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
        $profiles   = collect();

        foreach ($applicantData as $d) {
            $user = User::create([
                'first_name'        => $d['first_name'],
                'last_name'         => $d['last_name'],
                'middle_name'       => $d['middle_name'],
                'email'             => $d['email'],
                'password'          => Hash::make('Password123'),
                'role'              => 'applicant',
                'email_verified_at' => now(),
            ]);
            $applicants->push($user);

            $profiles->push(ApplicantProfile::create([
                'user_id'               => $user->id,
                'gender'                => $d['gender'],
                'civil_status'          => $d['civil_status'],
                'birthday'              => $d['birthday'],
                'religion'              => 'Roman Catholic',
                'region'                => 'Region VIII',
                'province'              => 'Leyte',
                'city_municipality'     => 'Tacloban City',
                'barangay'              => 'Barangay 1',
                'mobile_number'         => $d['mobile_number'],
                'eligibility'           => $d['eligibility'],
                'indigenous_group'      => 'No',
                'pwd'                   => 'No',
                'solo_parent'           => 'No',
                'profile_completed_at'  => now()->subDays(20),
            ]));
        }

        // ── Vacancies ─────────────────────────────────────────────────────────
        $vacancies = collect();

        $v1 = Vacancy::create([
            'position_title'      => 'Administrative Officer II',
            'plantilla_no'        => 'CSCRO8-AO2-001-2026',
            'salary_grade'        => 11,
            'monthly_salary'      => 27000.00,
            'position_level'      => 'Second Level',
            'place_of_assignment' => 'CSC Regional Office No. VIII, Tacloban City',
            'education_req'       => "Bachelor's Degree relevant to the job",
            'experience_req'      => '1 year of relevant experience',
            'training_req'        => '4 hours of relevant training',
            'eligibility_req'     => 'Career Service (Professional) / Second Level Eligibility',
            'status'              => 'published',
            'posted_by'           => $hrOfficer->id,
            'published_at'        => Carbon::now()->subDays(25),
            'deadline_at'         => Carbon::now()->addDays(5),
        ]);
        $vacancies->push($v1);

        $v2 = Vacancy::create([
            'position_title'      => 'Information Technology Officer I',
            'plantilla_no'        => 'CSCRO8-ITO1-002-2026',
            'salary_grade'        => 19,
            'monthly_salary'      => 57699.00,
            'position_level'      => 'Second Level',
            'place_of_assignment' => 'CSC Regional Office No. VIII, Tacloban City',
            'education_req'       => "Bachelor's Degree in Information Technology, Computer Science, or related field",
            'experience_req'      => '2 years of relevant experience',
            'training_req'        => '8 hours of relevant training',
            'eligibility_req'     => 'Career Service (Professional) / Second Level Eligibility',
            'status'              => 'published',
            'posted_by'           => $hrOfficer->id,
            'published_at'        => Carbon::now()->subDays(20),
            'deadline_at'         => Carbon::now()->addDays(10),
        ]);
        $vacancies->push($v2);

        $v3 = Vacancy::create([
            'position_title'      => 'Human Resource Management Officer III',
            'plantilla_no'        => 'CSCRO8-HRMO3-003-2026',
            'salary_grade'        => 18,
            'monthly_salary'      => 51357.00,
            'position_level'      => 'Second Level',
            'place_of_assignment' => 'CSC Regional Office No. VIII, Tacloban City',
            'education_req'       => "Bachelor's Degree in Public Administration, Human Resource Management, or related field",
            'experience_req'      => '2 years of relevant experience',
            'training_req'        => '8 hours of relevant training',
            'eligibility_req'     => 'Career Service (Professional) / Second Level Eligibility',
            'status'              => 'published',
            'posted_by'           => $hrOfficer->id,
            'published_at'        => Carbon::now()->subDays(15),
            'deadline_at'         => Carbon::now()->addDays(15),
        ]);
        $vacancies->push($v3);

        $v4 = Vacancy::create([
            'position_title'      => 'Records Officer II',
            'plantilla_no'        => 'CSCRO8-RO2-004-2026',
            'salary_grade'        => 11,
            'monthly_salary'      => 27000.00,
            'position_level'      => 'Second Level',
            'place_of_assignment' => 'CSC Regional Office No. VIII, Tacloban City',
            'education_req'       => "Bachelor's Degree relevant to the job",
            'experience_req'      => '1 year of relevant experience',
            'training_req'        => '4 hours of relevant training',
            'eligibility_req'     => 'Career Service (Professional) / Second Level Eligibility',
            'status'              => 'draft',
            'posted_by'           => $hrOfficer->id,
            'published_at'        => null,
            'deadline_at'         => null,
        ]);
        $vacancies->push($v4);

        // ── Vacancy Competencies ──────────────────────────────────────────────
        // V1 — Administrative Officer II (SG 11, operational)
        $v1Competencies = [
            ['key' => 'exemplifying_integrity',          'level' => 2],
            ['key' => 'delivering_service_excellence',   'level' => 2],
            ['key' => 'solving_problems_making_decisions','level' => 2],
            ['key' => 'planning_and_delivering',         'level' => 2],
            ['key' => 'writing_effectively_1',           'level' => 2],
            ['key' => 'records_management',              'level' => 2],
            ['key' => 'secretariat_liaison_services',    'level' => 2],
        ];
        foreach ($v1Competencies as $c) {
            VacancyCompetency::create(['vacancy_id' => $v1->id, 'competency_key' => $c['key'], 'competency_level' => $c['level']]);
        }

        // V2 — Information Technology Officer I (SG 19, technical)
        $v2Competencies = [
            ['key' => 'exemplifying_integrity',          'level' => 3],
            ['key' => 'delivering_service_excellence',   'level' => 3],
            ['key' => 'solving_problems_making_decisions','level' => 3],
            ['key' => 'championing_and_applying_innovation', 'level' => 3],
            ['key' => 'planning_and_delivering',         'level' => 3],
            ['key' => 'information_technology',          'level' => 3],
        ];
        foreach ($v2Competencies as $c) {
            VacancyCompetency::create(['vacancy_id' => $v2->id, 'competency_key' => $c['key'], 'competency_level' => $c['level']]);
        }

        // V3 — HRMO III (SG 18, supervisory)
        $v3Competencies = [
            ['key' => 'exemplifying_integrity',          'level' => 3],
            ['key' => 'delivering_service_excellence',   'level' => 3],
            ['key' => 'solving_problems_making_decisions','level' => 3],
            ['key' => 'thinking_strategically',          'level' => 3],
            ['key' => 'managing_performance_coaching',   'level' => 3],
            ['key' => 'building_collaborative_inclusive','level' => 3],
            ['key' => 'policy_interpretation_implementation', 'level' => 3],
        ];
        foreach ($v3Competencies as $c) {
            VacancyCompetency::create(['vacancy_id' => $v3->id, 'competency_key' => $c['key'], 'competency_level' => $c['level']]);
        }

        // V4 — Records Officer II (SG 11)
        $v4Competencies = [
            ['key' => 'exemplifying_integrity',        'level' => 2],
            ['key' => 'delivering_service_excellence', 'level' => 2],
            ['key' => 'planning_and_delivering',       'level' => 2],
            ['key' => 'records_management',            'level' => 2],
        ];
        foreach ($v4Competencies as $c) {
            VacancyCompetency::create(['vacancy_id' => $v4->id, 'competency_key' => $c['key'], 'competency_level' => $c['level']]);
        }

        // ── Applications for V1 (Admin Officer II) — full pipeline ─────────────
        // 5 applicants, all qualified, pushed through exam + BEI + CBWE stages
        $v1Apps = collect();
        $appDay = 23;
        foreach ([0, 1, 2, 3, 4] as $i) {
            $app = Application::create([
                'vacancy_id'   => $v1->id,
                'applicant_id' => $profiles[$i]->id,
                'status'       => match($i) {
                    0, 1 => 'for_interview',
                    2    => 'shortlisted',
                    3    => 'disqualified',
                    4    => 'shortlisted',
                },
                'submitted_at' => Carbon::now()->subDays($appDay - $i),
                'reviewed_at'  => Carbon::now()->subDays($appDay - $i - 2),
            ]);
            $v1Apps->push($app);

            // Anonymization token: CSCRO8-AO2-001-2026 → prefix AO2-0601
            AnonymizationToken::create([
                'application_id' => $app->id,
                'token'          => 'AO2-0601-00' . ($i + 1),
            ]);
        }

        // QS Evaluations (secretariat evaluates all; chairperson co-evaluates)
        $qsData = [
            // Ana Dela Cruz — all meets
            [$v1Apps[0]->id, $secretariat->id,  true,  true,  true,  true,  true,  'Meets all minimum qualifications.'],
            [$v1Apps[0]->id, $chairperson->id,  true,  true,  true,  true,  true,  'Concurs with secretariat evaluation.'],
            // Carlo Mendoza — all meets
            [$v1Apps[1]->id, $secretariat->id,  true,  true,  true,  true,  true,  'Meets all requirements.'],
            [$v1Apps[1]->id, $chairperson->id,  true,  true,  true,  true,  true,  null],
            // Liza Reyes — all meets
            [$v1Apps[2]->id, $secretariat->id,  true,  true,  true,  true,  true,  'Meets all minimum qualifications.'],
            [$v1Apps[2]->id, $hrChief->id,      true,  true,  true,  true,  true,  null],
            // Ramon Garcia — disqualified (training requirement not met)
            [$v1Apps[3]->id, $secretariat->id,  true,  true,  false, true,  false, 'Training requirement not met. No certificate submitted.'],
            [$v1Apps[3]->id, $chairperson->id,  true,  true,  false, true,  false, 'Concurs. Training hours insufficient.'],
            // Patricia Lim — all meets
            [$v1Apps[4]->id, $secretariat->id,  true,  true,  true,  true,  true,  'Meets all minimum qualifications.'],
            [$v1Apps[4]->id, $dirRep->id,       true,  true,  true,  true,  true,  null],
        ];

        foreach ($qsData as [$appId, $evalId, $edu, $exp, $trn, $elig, $overall, $remarks]) {
            QsEvaluation::create([
                'application_id'   => $appId,
                'evaluator_id'     => $evalId,
                'education_meets'  => $edu,
                'experience_meets' => $exp,
                'training_meets'   => $trn,
                'eligibility_meets'=> $elig,
                'overall_qualified'=> $overall,
                'remarks'          => $remarks,
                'evaluated_at'     => Carbon::now()->subDays(18),
                'locked_at'        => Carbon::now()->subDays(17),
            ]);
        }

        // Pre-Assessment (secretariat fills this for all qualified apps)
        $preAssessData = [
            [$v1Apps[0]->id, true, true, true, true, true, true, true, true, true, true, true, 'Applicant has strong academic credentials and relevant work experience. Highly recommended.'],
            [$v1Apps[1]->id, true, true, true, true, true, true, true, true, true, true, true, 'Complete documents submitted. Meets all qualifications.'],
            [$v1Apps[2]->id, true, false,true, true, true, true, true, true, true, true, true, 'IPCR not yet submitted. All other requirements complete.'],
            [$v1Apps[3]->id, true, true, true, true, false,false,false,false,false,false,false, 'Disqualified. Training requirement not met.'],
            [$v1Apps[4]->id, true, true, true, true, true, true, true, true, true, true, true, 'All requirements submitted and verified. Meets qualifications.'],
        ];

        foreach ($preAssessData as [$appId, $pds, $ipcr, $tor, $coe, $reqComp, $edu, $lic, $exp, $trn, $elig, $hrd, $remarks]) {
            PreAssessment::create([
                'application_id'        => $appId,
                'pds_submitted'         => $pds,
                'ipcr_submitted'        => $ipcr,
                'tor_submitted'         => $tor,
                'coe_submitted'         => $coe,
                'requirements_complete' => $reqComp,
                'requirements_remarks'  => null,
                'education_meets'       => $edu,
                'license_meets'         => $lic,
                'experience_meets'      => $exp,
                'training_meets'        => $trn,
                'eligibility_meets'     => $elig,
                'hrd_assessment'        => $hrd,
                'hrd_remarks'           => $remarks,
                'consensus'             => $hrd,
                'assessed_by'           => $secretariat->id,
                'assessed_at'           => Carbon::now()->subDays(17),
            ]);
        }

        // Exam Results — TWE (Written Exam) for 4 qualified applicants
        // Passing score: 70% (35/50)
        $tweScores = [
            [$v1Apps[0]->id, 44, 50, 'TWE'], // Ana   — 88%
            [$v1Apps[1]->id, 38, 50, 'TWE'], // Carlo — 76%
            [$v1Apps[2]->id, 41, 50, 'TWE'], // Liza  — 82%
            [$v1Apps[4]->id, 36, 50, 'TWE'], // Patricia — 72%
        ];
        foreach ($tweScores as [$appId, $raw, $max, $type]) {
            ExamResult::create([
                'application_id' => $appId,
                'exam_type'      => $type,
                'raw_score'      => $raw,
                'max_score'      => $max,
                'encoded_by'     => $secretariat->id,
                'encoded_at'     => Carbon::now()->subDays(14),
            ]);
        }

        // BEI Ratings (3 evaluators: chairperson, dirRep, divChief)
        // Competencies: professionalism_ethics, results_focus, teamwork_cooperation,
        //               creative_problem_solving, public_service_orientation
        // Scale: 1-5 (5 = Outstanding)
        $beiData = [
            // Ana Dela Cruz — excellent scores across all evaluators
            [$v1Apps[0]->id, $chairperson->id, ['professionalism_ethics'=>5,'results_focus'=>5,'teamwork_cooperation'=>4,'creative_problem_solving'=>4,'public_service_orientation'=>5], 4.60, 'Consistently demonstrates exemplary values and strong results orientation.'],
            [$v1Apps[0]->id, $dirRep->id,      ['professionalism_ethics'=>5,'results_focus'=>4,'teamwork_cooperation'=>5,'creative_problem_solving'=>4,'public_service_orientation'=>5], 4.60, 'Excellent interpersonal skills and commitment to public service.'],
            [$v1Apps[0]->id, $divChief->id,    ['professionalism_ethics'=>4,'results_focus'=>5,'teamwork_cooperation'=>4,'creative_problem_solving'=>5,'public_service_orientation'=>4], 4.40, 'Strong problem-solving ability. Highly recommended.'],
            // Carlo Mendoza
            [$v1Apps[1]->id, $chairperson->id, ['professionalism_ethics'=>4,'results_focus'=>4,'teamwork_cooperation'=>3,'creative_problem_solving'=>3,'public_service_orientation'=>4], 3.60, 'Adequate performance. Needs improvement in teamwork.'],
            [$v1Apps[1]->id, $dirRep->id,      ['professionalism_ethics'=>4,'results_focus'=>3,'teamwork_cooperation'=>4,'creative_problem_solving'=>3,'public_service_orientation'=>4], 3.60, 'Satisfactory. Modest problem-solving skills.'],
            [$v1Apps[1]->id, $divChief->id,    ['professionalism_ethics'=>3,'results_focus'=>4,'teamwork_cooperation'=>4,'creative_problem_solving'=>4,'public_service_orientation'=>3], 3.60, 'Acceptable. Communication skills need strengthening.'],
            // Liza Reyes
            [$v1Apps[2]->id, $chairperson->id, ['professionalism_ethics'=>5,'results_focus'=>4,'teamwork_cooperation'=>5,'creative_problem_solving'=>4,'public_service_orientation'=>5], 4.60, 'Outstanding integrity and team player. Highly recommended.'],
            [$v1Apps[2]->id, $dirRep->id,      ['professionalism_ethics'=>4,'results_focus'=>5,'teamwork_cooperation'=>4,'creative_problem_solving'=>5,'public_service_orientation'=>4], 4.40, 'Strong analytical skills. Very good candidate.'],
            [$v1Apps[2]->id, $divChief->id,    ['professionalism_ethics'=>5,'results_focus'=>4,'teamwork_cooperation'=>5,'creative_problem_solving'=>3,'public_service_orientation'=>5], 4.40, 'Excellent public service orientation.'],
            // Patricia Lim
            [$v1Apps[4]->id, $chairperson->id, ['professionalism_ethics'=>4,'results_focus'=>3,'teamwork_cooperation'=>4,'creative_problem_solving'=>3,'public_service_orientation'=>4], 3.60, 'Satisfactory overall. Moderate results focus.'],
            [$v1Apps[4]->id, $dirRep->id,      ['professionalism_ethics'=>3,'results_focus'=>4,'teamwork_cooperation'=>3,'creative_problem_solving'=>4,'public_service_orientation'=>3], 3.40, 'Average performance. Further development needed.'],
            [$v1Apps[4]->id, $divChief->id,    ['professionalism_ethics'=>4,'results_focus'=>3,'teamwork_cooperation'=>4,'creative_problem_solving'=>3,'public_service_orientation'=>4], 3.60, 'Satisfactory. Good interpersonal skills.'],
        ];

        foreach ($beiData as [$appId, $evalId, $scores, $total, $remarks]) {
            BeiRating::create([
                'application_id'  => $appId,
                'evaluator_id'    => $evalId,
                'competency_scores'=> $scores,
                'total_rating'    => $total,
                'remarks'         => $remarks,
                'rated_at'        => Carbon::now()->subDays(10),
                'locked_at'       => Carbon::now()->subDays(9),
            ]);
        }

        // CBWE Ratings — 3 competencies: exemplifying_integrity,
        //   delivering_service_excellence, solving_problems_making_decisions
        // Scale: 1-5
        $cbweData = [
            // Ana
            [$v1Apps[0]->id, $chairperson->id, ['exemplifying_integrity'=>5,'delivering_service_excellence'=>5,'solving_problems_making_decisions'=>4], 4.67, 'Exceptional integrity and client focus.'],
            [$v1Apps[0]->id, $dirRep->id,      ['exemplifying_integrity'=>5,'delivering_service_excellence'=>4,'solving_problems_making_decisions'=>5], 4.67, 'Very strong problem-solving in simulated scenarios.'],
            [$v1Apps[0]->id, $divChief->id,    ['exemplifying_integrity'=>4,'delivering_service_excellence'=>5,'solving_problems_making_decisions'=>4], 4.33, 'Excellent service orientation.'],
            // Carlo
            [$v1Apps[1]->id, $chairperson->id, ['exemplifying_integrity'=>4,'delivering_service_excellence'=>3,'solving_problems_making_decisions'=>3], 3.33, 'Acceptable. Needs growth in service delivery.'],
            [$v1Apps[1]->id, $dirRep->id,      ['exemplifying_integrity'=>3,'delivering_service_excellence'=>4,'solving_problems_making_decisions'=>3], 3.33, 'Average performance.'],
            [$v1Apps[1]->id, $divChief->id,    ['exemplifying_integrity'=>4,'delivering_service_excellence'=>3,'solving_problems_making_decisions'=>4], 3.67, 'Satisfactory.'],
            // Liza
            [$v1Apps[2]->id, $chairperson->id, ['exemplifying_integrity'=>5,'delivering_service_excellence'=>4,'solving_problems_making_decisions'=>5], 4.67, 'Demonstrates high integrity and sharp analytical skills.'],
            [$v1Apps[2]->id, $dirRep->id,      ['exemplifying_integrity'=>4,'delivering_service_excellence'=>5,'solving_problems_making_decisions'=>4], 4.33, 'Highly service-oriented.'],
            [$v1Apps[2]->id, $divChief->id,    ['exemplifying_integrity'=>5,'delivering_service_excellence'=>5,'solving_problems_making_decisions'=>4], 4.67, 'Outstanding.'],
            // Patricia
            [$v1Apps[4]->id, $chairperson->id, ['exemplifying_integrity'=>4,'delivering_service_excellence'=>3,'solving_problems_making_decisions'=>3], 3.33, 'Satisfactory with areas for improvement.'],
            [$v1Apps[4]->id, $dirRep->id,      ['exemplifying_integrity'=>3,'delivering_service_excellence'=>4,'solving_problems_making_decisions'=>3], 3.33, 'Average overall.'],
            [$v1Apps[4]->id, $divChief->id,    ['exemplifying_integrity'=>4,'delivering_service_excellence'=>3,'solving_problems_making_decisions'=>4], 3.67, 'Acceptable performance.'],
        ];

        foreach ($cbweData as [$appId, $evalId, $scores, $total, $remarks]) {
            CbweRating::create([
                'application_id'   => $appId,
                'evaluator_id'     => $evalId,
                'competency_scores'=> $scores,
                'total_rating'     => $total,
                'remarks'          => $remarks,
                'rated_at'         => Carbon::now()->subDays(10),
                'locked_at'        => Carbon::now()->subDays(9),
            ]);
        }

        // ── Applications for V2 (IT Officer I) — QS stage ────────────────────
        $v2Apps = collect();
        foreach ([1, 5, 6] as $i) {
            $app = Application::create([
                'vacancy_id'   => $v2->id,
                'applicant_id' => $profiles[$i]->id,
                'status'       => 'screened',
                'submitted_at' => Carbon::now()->subDays(18),
                'reviewed_at'  => Carbon::now()->subDays(16),
            ]);
            $v2Apps->push($app);

            AnonymizationToken::create([
                'application_id' => $app->id,
                'token'          => 'ITO1-0601-00' . ($v2Apps->count()),
            ]);

            QsEvaluation::create([
                'application_id'    => $app->id,
                'evaluator_id'      => $secretariat->id,
                'education_meets'   => true,
                'experience_meets'  => true,
                'training_meets'    => true,
                'eligibility_meets' => $i !== 5, // Marco has sub-professional — disqualified
                'overall_qualified' => $i !== 5,
                'remarks'           => $i === 5 ? 'Eligibility does not meet second level requirement.' : 'Meets all minimum qualifications.',
                'evaluated_at'      => Carbon::now()->subDays(14),
                'locked_at'         => Carbon::now()->subDays(13),
            ]);
        }

        // ── Applications for V3 (HRMO III) — submitted ───────────────────────
        foreach ([2, 6] as $i) {
            Application::create([
                'vacancy_id'   => $v3->id,
                'applicant_id' => $profiles[$i]->id,
                'status'       => 'submitted',
                'submitted_at' => Carbon::now()->subDays(10),
                'reviewed_at'  => null,
            ]);
        }

        // ── Audit Logs ────────────────────────────────────────────────────────
        $auditEntries = [
            [$hrOfficer->id, 'created',   $v1, Carbon::now()->subDays(25)],
            [$hrOfficer->id, 'published',  $v1, Carbon::now()->subDays(25)],
            [$hrOfficer->id, 'created',   $v2, Carbon::now()->subDays(20)],
            [$hrOfficer->id, 'published',  $v2, Carbon::now()->subDays(20)],
            [$hrOfficer->id, 'created',   $v3, Carbon::now()->subDays(15)],
            [$hrOfficer->id, 'published',  $v3, Carbon::now()->subDays(15)],
            [$admin->id,     'created',   $hrOfficer, Carbon::now()->subDays(30)],
        ];

        foreach ($auditEntries as [$userId, $action, $model, $createdAt]) {
            DB::table('audit_logs')->insert([
                'user_id'        => $userId,
                'action'         => $action,
                'auditable_type' => get_class($model),
                'auditable_id'   => $model->id,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ]);
        }
    }
}
