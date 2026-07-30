<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\VerifyEmail as VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const VERIFICATION_CODE_TTL_MINUTES = 15;

    public function sendEmailVerificationNotification(): void
    {
        $code = $this->generateEmailVerificationCode();

        $this->notify(new VerifyEmailNotification($code));
    }

    /**
     * Generate a new 6-digit verification code, store its hash, and return the plaintext code.
     */
    public function generateEmailVerificationCode(): string
    {
        $code = (string) random_int(100000, 999999);

        $this->forceFill([
            'email_verification_code' => Hash::make($code),
            'email_verification_code_expires_at' => now()->addMinutes(self::VERIFICATION_CODE_TTL_MINUTES),
        ])->save();

        return $code;
    }

    /**
     * Check whether the given plaintext code matches the stored (unexpired) code.
     */
    public function isValidEmailVerificationCode(string $code): bool
    {
        if (! $this->email_verification_code || ! $this->email_verification_code_expires_at) {
            return false;
        }

        if ($this->email_verification_code_expires_at->isPast()) {
            return false;
        }

        return Hash::check($code, $this->email_verification_code);
    }

    public function clearEmailVerificationCode(): void
    {
        $this->forceFill([
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
        ])->save();
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    protected $fillable = ['first_name', 'last_name', 'middle_name', 'suffix', 'email', 'password', 'has_password', 'role', 'google_id', 'google_avatar', 'photo_path', 'email_verified_at'];

    protected $hidden = ['password', 'remember_token', 'email_verification_code', 'email_verification_code_expires_at'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_code_expires_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'has_password' => 'boolean',
        ];
    }

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        $middle = $this->middle_name ? ' '.mb_substr($this->middle_name, 0, 1).'. ' : ' ';
        $suffix = $this->suffix ? ', '.$this->suffix : '';

        return $this->first_name.$middle.$this->last_name.$suffix;
    }

    public function applicantProfile(): HasOne
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function hrmbsCompositions(): HasMany
    {
        return $this->hasMany(HrmpsbComposition::class);
    }

    public function hrmbsDesignation(): ?string
    {
        return HrmpsbComposition::where('user_id', $this->id)
            ->where('is_active', true)
            ->value('hrmpsb_role');
    }

    public function hasAdminDesignation(): bool
    {
        return in_array($this->hrmbsDesignation(), ['secretariat', 'hr-chief']);
    }

    public function canAccessAdminModule(): bool
    {
        return $this->role === 'admin' || ($this->role === 'hrmpsb' && $this->hasAdminDesignation());
    }
}
