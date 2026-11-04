<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Positions the Regional Office does not post
    |--------------------------------------------------------------------------
    |
    | Director II, III and IV are filled by the CSC Central Office, so a vacancy
    | cannot be opened on them here: they are left out of the IMIS picker and
    | refused when entered by hand. A title is excluded when it starts with one
    | of these (whole words, any case), so "Director II" does not catch
    | "Director III" or "Director I". Chief Human Resource Specialist and the
    | other positions are unaffected.
    |
    */
    'central_office_positions' => array_values(array_filter(array_map('trim', explode(
        '|',
        (string) env('VACANCY_CENTRAL_OFFICE_POSITIONS', 'Director II|Director III|Director IV')
    )))),

];
