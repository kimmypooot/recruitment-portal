<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkExperienceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'position_title'    => $this->position_title,
            'department_agency' => $this->department_agency,
            'monthly_salary'    => $this->monthly_salary,
            'salary_grade'      => $this->salary_grade,
            'appointment_status' => $this->appointment_status,
            'government_service' => $this->government_service,
            'date_from'         => $this->date_from,
            'date_to'           => $this->date_to,
            'is_present'        => $this->is_present,
        ];
    }
}
