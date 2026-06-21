<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hrmpsb_compositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('hrmpsb_role'); // chairperson, secretariat, director-representative, etc.
            $table->enum('member_type', ['principal', 'alternate'])->default('principal');
            $table->boolean('is_active')->default(true);
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['vacancy_id', 'user_id', 'hrmpsb_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hrmpsb_compositions');
    }
};
