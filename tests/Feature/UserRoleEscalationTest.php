<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\HrmpsbComposition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `admin-access` (EnsureAdminAccess / User::canAccessAdminModule) admits two
 * different trust levels: a true admin, and an hrmpsb user delegated
 * secretariat/hr-chief. UserController must not let the delegated tier grant
 * or touch the `admin` role — that would let a secretariat/hr-chief user
 * promote themselves (or anyone) past what their designation grants.
 */
class UserRoleEscalationTest extends TestCase
{
    use RefreshDatabase;

    private function delegatedSecretariat(): User
    {
        $admin = User::factory()->admin()->create();
        $secretariat = User::factory()->hrmpsb()->create();

        HrmpsbComposition::create([
            'user_id' => $secretariat->id,
            'hrmpsb_role' => 'secretariat',
            'is_active' => true,
            'assigned_by' => $admin->id,
        ]);

        return $secretariat;
    }

    public function test_delegated_secretariat_cannot_create_an_admin_account(): void
    {
        $secretariat = $this->delegatedSecretariat();

        $response = $this->actingAs($secretariat, 'sanctum')->postJson('/api/admin/users', [
            'first_name' => 'New',
            'last_name' => 'Admin',
            'email' => 'new-admin@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'admin',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'new-admin@example.com']);
    }

    public function test_delegated_secretariat_can_still_create_a_non_admin_account(): void
    {
        $secretariat = $this->delegatedSecretariat();

        $response = $this->actingAs($secretariat, 'sanctum')->postJson('/api/admin/users', [
            'first_name' => 'New',
            'last_name' => 'HRMPSB Member',
            'email' => 'new-member@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'hrmpsb',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('users', ['email' => 'new-member@example.com', 'role' => 'hrmpsb']);
    }

    public function test_delegated_secretariat_cannot_promote_themselves_to_admin(): void
    {
        $secretariat = $this->delegatedSecretariat();

        $response = $this->actingAs($secretariat, 'sanctum')
            ->patchJson("/api/admin/users/{$secretariat->id}", ['role' => 'admin']);

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $secretariat->id, 'role' => 'hrmpsb']);
    }

    public function test_delegated_secretariat_cannot_modify_an_existing_admin(): void
    {
        $secretariat = $this->delegatedSecretariat();
        $otherAdmin = User::factory()->admin()->create();

        $response = $this->actingAs($secretariat, 'sanctum')
            ->patchJson("/api/admin/users/{$otherAdmin->id}", ['first_name' => 'Renamed']);

        $response->assertForbidden();
    }

    public function test_delegated_secretariat_cannot_delete_an_admin(): void
    {
        $secretariat = $this->delegatedSecretariat();
        $otherAdmin = User::factory()->admin()->create();

        $response = $this->actingAs($secretariat, 'sanctum')
            ->deleteJson("/api/admin/users/{$otherAdmin->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }

    public function test_true_admin_can_still_create_and_manage_admin_accounts(): void
    {
        $admin = User::factory()->admin()->create();

        $create = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/users', [
            'first_name' => 'Second',
            'last_name' => 'Admin',
            'email' => 'second-admin@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'admin',
        ]);
        $create->assertCreated();

        $newAdminId = $create->json('id');

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/users/{$newAdminId}", ['first_name' => 'Renamed'])
            ->assertOk();
    }
}
