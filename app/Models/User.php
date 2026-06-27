<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'password', 'role', 'google_id', 'google_avatar'];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function applicantProfile(): HasOne
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function hrmbsCompositions(): HasMany
    {
        return $this->hasMany(HrmbsboardComposition::class);
    }

    public function hrmbsDesignation(): ?string
    {
        return HrmbsboardComposition::where('user_id', $this->id)
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
