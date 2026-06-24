<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HrmbsboardController;
use App\Http\Controllers\QsEvaluationController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\BeiRatingController;
use App\Http\Controllers\DeliberationController;
use App\Http\Controllers\CsFormController;
use App\Http\Controllers\VacancyCompetencyController;
use App\Http\Controllers\CompetencyController;
use App\Http\Controllers\PreAssessmentController;

// Public routes
Route::get('/vacancies', [VacancyController::class, 'index']);
Route::get('/vacancies/{vacancy}', [VacancyController::class, 'show']);
Route::get('/competencies', [VacancyCompetencyController::class, 'index']);

// Authentication — throttled to prevent brute-force and registration spam
Route::middleware('throttle:5,1')->post('/login', [AuthController::class, 'login']);
Route::middleware('throttle:3,60')->post('/register', [AuthController::class, 'register']);

// Privacy consent re-acknowledgment (for existing users when policy updates)
Route::middleware(['auth:sanctum'])->post('/privacy-consent', [AuthController::class, 'recordConsent']);
Route::middleware(['auth:sanctum'])->post('/auth/google/link', [AuthController::class, 'googleLinkApi']);
Route::middleware(['auth:sanctum'])->post('/auth/google/unlink', [AuthController::class, 'googleUnlink']);

// Dashboard — aggregate counts only, safe for public
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// Dashboard — applicant PII; requires authentication
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard/recent-applications', [DashboardController::class, 'recentApplications']);
    Route::get('/dashboard/pipeline', [DashboardController::class, 'pipeline']);
});

// Authenticated applicant routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/my-applications', [ApplicationController::class, 'index']);
    Route::middleware('throttle:10,1')->post('/applications', [ApplicationController::class, 'store']);
    Route::get('/applications/{application}', [ApplicationController::class, 'show']);
    Route::post('/applications/{application}/documents', [DocumentController::class, 'store']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::post('/profile/documents', [ProfileController::class, 'uploadDocuments']);

    // Work Experience
    Route::post('/profile/experiences', [ProfileController::class, 'storeExperience']);
    Route::put('/profile/experiences/{id}', [ProfileController::class, 'updateExperience']);
    Route::delete('/profile/experiences/{id}', [ProfileController::class, 'deleteExperience']);

    // Education
    Route::post('/profile/education', [ProfileController::class, 'storeEducation']);
    Route::put('/profile/education/{id}', [ProfileController::class, 'updateEducation']);
    Route::delete('/profile/education/{id}', [ProfileController::class, 'deleteEducation']);

    // Trainings
    Route::post('/profile/trainings', [ProfileController::class, 'storeTraining']);
    Route::put('/profile/trainings/{id}', [ProfileController::class, 'updateTraining']);
    Route::delete('/profile/trainings/{id}', [ProfileController::class, 'deleteTraining']);
});

