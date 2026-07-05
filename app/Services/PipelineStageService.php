<?php

namespace App\Services;

use App\Models\Application;
use App\Models\BackgroundCheck;
use App\Models\BackgroundInvestigationReport;
use App\Models\BeiRating;
use App\Models\CbweRating;
use App\Models\DeliberationResult;
use App\Models\EoptResult;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\InterviewSchedule;
use App\Models\PreAssessment;
use App\Models\QsEvaluation;
use App\Models\Vacancy;

class PipelineStageService
{
    private const PREREQUISITES = [
        'pre-assessment' => null,
        'qs'             => 'pre_assessment_exists',
        'twe'            => 'qs_locked',
        'cbwe'           => 'twe_exists',
        'bei'            => 'cbwe_locked',
        'eopt'           => 'bei_locked',
        'background'     => 'eopt_exists',
        'deliberation'   => 'background_check_locked',
    ];

    public function isStageAccessible(string $stageKey, Vacancy $vacancy): bool
    {
        if (!isset(self::PREREQUISITES[$stageKey])) {
            return true;
        }

        $prerequisite = self::PREREQUISITES[$stageKey];

        if ($prerequisite === null) {
            return true;
        }

        $flags = $this->resolveFlags($vacancy);

        return $flags[$prerequisite] ?? false;
    }

    public function resolveFlags(Vacancy $vacancy): array
    {
        $appIds = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn'])
            ->pluck('id');

        $allAppIds = Application::where('vacancy_id', $vacancy->id)->pluck('id');

        if ($allAppIds->isEmpty()) {
            return [
                'pre_assessment_exists' => false,
                'qs_exists' => false,
                'qs_locked' => false,
                'twe_scheduled' => false,
                'twe_exists' => false,
                'cbwe_scheduled' => false,
                'cbwe_exists' => false,
                'cbwe_locked' => false,
                'bei_scheduled' => false,
                'bei_exists' => false,
                'bei_locked' => false,
                'eopt_exists' => false,
                'background_check_exists' => false,
                'background_check_locked' => false,
                'deliberation_exists' => false,
            ];
        }

        return [
            'pre_assessment_exists' => PreAssessment::whereIn('application_id', $appIds)->exists(),
            'qs_exists' => QsEvaluation::whereIn('application_id', $allAppIds)->exists(),
            'qs_locked' => QsEvaluation::whereIn('application_id', $allAppIds)->whereNotNull('locked_at')->exists(),
            'twe_scheduled' => ExamSchedule::whereIn('application_id', $appIds)->where('exam_type', 'TWE')->exists(),
            'twe_exists' => ExamResult::whereIn('application_id', $appIds)->where('exam_type', 'TWE')->exists(),
            'cbwe_scheduled' => ExamSchedule::whereIn('application_id', $appIds)->where('exam_type', 'CBWE')->exists(),
            'cbwe_exists' => CbweRating::whereIn('application_id', $appIds)->exists(),
            'cbwe_locked' => CbweRating::whereIn('application_id', $appIds)->whereNotNull('locked_at')->exists(),
            'bei_scheduled' => InterviewSchedule::whereIn('application_id', $appIds)->exists(),
            'bei_exists' => BeiRating::whereIn('application_id', $appIds)->exists(),
            'bei_locked' => BeiRating::whereIn('application_id', $appIds)->whereNotNull('locked_at')->exists(),
            'eopt_exists' => EoptResult::whereIn('application_id', $appIds)->exists(),
            'background_check_exists' => BackgroundCheck::whereIn('application_id', $appIds)->exists()
                || BackgroundInvestigationReport::whereIn('application_id', $appIds)->exists(),
            'background_check_locked' => BackgroundCheck::whereIn('application_id', $appIds)->whereNotNull('locked_at')->exists()
                || BackgroundInvestigationReport::whereIn('application_id', $appIds)->whereNotNull('locked_at')->exists(),
            'deliberation_exists' => DeliberationResult::where('vacancy_id', $vacancy->id)->whereNotNull('locked_at')->exists(),
        ];
    }
}
