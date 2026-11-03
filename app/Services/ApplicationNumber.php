<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The number an application carries: POSITION-ITEM-MM-YYYY-NNNN, e.g.
 * AOII-53-11-2026-0001.
 *
 *  - POSITION: initials of the position title with its level kept whole
 *    ("Administrative Officer II" => AOII).
 *  - ITEM: the vacancy's plantilla item (the serial of "A3-53-2005" is 53), so
 *    two vacancies for the same position in the same month still differ. A
 *    vacancy with no plantilla item uses V{vacancy id}.
 *  - MM-YYYY: the month the vacancy was published, so every applicant to one
 *    vacancy shares it.
 *  - NNNN: the applicant number, counted per prefix and never reused (a
 *    withdrawn or deleted application keeps its number). Because the count is
 *    per full prefix, the number is unique even if two prefixes ever coincide.
 */
class ApplicationNumber
{
    /** Words left out of the position initials. */
    private const SKIPPED_WORDS = ['of', 'and', 'the', 'for', 'in', 'to', 'a', 'an'];

    /**
     * Issues the next number for this vacancy. Call inside the database
     * transaction that creates the application, so the number is only used if
     * the application is saved.
     */
    public function next(Vacancy $vacancy): string
    {
        $prefix = $this->prefix($vacancy);

        DB::table('application_number_counters')->insertOrIgnore([
            'prefix' => $prefix, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $counter = DB::table('application_number_counters')->where('prefix', $prefix)->lockForUpdate()->first();
        $number = $counter->last_number + 1;

        DB::table('application_number_counters')->where('id', $counter->id)
            ->update(['last_number' => $number, 'updated_at' => now()]);

        return sprintf('%s-%04d', $prefix, $number);
    }

    public function prefix(Vacancy $vacancy): string
    {
        $month = ($vacancy->published_at ?? $vacancy->created_at ?? now());

        return sprintf('%s-%s-%s-%s', $this->positionCode($vacancy->position_title), $this->item($vacancy), $month->format('m'), $month->format('Y'));
    }

    public function positionCode(string $title): string
    {
        $words = preg_split('/[^A-Za-z0-9]+/', trim($title), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $level = '';

        // A trailing level ("II", "IV", "3") stays whole instead of being cut to its first letter.
        if ($words !== [] && preg_match('/^(?:[IVX]+|\d+)$/i', end($words))) {
            $level = strtoupper(array_pop($words));
        }

        $initials = collect($words)
            ->reject(fn ($word) => in_array(strtolower($word), self::SKIPPED_WORDS, true))
            ->map(fn ($word) => strtoupper($word[0]))
            ->implode('');

        return ($initials.$level) ?: 'POS';
    }

    private function item(Vacancy $vacancy): string
    {
        $plantilla = trim((string) $vacancy->plantilla_no);

        if ($plantilla === '') {
            return 'V'.$vacancy->id;
        }

        // "A3-53-2005" => the item serial 53.
        if (preg_match('/^[A-Za-z0-9]+-(\d+)-\d{4}$/', $plantilla, $match)) {
            return $match[1];
        }

        return Str::of($plantilla)->upper()->replaceMatches('/[^A-Z0-9]+/', '')->limit(12, '')->value() ?: 'V'.$vacancy->id;
    }
}
