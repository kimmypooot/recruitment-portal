<?php

namespace App\Traits;

trait FormatsApplicantName
{
    private function formatApplicantName($profile): string
    {
        if (!$profile) return '';
        $mi = $profile->middle_name
            ? ' ' . strtoupper(substr($profile->middle_name, 0, 1)) . '.'
            : '';
        return "{$profile->last_name}, {$profile->first_name}{$mi}";
    }
}
