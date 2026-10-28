<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An IMIS sign-in's API token remembers the `sid` IMIS gave (hashed), so
     * signing out of IMIS can revoke it. Null for every other token.
     */
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->char('imis_sid_hash', 64)->nullable()->index()->after('abilities');
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropIndex(['imis_sid_hash']);
            $table->dropColumn('imis_sid_hash');
        });
    }
};
