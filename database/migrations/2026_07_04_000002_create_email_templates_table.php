<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category')->default('General');
            $table->string('subject');
            $table->string('greeting');
            $table->text('body');
            $table->string('action_text')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('action_locked')->default(false);
            $table->string('footer')->default('CSC RO VIII - Recruitment Portal');
            $table->json('placeholders')->nullable();
            $table->json('sample_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
