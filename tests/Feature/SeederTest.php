<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\HrmpsbComposition;
use App\Models\PlaceOfAssignmentHead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_no_board_members(): void
    {
        $this->seed();

        // The board is CSC employees from IMIS, given roles by an administrator.
        $this->assertSame(0, User::where('role', 'hrmpsb')->count());
        $this->assertSame(0, HrmpsbComposition::count());
        $this->assertSame(0, PlaceOfAssignmentHead::count());
    }

    public function test_the_board_picker_can_list_every_hrmpsb_account_and_sees_which_are_from_imis(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->hrmpsb()->count(25)->create();
        User::factory()->hrmpsb()->create(['imis_user_id' => 41]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users?role=hrmpsb&per_page=100')->assertOk();

        $this->assertCount(26, $response->json('data'));
        $this->assertContains(41, array_column($response->json('data'), 'imis_user_id'));
    }
}
