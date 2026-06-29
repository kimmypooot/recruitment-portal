<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AppointingAuthorityController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackgroundCheckController;
use App\Http\Controllers\BackgroundInvestigationController;
use App\Http\Controllers\BeiRatingController;
use App\Http\Controllers\CbweRatingController;
use App\Http\Controllers\ComparativeAssessmentController;
use App\Http\Controllers\CompetencyController;
use App\Http\Controllers\CsFormController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliberationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EoptController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HrmbsboardController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PreAssessmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QsEvaluationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacancyCompetencyController;
use App\Http\Controllers\VacancyController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/vacancies', [VacancyController::class, 'index']);
Route::get('/vacancies/{vacancy}', [VacancyController::class, 'show']);
Route::get('/testimonials', [FeedbackController::class, 'testimonials']);
Route::get('/competencies', [VacancyCompetencyController::class, 'index']);

// Authentication — throttled to prevent brute-force and registration spam
Route::middleware('throttle:5,1')->post('/login', [AuthController::class, 'login']);
Route::middleware('throttle:3,60')->post('/register', [AuthController::class, 'register']);

// Privacy consent re-acknowledgment (for existing users when policy updates)
Route::middleware(['auth:sanctum'])->post('/privacy-consent', [AuthController::class, 'recordConsent']);
Route::middleware(['auth:sanctum'])->post('/logout', [AuthController::class, 'logout']);
Route::middleware(['auth:sanctum'])->post('/change-password', [AuthController::class, 'changePassword']);
Route::middleware(['auth:sanctum'])->post('/auth/google/link', [AuthController::class, 'googleLinkApi']);
Route::middleware(['auth:sanctum'])->post('/auth/google/unlink', [AuthController::class, 'googleUnlink']);

// Dashboard — aggregate counts only, safe for public
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// Dashboard — applicant PII; requires authentication
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard/recent-applications', [DashboardController::class, 'recentApplications']);
    Route::get('/dashboard/pipeline', [DashboardController::class, 'pipeline']);
    Route::get('/dashboard/upcoming', [DashboardController::class, 'upcoming']);
});

// Authenticated applicant routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/my-applications', [ApplicationController::class, 'index']);
    Route::middleware('throttle:10,1')->post('/applications', [ApplicationController::class, 'store']);
    Route::get('/applications/{application}', [ApplicationController::class, 'show']);
    Route::patch('/applications/{application}/withdraw', [ApplicationController::class, 'withdraw']);
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

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);

    // Feedback
    Route::post('/applications/{application}/feedback', [FeedbackController::class, 'store']);
});

// HR / Admin management routes (admin role + hrmpsb with secretariat/hr-chief designation)
Route::middleware(['auth:sanctum', 'admin-access'])->group(function () {
    Route::apiResource('vacancies', VacancyController::class)->except(['index', 'show']);
    Route::patch('/vacancies/bulk-status', [VacancyController::class, 'bulkUpdateStatus']);
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
});

// Admin module routes (user management, audit logs, HRMPSB compositions)
Route::middleware(['auth:sanctum', 'admin-access'])->prefix('admin')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/dashboard-stats', [DashboardController::class, 'adminStats']);
    Route::get('/feedbacks', [FeedbackController::class, 'index']);

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

// Exports
Route::middleware(['auth:sanctum', 'admin-access'])->prefix('exports')->group(function () {
    Route::get('/applicants/{vacancy?}', [ExportController::class, 'applicants']);
    Route::get('/audit-logs', [ExportController::class, 'auditLogs']);
});

