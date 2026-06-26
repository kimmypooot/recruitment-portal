<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VacancyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'position_title'         => $this->position_title,
            'plantilla_no'           => $this->plantilla_no,
            'salary_grade'           => $this->salary_grade,
            'monthly_salary'         => $this->monthly_salary,
            'position_level'         => $this->position_level,
            'is_anticipated_vacancy' => $this->is_anticipated_vacancy,
            'place_of_assignment'    => $this->place_of_assignment,
            'education_req'          => $this->education_req,
            'experience_req'         => $this->experience_req,
            'training_req'           => $this->training_req,
            'eligibility_req'        => $this->eligibility_req,
            'status'                 => $this->status,
            'published_at'           => $this->published_at?->toISOString(),
            'deadline_at'            => $this->deadline_at?->toISOString(),
            'applications_count'     => $this->when(isset($this->applications_count), $this->applications_count),
            'posted_by'              => $this->whenLoaded('postedBy', fn () => [
                'id'   => $this->postedBy->id,
                'name' => $this->postedBy->name,
            ]),
            'competencies'           => $this->whenLoaded('competencies', fn () =>
                $this->competencies->map(fn ($vc) => [
                    'competency_key'   => $vc->competency_key,
                    'competency_level' => $vc->competency_level,
                    'competency_name'  => $vc->competency?->competency_name,
                    'competency_group' => $vc->competency?->competency_group,
                    'description'      => $vc->competency?->description,
                ])
            ),
        ];
    }
}
