<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STATUSES = [
        'submitted', 'under_review', 'screened', 'qualified', 'disqualified',
        'exam_scheduled', 'interviewed', 'shortlisted', 'for_interview',
        'recommended', 'appointed', 'completed', 'withdrawn',
    ];

    public function up(): void
    {
        // SQLite does not enforce ENUMs; only run the column change on MySQL
        if (DB::getDriverName() !== 'mysql') {
            DB::table('applications')->where('status', 'passed')->update(['status' => 'recommended']);
            DB::table('applications')->where('status', 'failed')->update(['status' => 'disqualified']);
            return;
        }

        // Step 1: Relax to varchar so we can freely remap values
        DB::statement("ALTER TABLE applications MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'submitted'");

        // Step 2: Remap legacy statuses that don't exist in the new canonical set
        DB::table('applications')->where('status', 'passed')->update(['status' => 'recommended']);
        DB::table('applications')->where('status', 'failed')->update(['status' => 'disqualified']);

        // Step 3: Apply the canonical enum
        $values = implode("','", self::STATUSES);
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM('{$values}') NOT NULL DEFAULT 'submitted'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            DB::table('applications')->where('status', 'recommended')->update(['status' => 'passed']);
            DB::table('applications')->where('status', 'disqualified')->update(['status' => 'failed']);
            return;
        }

        DB::statement("ALTER TABLE applications MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'submitted'");
        DB::table('applications')->where('status', 'recommended')->update(['status' => 'passed']);
        DB::table('applications')->where('status', 'disqualified')->update(['status' => 'failed']);
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM('submitted','under_review','exam_scheduled','interviewed','passed','failed','withdrawn') NOT NULL DEFAULT 'submitted'");
    }
};
