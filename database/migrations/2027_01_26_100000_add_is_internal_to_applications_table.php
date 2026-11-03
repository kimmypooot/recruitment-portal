<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Whether the applicant was a signed-in IMIS employee when they applied.
        // A snapshot: it should not change if the account is relinked later.
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('is_internal')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });
    }
};
