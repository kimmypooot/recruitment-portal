<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'date_from'    => $this->date_from,
            'date_to'      => $this->date_to,
            'hours'        => $this->hours,
            'ld_type'      => $this->ld_type,
            'conducted_by' => $this->conducted_by,
        ];
    }
}
