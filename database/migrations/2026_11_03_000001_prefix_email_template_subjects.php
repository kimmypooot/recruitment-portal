<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')
            ->where('subject', 'not like', '[CSC RO VIII]%')
            ->update(['subject' => DB::raw("CONCAT('[CSC RO VIII] ', subject)")]);
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->where('subject', 'like', '[CSC RO VIII] %')
            ->update(['subject' => DB::raw("SUBSTRING(subject, 14)")]);
    }
};
