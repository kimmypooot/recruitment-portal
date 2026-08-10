<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Section VI item 29 — voluntary work or involvement in civic / non-government /
 * people / voluntary organisation/s.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voluntary_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_profile_id')->constrained()->onDelete('cascade');
            // The form prints organisation name and address in one column.
            $table->string('organization_name_address', 500);
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->decimal('number_of_hours', 8, 2)->nullable();
            $table->string('position_nature_of_work')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['applicant_profile_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntary_works');
    }
};
