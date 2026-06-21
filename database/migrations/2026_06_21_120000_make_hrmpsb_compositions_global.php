<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hrmpsb_compositions', function (Blueprint $table) {
            // FK must be dropped before its supporting index can be dropped
            $table->dropForeign(['vacancy_id']);
            $table->dropUnique(['vacancy_id', 'user_id', 'hrmpsb_role']);
            $table->dropColumn('vacancy_id');

            // One person per role globally
            $table->unique(['user_id', 'hrmpsb_role']);
        });
    }

    public function down(): void
    {
        Schema::table('hrmpsb_compositions', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'hrmpsb_role']);
            $table->foreignId('vacancy_id')->after('id')->constrained()->onDelete('cascade');
            $table->unique(['vacancy_id', 'user_id', 'hrmpsb_role']);
        });
    }
};
