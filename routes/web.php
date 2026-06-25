<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
Route::get('/how-to-apply', fn () => Inertia::render('HowToApply'));
Route::get('/about', fn () => Inertia::render('About'));
Route::get('/privacy-policy', fn () => Inertia::render('PrivacyPolicy'));
Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');
Route::get('/register', fn () => Inertia::render('Auth/Register'));

// Password reset
Route::get('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
    ->middleware('guest')->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->middleware('guest')->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])
    ->middleware('guest')->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('guest')->name('password.update');

// Email verification
Route::get('/email/verify', fn () => Inertia::render('Auth/VerifyEmail'))
    ->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
    ->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/vacancies/{id}/apply', fn ($id) => Inertia::render('Vacancies/Apply', ['vacancyId' => (int) $id]));

Route::get('/profile/documents/{path}', [App\Http\Controllers\ProfileController::class, 'serveDocument'])
    ->where('path', '.*');
Route::get('/profile/photo', [App\Http\Controllers\ProfileController::class, 'servePhoto']);

Route::get('/auth/google', [AuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);
Route::get('/auth/google/callback-handler', fn () => Inertia::render('Auth/GoogleCallback'))
    ->name('auth.google.callback-handler');

// Authenticated applicant pages
Route::middleware('auth')->prefix('applicant')->group(function () {
    Route::get('/dashboard',        fn () => Inertia::render('Applicant/Dashboard'));
    Route::get('/applications',     fn () => Inertia::render('Applicant/Applications'));
    Route::get('/complete-profile', fn () => Inertia::render('Applicant/CompleteProfile'));
});

// Authenticated admin pages
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/dashboard',   fn () => Inertia::render('Admin/Dashboard'));
    Route::get('/vacancies',   fn () => Inertia::render('Admin/Vacancies'));
    Route::get('/applications',fn () => Inertia::render('Admin/Applications'));
    Route::get('/users',       fn () => Inertia::render('Admin/Users'));
    Route::get('/audit-logs',  fn () => Inertia::render('Admin/AuditLogs'));
    Route::get('/hrmpsb',               fn () => Inertia::render('Admin/Hrmpsb'));
    Route::get('/reports',              fn () => Inertia::render('Admin/Reports'));
    Route::get('/compliance',           fn () => Inertia::render('Admin/Compliance'));
    Route::get('/competencies', fn () => Inertia::render('Admin/Competencies'));
});

// Authenticated HRMPSB evaluation pages
Route::middleware('auth')->prefix('hrmpsb')->group(function () {
    Route::get('/dashboard',                fn () => Inertia::render('Hrmpsb/Dashboard'));
    Route::get('/qs-evaluation/{vacancy}',  fn ($vacancy) => Inertia::render('Hrmpsb/QsEvaluation', ['vacancyId' => (int) $vacancy]));
    Route::get('/qs-results/{vacancy}',     fn ($vacancy) => Inertia::render('Hrmpsb/QsResults', ['vacancyId' => (int) $vacancy]));
    Route::get('/exam-results/{vacancy}',   fn ($vacancy) => Inertia::render('Hrmpsb/ExamResults', ['vacancyId' => (int) $vacancy]));
    Route::get('/bei-rating/{vacancy}',     fn ($vacancy) => Inertia::render('Hrmpsb/BeiRating', ['vacancyId' => (int) $vacancy]));
    Route::get('/deliberation/{vacancy}',   fn ($vacancy) => Inertia::render('Hrmpsb/Deliberation',   ['vacancyId' => (int) $vacancy]));
    Route::get('/pre-assessment/{vacancy}', fn ($vacancy) => Inertia::render('Hrmpsb/PreAssessment', ['vacancyId' => (int) $vacancy]));
    Route::get('/exam-schedule/{vacancy}',  fn ($vacancy) => Inertia::render('Hrmpsb/ExamScheduler', ['vacancyId' => (int) $vacancy]));
    Route::get('/bei-schedule/{vacancy}',   fn ($vacancy) => Inertia::render('Hrmpsb/BeiScheduler',  ['vacancyId' => (int) $vacancy]));
});