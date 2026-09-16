<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_an_application_invalidates_the_cached_dashboard_stats(): void
    {
        $applicant = User::factory()->applicant()->create();
        $profile   = ApplicantProfile::factory()->complete()->withDocuments()->create(['user_id' => $applicant->id]);
        $vacancy   = Vacancy::factory()->published()->create();

        // Prime the cache the way DashboardController::stats() would.
        $this->getJson('/api/dashboard/stats')->assertOk();
        $this->assertTrue(Cache::has('dashboard_stats'));
        $before = Cache::get('dashboard_stats');
        $this->assertSame(0, $before['applications']['total']);

        $this->actingAs($applicant, 'sanctum')
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id])
            ->assertCreated();

        // The write should have evicted the stale cached value rather than
        // leaving it to expire on its own an hour later.
        $this->assertFalse(Cache::has('dashboard_stats'));

        $after = $this->getJson('/api/dashboard/stats')->assertOk()->json();
        $this->assertSame(1, $after['applications']['total']);
    }

    public function test_publishing_a_vacancy_invalidates_the_cached_dashboard_stats(): void
    {
        $admin = User::factory()->admin()->create();
        $vacancy = Vacancy::factory()->create(['status' => 'draft']);

        $this->getJson('/api/dashboard/stats')->assertOk();
        $this->assertTrue(Cache::has('dashboard_stats'));

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/vacancies/{$vacancy->id}/publish")
            ->assertOk();

        $this->assertFalse(Cache::has('dashboard_stats'));
    }
}
