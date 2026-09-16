<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `users.role` was created as enum('applicant','hr-officer','hr-manager','admin'),
 * which SQLite and Postgres realise as a CHECK constraint.
 *
 * 2026_06_27_100000_simplify_users_role_to_three only rewrites the constraint on
 * MySQL — for every other driver it early-returns after migrating data, so the
 * original four-value constraint survives and `role = 'hrmpsb'` is rejected.
 * The practical effect is that no HRMPSB user can be created on SQLite, which is
 * what the test suite runs on, so the entire role was untestable.
 *
 * Converted to a plain string rather than a rebuilt CHECK, matching how
 * vacancies.status was already handled in this codebase. UserController::ALLOWED_ROLES
 * remains the enforcement point.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('applicant')->change();
        });
    }

    public function down(): void
    {
        // Irreversible by design: restoring the old constraint would reject the
        // 'hrmpsb' rows this migration makes possible.
    }
};
