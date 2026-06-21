<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VacancyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'position_title'      => $this->position_title,
            'item_number'         => $this->item_number,
            'salary_grade'        => $this->salary_grade,
            'plantilla_number'    => $this->plantilla_number,
            'place_of_assignment' => $this->place_of_assignment,
            'education_req'       => $this->education_req,
            'experience_req'      => $this->experience_req,
            'training_req'        => $this->training_req,
            'eligibility_req'     => $this->eligibility_req,
            'status'              => $this->status,
            'published_at'        => $this->published_at?->toISOString(),
            'deadline_at'         => $this->deadline_at?->toISOString(),
            'posted_by'           => $this->whenLoaded('postedBy', fn () => [
                'id'   => $this->postedBy->id,
                'name' => $this->postedBy->name,
            ]),
        ];
    }
}
