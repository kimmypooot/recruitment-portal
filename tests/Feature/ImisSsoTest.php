<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImisSsoTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $imisAnswer = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.imis.sso_url' => 'https://imis.test/api/v1/sso/validate', 'services.imis.audience' => 'recruitment.test']);

        // Registered once; the answer is read when the request is made.
        Http::fake(['imis.test/*' => fn () => Http::response($this->imisAnswer)]);
    }

    /** What IMIS answers for a person, overridable per test. */
    private function imis(array $user = []): void
    {
        $this->imisAnswer = ['success' => true, 'user' => $user + [
            'id' => 7, 'username' => 'hrstaff', 'fname' => 'Juan', 'lname' => 'Cruz', 'mname' => 'Dela',
            'email' => 'juan@csc.test', 'role' => 'Admin', 'role_code' => 'admin',
        ]];
    }

    private function signIn(string $token = 'abc')
    {
        return $this->postJson('/api/sso', ['sso_token' => $token]);
    }

    public function test_the_link_from_imis_shows_the_splash_page(): void
    {
        $this->get('/sso?sso_token=abc')->assertOk();
    }

    public function test_an_account_is_created_on_first_sign_in_with_the_imis_role(): void
    {
        $this->imis();

        $response = $this->signIn()->assertOk()->assertJsonPath('user.role', 'admin')->assertJsonPath('user.email', 'juan@csc.test');

        $user = User::where('imis_user_id', 7)->firstOrFail();
        $this->assertSame('Juan', $user->first_name);
        $this->assertSame('Cruz', $user->last_name);
        $this->assertFalse($user->has_password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotEmpty($response->json('token'));
        Http::assertSent(fn ($r) => $r['token'] === 'abc' && $r['audience'] === 'recruitment.test');

        // The token works against the API as that user.
        $this->withHeader('Authorization', 'Bearer '.$response->json('token'))->getJson('/api/me')->assertOk();
    }

    public function test_board_members_get_the_hrmpsb_role(): void
    {
        $this->imis(['role_code' => 'hrmpsb']);

        $this->signIn()->assertOk()->assertJsonPath('user.role', 'hrmpsb');
    }

    public function test_an_existing_staff_account_is_linked_by_email_and_its_role_is_rewritten(): void
    {
        $existing = User::factory()->hrmpsb()->create(['email' => 'juan@csc.test']);
        $this->imis(['role_code' => 'admin']);

        $this->signIn()->assertOk();

        $this->assertSame(7, $existing->fresh()->imis_user_id);
        $this->assertSame('admin', $existing->fresh()->role);
        $this->assertSame(1, User::where('email', 'juan@csc.test')->count());
    }

    public function test_role_is_rewritten_from_imis_on_every_sign_in(): void
    {
        $this->imis(['role_code' => 'admin']);
        $this->signIn()->assertOk();

        $this->imis(['role_code' => 'hrmpsb']);
        $this->signIn('def')->assertOk()->assertJsonPath('user.role', 'hrmpsb');

        $this->assertSame(1, User::where('imis_user_id', 7)->count());
    }

    public function test_an_applicant_with_the_same_email_is_never_promoted(): void
    {
        $applicant = User::factory()->applicant()->create(['email' => 'juan@csc.test']);
        $this->imis();

        $this->signIn()->assertStatus(409);

        $this->assertSame('applicant', $applicant->fresh()->role);
        $this->assertNull($applicant->fresh()->imis_user_id);
    }

    public function test_no_role_applicant_role_or_unknown_role_is_refused(): void
    {
        foreach ([null, 'applicant', 'made_up'] as $code) {
            $this->imis(['role_code' => $code]);
            $this->signIn()->assertForbidden();
        }

        $this->assertSame(0, User::where('imis_user_id', 7)->count());
    }

    public function test_an_invalid_token_or_a_missing_one_is_refused_without_a_401(): void
    {
        $this->imisAnswer = ['success' => false];
        $this->signIn('bad')->assertStatus(422);

        $this->postJson('/api/sso')->assertStatus(422);
        $this->assertSame(0, User::where('imis_user_id', 7)->count());
    }

    public function test_a_linked_staff_account_cannot_use_a_password_or_google(): void
    {
        $linked = User::factory()->admin()->create(['imis_user_id' => 9]);
        $this->postJson('/api/login', ['email' => $linked->email, 'password' => 'password'])->assertForbidden();

        // An unlinked admin keeps the password (the break-glass way in), as do applicants.
        $unlinked = User::factory()->admin()->create();
        $this->postJson('/api/login', ['email' => $unlinked->email, 'password' => 'password'])->assertOk();

        $applicant = User::factory()->applicant()->create();
        $this->postJson('/api/login', ['email' => $applicant->email, 'password' => 'password'])->assertOk();
    }

    public function test_password_sign_in_is_unchanged_while_imis_sign_in_is_off(): void
    {
        config(['services.imis.sso_url' => null]);
        $linked = User::factory()->admin()->create(['imis_user_id' => 9]);

        $this->postJson('/api/login', ['email' => $linked->email, 'password' => 'password'])->assertOk();
    }
}