// HR Officer routes
Route::middleware(['auth:sanctum', 'role:hr-officer,hr-manager,admin'])->group(function () {
    Route::get('/dashboard/upcoming', [DashboardController::class, 'upcoming']);
    Route::apiResource('vacancies', VacancyController::class)->except(['index', 'show']);
    Route::patch('/vacancies/{vacancy}/publish', [VacancyController::class, 'publish']);
    Route::patch('/vacancies/{vacancy}/archive', [VacancyController::class, 'archive']);
    Route::get('/applications', [ApplicationController::class, 'hrIndex']);
    Route::get('/vacancy-application-summary', [ApplicationController::class, 'vacancySummary']);
    Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus']);
    Route::apiResource('examinations', ExaminationController::class);
    Route::apiResource('interviews', InterviewController::class);
    Route::post('/documents/{document}/verify', [DocumentController::class, 'verify']);
    Route::get('/reports/{type}', [ReportController::class, 'generate']);

    // CS Forms
    Route::get('/applications/{application}/applicant-profile', [ApplicationController::class, 'applicantProfile']);
    Route::get('/applications/{application}/applicant-documents/{type}', [ApplicationController::class, 'serveApplicantDocument']);

    Route::get('/applications/{application}/forms', [CsFormController::class, 'index']);
    Route::post('/applications/{application}/forms', [CsFormController::class, 'generate']);
    Route::get('/forms/{csForm}/download', [CsFormController::class, 'download']);
    Route::patch('/forms/{csForm}/mark-submitted', [CsFormController::class, 'markSubmitted']);
    Route::patch('/forms/{csForm}/sign', [CsFormController::class, 'sign']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/dashboard-stats', [DashboardController::class, 'adminStats']);

    // Competency library management (admin)
    Route::get('/competencies', [CompetencyController::class, 'index']);
    Route::post('/competencies', [CompetencyController::class, 'store']);
    Route::put('/competencies/{competency}', [CompetencyController::class, 'update']);
    Route::delete('/competencies/{competency}', [CompetencyController::class, 'destroy']);

    // Vacancy competency management (admin)
    Route::get('/competencies/vacancy/{vacancy}', [VacancyCompetencyController::class, 'byVacancy']);
    Route::post('/competencies/vacancy/{vacancy}/sync', [VacancyCompetencyController::class, 'sync']);
    Route::delete('/competencies/vacancy/{vacancy}/{competencyKey}', [VacancyCompetencyController::class, 'remove']);

    // HRMPSB fixed board composition management
    Route::get('/hrmpsb/compositions', [HrmbsboardController::class, 'compositions']);
    Route::post('/hrmpsb/compositions', [HrmbsboardController::class, 'assign']);
    Route::delete('/hrmpsb/compositions/{composition}', [HrmbsboardController::class, 'remove']);
    Route::patch('/hrmpsb/compositions/{composition}/toggle-type', [HrmbsboardController::class, 'toggleType']);
    Route::patch('/hrmpsb/compositions/{composition}/toggle-active', [HrmbsboardController::class, 'toggleActive']);
});

// HRMPSB evaluation routes (members + secretariat + admin)
Route::middleware(['auth:sanctum', 'role:hrmpsb-member,hrmpsb-secretariat,admin,hr-manager,appointing-authority'])->group(function () {
    Route::get('/hrmpsb/my-role', [HrmbsboardController::class, 'myRole']);
    Route::get('/hrmpsb/pipeline-stages', [HrmbsboardController::class, 'pipelineStages']);
    Route::get('/hrmpsb/applications/{application}/profile', [HrmbsboardController::class, 'applicantProfile']);
    Route::get('/hrmpsb/applications/{application}/documents/{type}', [HrmbsboardController::class, 'serveDocument']);
    // QS Evaluation
    Route::get('/qs-evaluations/{vacancy}', [QsEvaluationController::class, 'index']);
    Route::post('/qs-evaluations', [QsEvaluationController::class, 'store']);
    Route::put('/qs-evaluations/{qsEvaluation}', [QsEvaluationController::class, 'update']);
    Route::patch('/qs-evaluations/{vacancy}/lock', [QsEvaluationController::class, 'lock']);

    // Exam Scheduler (TWE & CBWE)
    Route::get('/exam-schedules/{vacancy}', [ExaminationController::class, 'vacancySchedules']);
    Route::post('/exam-schedules', [ExaminationController::class, 'storeHrmpsb']);
    Route::post('/exam-schedules/batch/{vacancy}', [ExaminationController::class, 'batchSchedule']);
    Route::post('/exam-schedules/notify/{vacancy}', [ExaminationController::class, 'notifyApplicants']);
    Route::put('/exam-schedules/{examination}', [ExaminationController::class, 'update']);
    Route::delete('/exam-schedules/{examination}', [ExaminationController::class, 'destroy']);

    // BEI Scheduler
    Route::get('/bei-schedules/{vacancy}', [InterviewController::class, 'vacancySchedules']);
    Route::post('/bei-schedules', [InterviewController::class, 'storeHrmpsb']);
    Route::post('/bei-schedules/batch/{vacancy}', [InterviewController::class, 'batchSchedule']);
    Route::post('/bei-schedules/notify/{vacancy}', [InterviewController::class, 'notifyApplicants']);
    Route::put('/bei-schedules/{interview}', [InterviewController::class, 'update']);
    Route::delete('/bei-schedules/{interview}', [InterviewController::class, 'destroy']);

    // Exam Results
    Route::get('/exam-results/{vacancy}', [ExamResultController::class, 'index']);
    Route::post('/exam-results', [ExamResultController::class, 'store']);
    Route::delete('/exam-results/{examResult}', [ExamResultController::class, 'destroy']);

    // BEI Ratings
    Route::get('/bei-ratings/{vacancy}', [BeiRatingController::class, 'index']);
    Route::post('/bei-ratings', [BeiRatingController::class, 'store']);
    Route::patch('/bei-ratings/{vacancy}/lock', [BeiRatingController::class, 'lock']);

    // Pre-Assessment Matrix
    Route::get('/pre-assessment/{vacancy}', [PreAssessmentController::class, 'index']);
    Route::post('/pre-assessment/{application}', [PreAssessmentController::class, 'upsert']);

    // Deliberation
    Route::get('/deliberation/{vacancy}', [DeliberationController::class, 'index']);
    Route::patch('/deliberation/{vacancy}/unmask', [DeliberationController::class, 'unmask']);
    Route::post('/deliberation/{vacancy}/decide', [DeliberationController::class, 'decide']);
});
