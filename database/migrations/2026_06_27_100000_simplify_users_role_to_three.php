<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            DB::table('users')->whereIn('role', ['hr-officer', 'hr-manager'])->update(['role' => 'admin']);
            DB::table('users')->whereIn('role', [
                'hrmpsb-member', 'hrmpsb-secretariat', 'appointing-authority', 'hr-chief',
            ])->update(['role' => 'hrmpsb']);
            return;
        }

        // Step 1: expand enum to include all old + new values so data migration won't violate constraints
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'applicant','hr-officer','hr-manager','admin',
            'hrmpsb-member','hrmpsb-secretariat','appointing-authority','hr-chief','hrmpsb'
        ) NOT NULL DEFAULT 'applicant'");

        // Step 2: migrate data
        DB::table('users')->whereIn('role', ['hr-officer', 'hr-manager'])->update(['role' => 'admin']);
        DB::table('users')->whereIn('role', [
            'hrmpsb-member', 'hrmpsb-secretariat', 'appointing-authority', 'hr-chief',
        ])->update(['role' => 'hrmpsb']);

        // Step 3: shrink enum to the 3 final values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'applicant','hrmpsb','admin'
        ) NOT NULL DEFAULT 'applicant'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'applicant','hr-officer','hr-manager','admin',
            'hrmpsb-member','hrmpsb-secretariat','appointing-authority'
        ) NOT NULL DEFAULT 'applicant'");
    }
};
