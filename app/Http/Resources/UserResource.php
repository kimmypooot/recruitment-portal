<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'first_name'       => $this->first_name,
            'last_name'        => $this->last_name,
            'middle_name'      => $this->middle_name,
            'suffix'           => $this->suffix,
            'full_name'        => $this->full_name,
            'email'            => $this->email,
            'role'             => $this->role,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'google_avatar'    => $this->google_avatar,
            'photo_path'       => $this->photo_path,
            'has_password'     => $this->has_password,
            'last_login_at'    => $this->last_login_at?->toISOString(),
            'created_at'       => $this->created_at?->toISOString(),
            'applicant_profile' => $this->whenLoaded('applicantProfile', fn () =>
                ApplicantProfileResource::make($this->applicantProfile)
            ),
        ];
    }
}
