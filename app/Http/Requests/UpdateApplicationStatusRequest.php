<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'  => 'required|in:under_review,screened,qualified,disqualified,exam_scheduled,interviewed,shortlisted,for_interview,recommended,appointed,completed,withdrawn',
            'remarks' => 'nullable|string|max:1000',
            'reason'  => 'nullable|string|max:1000',
        ];
    }
}
