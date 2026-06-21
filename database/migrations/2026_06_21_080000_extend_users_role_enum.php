<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'applicant','hr-officer','hr-manager','admin',
            'hrmpsb-member','hrmpsb-secretariat','appointing-authority'
        ) NOT NULL DEFAULT 'applicant'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'applicant','hr-officer','hr-manager','admin'
        ) NOT NULL DEFAULT 'applicant'");
    }
};
