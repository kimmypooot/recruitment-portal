<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\PdsOtherInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PdsProfileSectionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ApplicantProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user    = User::factory()->applicant()->create();
        $this->profile = ApplicantProfile::factory()->create(['user_id' => $this->user->id]);
    }

    private function actor(): self
    {
        return $this->actingAs($this->user, 'sanctum');
    }

    public function test_the_pds_sections_require_authentication(): void
    {
        $this->getJson('/api/profile/pds')->assertUnauthorized();
    }

    public function test_it_returns_every_section_in_one_call(): void
    {
        $this->actor()->getJson('/api/profile/pds')
            ->assertOk()
            ->assertJsonStructure([
                'family_background', 'children', 'eligibilities',
                'voluntary_works', 'other_info', 'references', 'declaration',
            ]);
    }

    public function test_family_background_is_created_then_updated_in_place(): void
    {
        $this->actor()->putJson('/api/profile/pds/family-background', [
            'mother_maiden_surname' => 'Reyes',
        ])->assertOk();

        $this->actor()->putJson('/api/profile/pds/family-background', [
            'mother_maiden_surname' => 'Santos',
        ])->assertOk();

        // updateOrCreate must not produce a second row.
        $this->assertDatabaseCount('pds_family_backgrounds', 1);
        $this->assertSame('Santos', $this->profile->fresh()->familyBackground->mother_maiden_surname);
    }

    public function test_the_declaration_records_an_explicit_no(): void
    {
        $this->actor()->putJson('/api/profile/pds/declaration', [
            'q36_convicted' => false,
        ])->assertOk();

        $declaration = $this->profile->fresh()->declaration;

        $this->assertFalse($declaration->q36_convicted);
        $this->assertNull($declaration->q34a_related_third_degree, 'untouched questions must stay unanswered');
    }

    #[DataProvider('repeatingSections')]
    public function test_repeating_sections_support_create_update_and_delete(string $section, array $payload, string $field, string $changed): void
    {
        $created = $this->actor()->postJson("/api/profile/pds/{$section}", $payload)->assertCreated()->json();

        $this->actor()->putJson("/api/profile/pds/{$section}/{$created['id']}", [$field => $changed])
            ->assertOk()
            ->assertJsonPath($field, $changed);

        $this->actor()->deleteJson("/api/profile/pds/{$section}/{$created['id']}")->assertOk();

        $this->actor()->putJson("/api/profile/pds/{$section}/{$created['id']}", [$field => $changed])
            ->assertNotFound();
    }

    public static function repeatingSections(): array
    {
        return [
            'children' => [
                'children',
                ['full_name' => 'Ana Dela Cruz', 'date_of_birth' => '2015-04-02'],
                'full_name', 'Ana Marie Dela Cruz',
            ],
            'eligibilities' => [
                'eligibilities',
                ['eligibility_name' => 'Career Service Professional', 'rating' => '85.50'],
                'rating', '90.00',
            ],
            'voluntary works' => [
                'voluntary-works',
                ['organization_name_address' => 'Red Cross, Tacloban', 'number_of_hours' => 40],
                'position_nature_of_work', 'Volunteer',
            ],
            'other info' => [
                'other-info',
                ['kind' => PdsOtherInfo::KIND_MEMBERSHIP, 'value' => 'PASUC'],
                'value', 'PASUC Region VIII',
            ],
            'references' => [
                'references',
                ['name' => 'Maria Santos', 'contact_no_or_email' => 'maria@example.com'],
                'name', 'Maria Cruz',
            ],
        ];
    }

    public function test_an_unknown_section_is_rejected(): void
    {
        $this->actor()->postJson('/api/profile/pds/not-a-section', ['x' => 1])
            ->assertNotFound();
    }

    public function test_other_info_rejects_an_unknown_kind(): void
    {
        $this->actor()->postJson('/api/profile/pds/other-info', ['kind' => 'nonsense', 'value' => 'x'])
            ->assertStatus(422);
    }

    public function test_required_fields_are_enforced(): void
    {
        $this->actor()->postJson('/api/profile/pds/children', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('full_name');
    }

    public function test_a_user_cannot_touch_another_applicants_records(): void
    {
        $other = ApplicantProfile::factory()->create();
        $child = $other->children()->create(['full_name' => 'Not Yours']);

        $this->actor()->putJson("/api/profile/pds/children/{$child->id}", ['full_name' => 'Hijacked'])
            ->assertNotFound();

        $this->actor()->deleteJson("/api/profile/pds/children/{$child->id}")
            ->assertNotFound();

        $this->assertSame('Not Yours', $child->fresh()->full_name);
    }

    public function test_sort_order_defaults_to_the_end_of_the_list(): void
    {
        $first  = $this->actor()->postJson('/api/profile/pds/references', ['name' => 'First'])->json();
        $second = $this->actor()->postJson('/api/profile/pds/references', ['name' => 'Second'])->json();

        $this->assertSame(0, $first['sort_order']);
        $this->assertSame(1, $second['sort_order']);
    }
}
