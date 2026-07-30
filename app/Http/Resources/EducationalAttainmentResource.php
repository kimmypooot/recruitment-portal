<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationalAttainmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'level'          => $this->level,
            'school_name'    => $this->school_name,
            'degree_course'  => $this->degree_course,
            'period_from'    => $this->period_from,
            'period_to'      => $this->period_to,
            'units_earned'   => $this->units_earned,
            'year_graduated' => $this->year_graduated,
            'honors'         => $this->honors,
        ];
    }
}
