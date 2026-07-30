<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicantProfile;
use App\Models\HrmpsbComposition;
use App\Models\User;
use App\Models\Vacancy;
use App\Policies\EvaluationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EvaluationPolicyTest extends TestCase
{
    use RefreshDatabase;

    private EvaluationPolicy $policy;
    private Application $application;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // SQLite CHECK constraints from the original enum() migration do not include
        // the 'hrmpsb' role value (added later by the simplify migration, which only
        // alters the MySQL column definition). The PRAGMA allows inserting that role
        // so the policy tests can validate role-based gating behaviour.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        $this->policy = new EvaluationPolicy();

        $vacancy = Vacancy::factory()->published()->create();
        $profile = ApplicantProfile::factory()->complete()->create();

        $this->application = Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->admin = User::factory()->admin()->create();
    }

    private function createComposition(User $user, string $role): HrmpsbComposition
    {
        return HrmpsbComposition::create([
            'user_id'     => $user->id,
            'hrmpsb_role' => $role,
            'is_active'   => true,
            'assigned_by' => $this->admin->id,
        ]);
    }

    // ── evaluate() ────────────────────────────────────────────────────────

    public function test_admin_can_evaluate(): void
    {
        $this->assertTrue($this->policy->evaluate($this->admin, $this->application));
    }

    public function test_hrmpsb_with_active_composition_can_evaluate(): void
    {
        $user = User::factory()->hrmpsb()->create();
        $this->createComposition($user, 'chairperson');

        $this->assertTrue($this->policy->evaluate($user, $this->application));
    }

    public function test_hrmpsb_without_active_composition_cannot_evaluate(): void
    {
        $user = User::factory()->hrmpsb()->create();

        $this->assertFalse($this->policy->evaluate($user, $this->application));
    }

    public function test_applicant_cannot_evaluate(): void
    {
        $user = User::factory()->applicant()->create();

        $this->assertFalse($this->policy->evaluate($user, $this->application));
    }

    // ── lock() ────────────────────────────────────────────────────────────

    public function test_admin_can_lock(): void
    {
        $this->assertTrue($this->policy->lock($this->admin, $this->application));
    }

    public function test_hrmpsb_secretariat_can_lock(): void
    {
        $user = User::factory()->hrmpsb()->create();
        $this->createComposition($user, 'secretariat');

        $this->assertTrue($this->policy->lock($user, $this->application));
    }

    public function test_hrmpsb_without_secretariat_role_cannot_lock(): void
    {
        $user = User::factory()->hrmpsb()->create();
        $this->createComposition($user, 'chairperson');

        $this->assertFalse($this->policy->lock($user, $this->application));
    }

    // ── unmask() ──────────────────────────────────────────────────────────

    public function test_admin_can_unmask(): void
    {
        $this->assertTrue($this->policy->unmask($this->admin));
    }

    public function test_hrmpsb_secretariat_can_unmask(): void
    {
        $user = User::factory()->hrmpsb()->create();
        $this->createComposition($user, 'secretariat');

        $this->assertTrue($this->policy->unmask($user));
    }

    public function test_hrmpsb_non_secretariat_cannot_unmask(): void
    {
        $user = User::factory()->hrmpsb()->create();
        $this->createComposition($user, 'chairperson');

        $this->assertFalse($this->policy->unmask($user));
    }
}
