<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `work_experiences` and `trainings` store inclusive dates as strings. The PDS
 * prints them as dd/mm/yyyy and the generator has to format them, so make them
 * real dates.
 *
 * Done as add / backfill / drop / rename rather than a direct ->change(), so a
 * value that will not parse lands as NULL instead of failing the migration or
 * being silently zeroed by MySQL.
 *
 * The backfill runs in PHP rather than SQL: the app runs on MySQL but the test
 * suite runs on in-memory SQLite, and STR_TO_DATE/DATE_FORMAT are MySQL-only.
 */
return new class extends Migration
{
    private const TARGETS = [
        'work_experiences' => ['date_from', 'date_to'],
        'trainings'        => ['date_from', 'date_to'],
    ];

    public function up(): void
    {
        $this->convert(fn (Blueprint $table, string $column) => $table->date($column)->nullable(),
            fn ($value) => $this->toDate($value));
    }

    public function down(): void
    {
        $this->convert(fn (Blueprint $table, string $column) => $table->string($column)->nullable(),
            fn ($value) => $value === null ? null : Carbon::parse($value)->format('Y-m-d'));
    }

    /**
     * Add a temp column of the target type, move converted values into it,
     * then drop the original and rename the temp into its place.
     */
    private function convert(callable $defineColumn, callable $transform): void
    {
        foreach (self::TARGETS as $tableName => $columns) {
            foreach ($columns as $column) {
                $temp = "{$column}__tmp";

                Schema::table($tableName, function (Blueprint $table) use ($defineColumn, $temp) {
                    $defineColumn($table, $temp);
                });

                DB::table($tableName)
                    ->select('id', $column)
                    ->orderBy('id')
                    ->chunk(500, function ($rows) use ($tableName, $column, $temp, $transform) {
                        foreach ($rows as $row) {
                            $converted = $transform($row->{$column});

                            if ($converted !== null) {
                                DB::table($tableName)
                                    ->where('id', $row->id)
                                    ->update([$temp => $converted]);
                            }
                        }
                    });

                Schema::table($tableName, function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });

                Schema::table($tableName, function (Blueprint $table) use ($temp, $column) {
                    $table->renameColumn($temp, $column);
                });
            }
        }
    }

    /** Anything unparseable becomes NULL rather than blowing up the migration. */
    private function toDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y'] as $format) {
            $parsed = Carbon::createFromFormat($format, trim($value));

            if ($parsed !== false) {
                return $parsed->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
};
