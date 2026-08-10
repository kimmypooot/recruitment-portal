<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\User;
use App\Services\Pds\PdsDataAssembler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The address is entered once, on the PDS Details tab, as the residential
 * `res_*` block. Region stays on Personal Info and narrows the province list.
 *
 * The legacy province/city_municipality/barangay columns are mirrored from the
 * residential address so existing consumers keep working until they are moved
 * off them.
 */
class ProfileAddressConsolidationTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): array
    {
        $user    = User::factory()->applicant()->create();
        $profile = ApplicantProfile::factory()->create(['user_id' => $user->id]);

        return [$user, $profile];
    }

    public function test_the_residential_address_is_mirrored_into_the_legacy_columns(): void
    {
        [$user, $profile] = $this->actor();

        $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
            'region'                => 'Region VIII',
            'res_province'          => 'Leyte',
            'res_city_municipality' => 'Tacloban City',
            'res_barangay'          => 'Barangay 20',
        ])->assertOk();

        $fresh = $profile->fresh();

        $this->assertSame('Leyte', $fresh->province);
        $this->assertSame('Tacloban City', $fresh->city_municipality);
        $this->assertSame('Barangay 20', $fresh->barangay);
    }

    public function test_the_legacy_columns_are_left_alone_when_no_residential_address_is_sent(): void
    {
        [$user, $profile] = $this->actor();
        $profile->forceFill(['province' => 'Samar'])->save();

        $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
            'region' => 'Region VIII',
        ])->assertOk();

        $this->assertSame('Samar', $profile->fresh()->province);
    }

    /**
     * The selects used to store PSGC codes, which would have printed as e.g.
     * "0837" on the form. Names are stored now, and the assembler passes them
     * through untouched.
     */
    public function test_the_form_prints_place_names_not_psgc_codes(): void
    {
        [$user, $profile] = $this->actor();

        $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
            'region'                 => 'Region VIII',
            'res_province'           => 'Leyte',
            'res_city_municipality'  => 'Tacloban City',
            'res_barangay'           => 'Barangay 20',
            'perm_province'          => 'Samar',
            'perm_city_municipality' => 'Catbalogan City',
        ])->assertOk();

        $address = app(PdsDataAssembler::class)
            ->assemble($profile->fresh())['personal_information'];

        $this->assertSame('Leyte', $address['residential_address']['province']);
        $this->assertSame('Tacloban City', $address['residential_address']['city_municipality']);
        $this->assertSame('Barangay 20', $address['residential_address']['barangay']);
        $this->assertSame('Samar', $address['permanent_address']['province']);

        foreach ($address['residential_address'] as $value) {
            $this->assertDoesNotMatchRegularExpression(
                '/^\d{4,}$/',
                (string) $value,
                'a PSGC code reached the form instead of a place name'
            );
        }
    }

    public function test_saving_personal_info_no_longer_requires_province_or_barangay(): void
    {
        [$user, $profile] = $this->actor();

        // Province, city and barangay now live on the PDS Details tab.
        $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
            'gender'        => 'Male',
            'civil_status'  => 'Single',
            'birthday'      => '1990-03-17',
            'mobile_number' => '09171234567',
            'region'        => 'Region VIII',
        ])->assertOk();

        $this->assertSame('Region VIII', $profile->fresh()->region);
    }
}
