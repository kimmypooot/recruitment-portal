<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_password')->default(true)->after('password');
        });

        // Google-created accounts received a random password the user never saw.
        // Accounts that registered with a password but linked Google later are also
        // marked false here (indistinguishable in existing data); the flag flips to
        // true the next time they change or reset their password.
        DB::table('users')->whereNotNull('google_id')->update(['has_password' => false]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_password');
        });
    }
};
