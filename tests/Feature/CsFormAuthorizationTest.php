<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\CsForm;
use App\Models\HrmpsbComposition;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * CsFormController previously relied on nothing but the blanket
 * role:hrmpsb,admin route middleware — unlike QS/CBWE/BEI, it never checked
 * for an active HrmpsbComposition row, so an hrmpsb-role account with no
 * board seat at all (never assigned, or since deactivated) could generate,
 * download, sign, or mark CS Forms as submitted to CSC.
 */
class CsFormAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        Storage::fake('local');

        $this->admin = User::factory()->admin()->create();

        $vacancy = Vacancy::factory()->published()->create();
        $profile = ApplicantProfile::factory()->complete()->create();
        $this->application = Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function test_hrmpsb_user_with_no_board_seat_cannot_generate_a_form(): void
    {
        $noSeat = User::factory()->hrmpsb()->create();

        $this->actingAs($noSeat, 'sanctum')
            ->postJson("/api/applications/{$this->application->id}/forms", ['form_type' => 'form1'])
            ->assertForbidden();
    }

    public function test_hrmpsb_user_with_no_board_seat_cannot_view_form_list(): void
    {
        $noSeat = User::factory()->hrmpsb()->create();

        $this->actingAs($noSeat, 'sanctum')
            ->getJson("/api/applications/{$this->application->id}/forms")
            ->assertForbidden();
    }

    public function test_hrmpsb_user_with_no_board_seat_cannot_download_or_sign_a_form(): void
    {
        $noSeat = User::factory()->hrmpsb()->create();

        $form = CsForm::create([
            'application_id' => $this->application->id,
            'form_type'      => 'form1',
            'file_path'      => 'cs-forms/test.pdf',
            'generated_by'   => $this->admin->id,
            'generated_at'   => now(),
        ]);
        Storage::disk('local')->put('cs-forms/test.pdf', 'fake-pdf');

        $this->actingAs($noSeat, 'sanctum')
            ->getJson("/api/forms/{$form->id}/download")
            ->assertForbidden();

        $this->actingAs($noSeat, 'sanctum')
            ->patchJson("/api/forms/{$form->id}/mark-submitted")
            ->assertForbidden();

        $this->actingAs($noSeat, 'sanctum')
            ->patchJson("/api/forms/{$form->id}/sign")
            ->assertForbidden();
    }

    public function test_any_active_hrmpsb_composition_role_can_generate_a_form(): void
    {
        // HRMPSB composition is global, not per-vacancy, and CS Forms are
        // deliberately meant to be accessible to any active board member
        // (per the route comment "accessible by all hrmpsb members
        // including appointing-authority") — so a role with no connection
        // to this specific vacancy should still be allowed through.
        $member = User::factory()->hrmpsb()->create();
        HrmpsbComposition::create([
            'user_id'     => $member->id,
            'hrmpsb_role' => 'pintig-representative-2nd',
            'is_active'   => true,
            'assigned_by' => $this->admin->id,
        ]);

        $this->actingAs($member, 'sanctum')
            ->postJson("/api/applications/{$this->application->id}/forms", ['form_type' => 'form1'])
            ->assertCreated();
    }
}
