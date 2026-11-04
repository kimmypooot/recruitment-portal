<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminUsersImisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.imis.sso_url' => 'https://imis.test/api/v1/sso/validate',
            'services.imis.api_url' => 'https://imis.test',
            'services.imis.api_token' => 'tok',
        ]);
    }

    private function imisStaff(array $people): void
    {
        Http::fake(['imis.test/api/v1/recruitment/users' => Http::response(['data' => $people])]);
    }

    private function person(int $id, string $email, string $role, array $extra = []): array
    {
        return $extra + [
            'id' => $id, 'fname' => 'Ana', 'mname' => 'B', 'lname' => 'Cruz', 'email' => $email,
            'position' => 'HRMO III', 'office_name' => 'Human Resource Division', 'role_code' => $role, 'photo_url' => null,
        ];
    }

    public function test_imis_staff_are_matched_to_portal_accounts_and_the_local_only_ones_are_listed(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@csc.test']);
        $linked = User::factory()->hrmpsb()->create(['imis_user_id' => 11, 'email' => 'linked@csc.test', 'last_login_at' => now()]);
        $drifted = User::factory()->hrmpsb()->create(['imis_user_id' => 12, 'email' => 'drift@csc.test']);
        $sameEmail = User::factory()->admin()->create(['email' => 'seeded@csc.test']);
        $seeded = User::factory()->hrmpsb()->create(['email' => 'old.seed@csc.test']);
        User::factory()->applicant()->create(['email' => 'applicant@example.com']);

        $this->imisStaff([
            $this->person(11, 'linked@csc.test', 'hrmpsb'),
            $this->person(12, 'drift@csc.test', 'admin'),
            $this->person(13, 'seeded@csc.test', 'admin'),
            $this->person(14, 'new@csc.test', 'hrmpsb'),
        ]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users/imis-staff')->assertOk();
        $rows = collect($response->json('data'))->keyBy('imis_id');

        $this->assertTrue($response->json('enabled'));
        $this->assertSame('linked', $rows[11]['status']);
        $this->assertTrue($rows[11]['role_in_sync']);
        $this->assertSame($linked->id, $rows[11]['portal_user_id']);
        $this->assertSame('linked', $rows[12]['status']);
        $this->assertFalse($rows[12]['role_in_sync']);
        $this->assertSame('unlinked_account', $rows[13]['status']);
        $this->assertSame($sameEmail->id, $rows[13]['portal_user_id']);
        $this->assertSame('not_signed_in', $rows[14]['status']);
        $this->assertNull($rows[14]['portal_user_id']);
        $this->assertSame('Cruz, Ana B', $rows[11]['name']);

        // Staff accounts here that IMIS does not list: the admin who is looking, and the old seeded board member.
        $this->assertEqualsCanonicalizing([$admin->id, $seeded->id], $response->json('local_only'));
    }

    public function test_it_reports_when_imis_is_not_connected_or_fails(): void
    {
        $admin = User::factory()->admin()->create();

        config(['services.imis.api_token' => null]);
        $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users/imis-staff')
            ->assertOk()->assertJsonPath('enabled', false);

        config(['services.imis.api_token' => 'tok']);
        Http::fake(['imis.test/*' => Http::response([], 500)]);
        $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users/imis-staff')->assertStatus(502);
    }

    public function test_an_account_managed_in_imis_cannot_be_edited_or_deleted_here(): void
    {
        $admin = User::factory()->admin()->create();
        $managed = User::factory()->hrmpsb()->create(['imis_user_id' => 11]);

        $this->actingAs($admin, 'sanctum')->putJson("/api/admin/users/{$managed->id}", ['role' => 'admin'])
            ->assertUnprocessable();
        $this->actingAs($admin, 'sanctum')->deleteJson("/api/admin/users/{$managed->id}")->assertUnprocessable();

        $this->assertSame('hrmpsb', $managed->fresh()->role);
        $this->assertNotNull(User::find($managed->id));
    }

    public function test_local_accounts_and_applicants_can_still_be_edited(): void
    {
        $admin = User::factory()->admin()->create();
        $local = User::factory()->hrmpsb()->create();
        $applicant = User::factory()->applicant()->create();

        $this->actingAs($admin, 'sanctum')->putJson("/api/admin/users/{$local->id}", ['first_name' => 'Renamed'])->assertOk();
        $this->actingAs($admin, 'sanctum')->deleteJson("/api/admin/users/{$applicant->id}")->assertOk();

        $this->assertSame('Renamed', $local->fresh()->first_name);
    }

    public function test_the_user_list_returns_the_imis_link_for_each_account(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->hrmpsb()->create(['imis_user_id' => 11]);

        $rows = collect($this->actingAs($admin, 'sanctum')->getJson('/api/admin/users?per_page=100')->assertOk()->json('data'));

        $this->assertContains(11, $rows->pluck('imis_user_id')->all());
    }
}
