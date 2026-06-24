<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyConsent extends Model
{
    protected $fillable = [
        'user_id',
        'policy_version',
        'accepted_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(User $user, string $policyVersion, \Illuminate\Http\Request $request): self
    {
        return self::create([
            'user_id'        => $user->id,
            'policy_version' => $policyVersion,
            'accepted_at'    => now(),
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);
    }
}
