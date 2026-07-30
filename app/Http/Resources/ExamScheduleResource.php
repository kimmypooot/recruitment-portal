<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'application_id' => $this->application_id,
            'exam_type'      => $this->exam_type,
            'scheduled_at'   => $this->scheduled_at?->toISOString(),
            'venue'          => $this->venue,
            'notes'          => $this->notes,
        ];
    }
}
