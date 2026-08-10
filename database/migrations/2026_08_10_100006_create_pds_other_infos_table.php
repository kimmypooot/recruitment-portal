<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Section VIII items 31-33 — three parallel repeating lists printed side by side:
 *   31 Special Skills and Hobbies
 *   32 Non-Academic Distinctions / Recognition
 *   33 Membership in Association / Organization
 *
 * One table with a `kind` discriminator rather than three near-identical tables;
 * they share an identical shape and are always rendered together.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pds_other_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_profile_id')->constrained()->onDelete('cascade');
            // special_skill_hobby | non_academic_distinction | membership
            $table->string('kind', 40);
            $table->string('value', 500);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['applicant_profile_id', 'kind', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pds_other_infos');
    }
};
