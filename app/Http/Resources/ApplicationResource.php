<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'vacancy_id'      => $this->vacancy_id,
            'applicant_id'    => $this->applicant_id,
            'status'          => $this->status,
            'remarks'         => $this->remarks,
            'submitted_at'    => $this->submitted_at?->toISOString(),
            'reviewed_at'     => $this->reviewed_at?->toISOString(),
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),

            'vacancy'         => $this->whenLoaded('vacancy', fn () =>
                VacancyResource::make($this->vacancy)
            ),
            'applicant'       => $this->whenLoaded('applicant', fn () =>
                ApplicantProfileResource::make($this->applicant)
            ),
            'documents'       => DocumentResource::collection($this->whenLoaded('documents')),
            'exam_schedule'   => ExamScheduleResource::collection($this->whenLoaded('examSchedule')),
            'interview_schedule' => InterviewScheduleResource::collection($this->whenLoaded('interviewSchedule')),
            'pre_assessment'  => $this->whenLoaded('preAssessment'),
            'qs_evaluations'  => $this->whenLoaded('qsEvaluations'),
            'exam_results'    => $this->whenLoaded('examResults'),
            'bei_ratings'     => $this->whenLoaded('beiRatings'),
            'cbwe_ratings'    => $this->whenLoaded('cbweRatings'),
            'eopt_result'     => $this->whenLoaded('eoptResult'),
            'background_checks' => $this->whenLoaded('backgroundChecks'),
            'deliberation_results' => $this->whenLoaded('deliberationResults'),
        ];
    }
}
