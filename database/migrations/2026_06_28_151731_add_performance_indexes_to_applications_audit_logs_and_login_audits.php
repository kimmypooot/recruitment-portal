<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->index('status', 'applications_status_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['action', 'created_at'], 'audit_logs_action_created_idx');
        });

        Schema::table('login_audits', function (Blueprint $table) {
            $table->index(['user_id', 'event'], 'login_audits_user_event_idx');
            $table->index('created_at', 'login_audits_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('applications_status_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_action_created_idx');
        });

        Schema::table('login_audits', function (Blueprint $table) {
            $table->dropIndex('login_audits_user_event_idx');
            $table->dropIndex('login_audits_created_idx');
        });
    }
};
