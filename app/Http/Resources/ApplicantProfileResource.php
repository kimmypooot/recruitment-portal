<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicantProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'user_id'            => $this->user_id,
            'first_name'         => $this->first_name,
            'last_name'          => $this->last_name,
            'middle_name'        => $this->middle_name,
            'suffix'             => $this->suffix,
            'gender'             => $this->gender,
            'civil_status'       => $this->civil_status,
            'birthday'           => $this->birthday?->toISOString(),
            'religion'           => $this->religion,
            'region'             => $this->region,
            'province'           => $this->province,
            'city_municipality'  => $this->city_municipality,
            'barangay'           => $this->barangay,
            'mobile_number'      => $this->mobile_number,
            'eligibility'        => $this->eligibility,
            'eligibility_other'  => $this->eligibility_other,
            'indigenous_group'   => $this->indigenous_group,
            'pwd'                => $this->pwd,
            'solo_parent'        => $this->solo_parent,
            'photo_path'         => $this->photo_path,
            'profile_completed_at' => $this->profile_completed_at?->toISOString(),
            'is_complete'        => $this->isComplete(),
            'has_required_documents' => $this->hasRequiredDocuments(),
            'work_experiences'   => WorkExperienceResource::collection($this->whenLoaded('workExperiences')),
            'educational_attainments' => EducationalAttainmentResource::collection($this->whenLoaded('educationalAttainments')),
            'trainings'          => TrainingResource::collection($this->whenLoaded('trainings')),
            'user'               => $this->whenLoaded('user', fn () =>
                UserResource::make($this->user)
            ),
        ];
    }
}