// HRMPSB evaluation routes (all hrmpsb users + admin)
Route::middleware(['auth:sanctum', 'role:hrmpsb,admin'])->group(function () {
    Route::get('/hrmpsb/my-role', [HrmbsboardController::class, 'myRole']);
    Route::get('/hrmpsb/pipeline-stages', [HrmbsboardController::class, 'pipelineStages']);
    Route::get('/hrmpsb/applications/{application}/profile', [HrmbsboardController::class, 'applicantProfile']);
    Route::get('/hrmpsb/applications/{application}/documents/{type}', [HrmbsboardController::class, 'serveDocument']);
    Route::get('/hrmpsb/vacancies/{vacancy}/applicants', [HrmbsboardController::class, 'vacancyApplicants']);
    Route::post('/hrmpsb/vacancies/{vacancy}/download-requirements', [HrmbsboardController::class, 'downloadRequirements']);
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

    // CBWE Ratings (competency-based evaluation, replaces written CBWE exam)
    Route::get('/cbwe-ratings/{vacancy}', [CbweRatingController::class, 'index']);
    Route::post('/cbwe-ratings', [CbweRatingController::class, 'store']);
    Route::patch('/cbwe-ratings/{vacancy}/lock', [CbweRatingController::class, 'lock']);

    // EOPT
    Route::get('/eopt/{vacancy}', [EoptController::class, 'index']);
    Route::post('/eopt/{vacancy}', [EoptController::class, 'store']);

    // Pre-Assessment Matrix
    Route::get('/pre-assessment/{vacancy}', [PreAssessmentController::class, 'index']);
    Route::post('/pre-assessment/{application}', [PreAssessmentController::class, 'upsert']);

    // Deliberation
    Route::get('/deliberation/{vacancy}', [DeliberationController::class, 'index']);
    Route::patch('/deliberation/{vacancy}/unmask', [DeliberationController::class, 'unmask']);
    Route::patch('/deliberation/{vacancy}/lock', [DeliberationController::class, 'lock']);
    Route::post('/deliberation/{vacancy}/decide', [DeliberationController::class, 'decide']);

    // Comparative Assessment Result
    Route::get('/deliberation/{vacancy}/comparative-assessment', [ComparativeAssessmentController::class, 'index']);
    Route::post('/deliberation/{vacancy}/comparative-assessment/generate', [ComparativeAssessmentController::class, 'generate']);
    Route::get('/deliberation/{vacancy}/comparative-assessment/download', [ComparativeAssessmentController::class, 'download']);

    // Appointing Authority
    Route::get('/deliberation/{vacancy}/appointing-authority', [AppointingAuthorityController::class, 'index']);
    Route::patch('/deliberation/{vacancy}/appointing-authority/decide', [AppointingAuthorityController::class, 'decide']);
    Route::get('/hrmpsb/appointing-authority/decisions', [AppointingAuthorityController::class, 'secretariatDecisions']);

    // CS Forms (accessible by all hrmpsb members including appointing-authority)
    Route::get('/applications/{application}/forms', [CsFormController::class, 'index']);
    Route::post('/applications/{application}/forms', [CsFormController::class, 'generate']);
    Route::get('/forms/{csForm}/download', [CsFormController::class, 'download']);
    Route::patch('/forms/{csForm}/mark-submitted', [CsFormController::class, 'markSubmitted']);
    Route::patch('/forms/{csForm}/sign', [CsFormController::class, 'sign']);

    // Background Investigation
    Route::get('/background-checks/{vacancy}', [BackgroundCheckController::class, 'index']);
    Route::post('/background-checks', [BackgroundCheckController::class, 'store']);
    Route::patch('/background-checks/{vacancy}/lock', [BackgroundCheckController::class, 'lock']);

    // Background Investigation Reports
    Route::get('/background-investigation/{vacancy}', [BackgroundInvestigationController::class, 'index']);
    Route::post('/background-investigation/generate-link', [BackgroundInvestigationController::class, 'generateLink']);
    Route::post('/background-investigation/resend-link/{report}', [BackgroundInvestigationController::class, 'resendLink']);
    Route::delete('/background-investigation/revoke-link/{report}', [BackgroundInvestigationController::class, 'revokeLink']);
});
