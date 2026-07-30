<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'application_id'  => $this->application_id,
            'type'            => $this->type,
            'file_path'       => $this->file_path,
            'original_name'   => $this->original_name,
            'mime_type'       => $this->mime_type,
            'size'            => $this->size,
            'verified_at'     => $this->verified_at?->toISOString(),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
