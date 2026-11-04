<?php

namespace Database\Seeders;

use App\Models\AnonymizationToken;
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

        // ── Applicants ────────────────────────────────────────────────────────
        // None are seeded. External applicants register themselves, and CSC employees
        // sign in from IMIS (they are listed under Admin → Users).

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
