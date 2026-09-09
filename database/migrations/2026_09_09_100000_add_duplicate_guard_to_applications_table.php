<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ApplicationController::store() only prevented duplicate applications with an
 * app-level check-then-create — two concurrent submissions for the same
 * applicant/vacancy could both pass the existence check before either INSERT
 * committed. A plain unique(['vacancy_id','applicant_id']) can't be used
 * because applications are soft-deleted and a re-application after a deleted
 * row is legitimate; MySQL/SQLite both treat NULL as distinct in a unique
 * index, so a generated column that collapses every *active* row to the same
 * sentinel (and every soft-deleted row to NULL) gives the same effect as a
 * partial/filtered unique index would on Postgres.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('active_duplicate_guard')
                ->nullable()
                ->storedAs('(CASE WHEN deleted_at IS NULL THEN 1 ELSE NULL END)')
                ->after('deleted_at');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->unique(
                ['vacancy_id', 'applicant_id', 'active_duplicate_guard'],
                'applications_active_vacancy_applicant_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique('applications_active_vacancy_applicant_unique');
            $table->dropColumn('active_duplicate_guard');
        });
    }
};
