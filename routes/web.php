<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
Route::get('/how-to-apply', fn () => Inertia::render('HowToApply'));
Route::get('/about', fn () => Inertia::render('About'));
Route::get('/login', fn () => Inertia::render('Auth/Login'));
Route::get('/register', fn () => Inertia::render('Auth/Register'));

Route::get('/vacancies/{id}/apply', fn ($id) => Inertia::render('Vacancies/Apply', ['vacancyId' => (int) $id]));

Route::get('/profile/documents/{path}', [App\Http\Controllers\ProfileController::class, 'serveDocument'])
    ->where('path', '.*');
Route::get('/profile/photo', [App\Http\Controllers\ProfileController::class, 'servePhoto']);

Route::get('/auth/google', [AuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);

// Applicant pages
Route::prefix('applicant')->group(function () {
    Route::get('/dashboard',        fn () => Inertia::render('Applicant/Dashboard'));
    Route::get('/applications',     fn () => Inertia::render('Applicant/Applications'));
    Route::get('/complete-profile', fn () => Inertia::render('Applicant/CompleteProfile'));
});

// Admin pages
Route::prefix('admin')->group(function () {
    Route::get('/dashboard',   fn () => Inertia::render('Admin/Dashboard'));
    Route::get('/vacancies',   fn () => Inertia::render('Admin/Vacancies'));
    Route::get('/applications',fn () => Inertia::render('Admin/Applications'));
    Route::get('/users',       fn () => Inertia::render('Admin/Users'));
    Route::get('/audit-logs',  fn () => Inertia::render('Admin/AuditLogs'));
});