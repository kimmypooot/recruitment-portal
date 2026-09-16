<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_password_revokes_other_tokens_but_keeps_the_current_one(): void
    {
        $user = User::factory()->applicant()->create([
            'password'     => Hash::make('OldPassword1'),
            'has_password' => true,
        ]);

        // Simulate a second, already-issued session (e.g. another device, or
        // a token an attacker obtained before this password change).
        $otherToken = $user->createToken('other-device')->plainTextToken;
        $currentToken = $user->createToken('this-request')->plainTextToken;

        $this->assertSame(2, PersonalAccessToken::where('tokenable_id', $user->id)->count());

        $this->withHeader('Authorization', "Bearer {$currentToken}")
            ->postJson('/api/change-password', [
                'current_password'      => 'OldPassword1',
                'password'               => 'NewPassword1',
                'password_confirmation'  => 'NewPassword1',
            ])
            ->assertOk();

        $remaining = PersonalAccessToken::where('tokenable_id', $user->id)->get();
        $this->assertCount(1, $remaining);
        $this->assertSame(
            explode('|', $currentToken, 2)[0],
            (string) $remaining->first()->id
        );
    }
}
