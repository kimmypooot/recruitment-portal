<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The FKs below record *who performed an administrative/evaluation action*
 * (posted a vacancy, evaluated/rated a candidate, decided a deliberation,
 * generated/signed a CS form, checked a background, assigned an HRMPSB
 * role) rather than "this row belongs to that parent". They were created
 * with onDelete('cascade')/cascadeOnDelete() against users.id, which means
 * hard-deleting a user (User has no SoftDeletes — UserController::destroy()
 * calls $user->delete() directly) silently destroys every official record
 * that user ever produced, including signed CS Forms and deliberation
 * decisions. Switched to restrictOnDelete() so such a delete fails loudly
 * instead of quietly erasing pipeline/audit history; staff accounts should
 * be deactivated (deactivated_at), not hard-deleted, once they've acted on
 * any application.
 */
return new class extends Migration
{
    private array $targets = [
        'vacancies' => ['posted_by'],
        'hrmpsb_compositions' => ['assigned_by'],
        'qs_evaluations' => ['evaluator_id'],
        'exam_results' => ['encoded_by'],
        'bei_ratings' => ['evaluator_id'],
        'cbwe_ratings' => ['evaluator_id'],
        'deliberation_results' => ['decided_by'],
        'appointing_authority_decisions' => ['decided_by'],
        'cs_forms' => ['generated_by'],
        'comparative_assessment_results' => ['generated_by'],
        'background_checks' => ['checked_by'],
    ];

    public function up(): void
    {
        foreach ($this->targets as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column) {
                    $blueprint->dropForeign([$column]);
                    $blueprint->foreign($column)->references('id')->on('users')->restrictOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->targets as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column) {
                    $blueprint->dropForeign([$column]);
                    $blueprint->foreign($column)->references('id')->on('users')->cascadeOnDelete();
                }
            });
        }
    }
};
