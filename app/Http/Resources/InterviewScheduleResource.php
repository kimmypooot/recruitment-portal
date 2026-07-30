<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterviewScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'application_id' => $this->application_id,
            'scheduled_at'   => $this->scheduled_at?->toISOString(),
            'venue'          => $this->venue,
            'notes'          => $this->notes,
        ];
    }
}
