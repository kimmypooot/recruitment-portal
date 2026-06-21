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

// Public routes
Route::get('/vacancies', [VacancyController::class, 'index']);
Route::get('/vacancies/{vacancy}', [VacancyController::class, 'show']);

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Authenticated applicant routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/my-applications', [ApplicationController::class, 'index']);
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::get('/applications/{application}', [ApplicationController::class, 'show']);
    Route::post('/applications/{application}/documents', [DocumentController::class, 'store']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/documents', [ProfileController::class, 'uploadDocuments']);

    // Work Experience
    Route::post('/profile/experiences', [ProfileController::class, 'storeExperience']);
    Route::delete('/profile/experiences/{id}', [ProfileController::class, 'deleteExperience']);

    // Education
    Route::post('/profile/education', [ProfileController::class, 'storeEducation']);
    Route::delete('/profile/education/{id}', [ProfileController::class, 'deleteEducation']);

    // Trainings
    Route::post('/profile/trainings', [ProfileController::class, 'storeTraining']);
    Route::delete('/profile/trainings/{id}', [ProfileController::class, 'deleteTraining']);
});

// HR Officer routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('vacancies', VacancyController::class)->except(['index', 'show']);
    Route::patch('/vacancies/{vacancy}/publish', [VacancyController::class, 'publish']);
    Route::patch('/vacancies/{vacancy}/archive', [VacancyController::class, 'archive']);
    Route::get('/applications', [ApplicationController::class, 'hrIndex']);
    Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus']);
    Route::apiResource('examinations', ExaminationController::class);
    Route::apiResource('interviews', InterviewController::class);
    Route::post('/documents/{document}/verify', [DocumentController::class, 'verify']);
    Route::get('/reports/{type}', [ReportController::class, 'generate']);
});

// Admin routes
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/dashboard-stats', [DashboardController::class, 'adminStats']);
});

// Dashboard routes
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
Route::get('/dashboard/recent-applications', [DashboardController::class, 'recentApplications']);
Route::get('/dashboard/pipeline', [DashboardController::class, 'pipeline']);
