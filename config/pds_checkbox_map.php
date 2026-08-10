<?php

declare(strict_types=1);

/**
 * Form-control checkboxes on CS Form No. 212 (Revised 2026).
 *
 * The template carries 35 Excel form controls. None has an fmlaLink, so their
 * state lives only in the VML shape — there is no cell to write. They are
 * ticked by XlsxCheckboxWriter, which injects <x:Checked>1</x:Checked> into the
 * matching shape after PhpSpreadsheet has saved the workbook.
 *
 * Shapes are identified by their <x:Anchor> string, which was verified to
 * survive a PhpSpreadsheet load/save round-trip byte-identical. Captions are
 * recorded for readability and are asserted against the template in tests.
 *
 * Sheet keys are the worksheet names; the writer resolves each sheet to its
 * legacyDrawing relationship rather than assuming a vmlDrawingN.vml filename,
 * because PhpSpreadsheet renumbers drawing parts on save.
 */
return [

    // --- Sheet C1 : Section I ------------------------------------------

    // Item 5, sex at birth
    'personal_information.sex_at_birth' => [
        'sheet'   => 'C1',
        'options' => [
            'Male'   => ['anchor' => '3,9,15,0,3,120,15,40', 'caption' => 'Male'],
            'Female' => ['anchor' => '4,3,15,0,5,49,15,39', 'caption' => 'Female'],
        ],
    ],

    // Item 6, civil status
    'personal_information.civil_status' => [
        'sheet'   => 'C1',
        'options' => [
            'Single'    => ['anchor' => '3,9,15,35,3,120,17,8', 'caption' => 'Single'],
            'Married'   => ['anchor' => '4,3,15,38,5,49,17,8', 'caption' => 'Married'],
            'Widowed'   => ['anchor' => '3,9,16,18,3,120,19,8', 'caption' => 'Widowed'],
            'Separated' => ['anchor' => '4,3,16,19,5,49,19,8', 'caption' => 'Separated'],
            'Other'     => ['anchor' => '3,9,18,3,3,120,21,0', 'caption' => 'Other/s:'],
        ],
    ],

    // Item 16, citizenship
    'personal_information.citizenship' => [
        'sheet'   => 'C1',
        'options' => [
            'filipino' => ['anchor' => '9,14,12,8,10,86,13,8', 'caption' => 'Filipino'],
            'dual'     => ['anchor' => '10,49,12,5,12,36,13,9', 'caption' => 'Dual Citizenship'],
        ],
    ],

    'personal_information.citizenship_basis' => [
        'sheet'   => 'C1',
        'options' => [
            'by_birth'          => ['anchor' => '11,9,12,33,11,94,14,13', 'caption' => 'by birth'],
            'by_naturalization' => ['anchor' => '11,95,12,33,13,78,14,15', 'caption' => 'by naturalization'],
        ],
    ],

    // --- Sheet C4 : page 4, questions 34-40 -----------------------------
    // Every question is a YES/NO pair. An unanswered question leaves BOTH
    // boxes blank - it must never default to NO.

    'declarations.q34a' => ['sheet' => 'C4', 'yes' => '6,18,4,8,7,50,7,0',     'no' => '8,73,4,9,10,25,7,0'],
    'declarations.q34b' => ['sheet' => 'C4', 'yes' => '6,18,6,8,7,50,9,3',     'no' => '8,73,7,0,10,25,9,3'],
    'declarations.q35a' => ['sheet' => 'C4', 'yes' => '6,15,12,4,7,48,13,3',   'no' => '9,3,12,4,10,29,13,3'],
    'declarations.q35b' => ['sheet' => 'C4', 'yes' => '6,15,16,8,7,48,18,4',   'no' => '9,8,16,8,10,34,18,4'],
    'declarations.q36'  => ['sheet' => 'C4', 'yes' => '6,14,22,8,7,48,23,5',   'no' => '9,13,22,8,10,39,23,5'],
    'declarations.q37'  => ['sheet' => 'C4', 'yes' => '6,13,26,3,7,45,27,9',   'no' => '9,13,26,3,10,39,27,9'],
    'declarations.q38a' => ['sheet' => 'C4', 'yes' => '6,15,30,5,7,48,31,4',   'no' => '10,0,30,5,11,3,31,4'],
    'declarations.q38b' => ['sheet' => 'C4', 'yes' => '6,18,32,9,7,49,34,3',   'no' => '10,3,32,10,11,3,34,4'],
    'declarations.q39'  => ['sheet' => 'C4', 'yes' => '6,15,36,8,7,48,37,5',   'no' => '10,0,36,8,11,3,37,5'],
    'declarations.q40a' => ['sheet' => 'C4', 'yes' => '6,15,42,4,7,48,43,10',  'no' => '10,3,42,4,11,4,43,10'],
    'declarations.q40b' => ['sheet' => 'C4', 'yes' => '6,15,44,0,7,48,45,8',   'no' => '10,3,44,0,11,4,45,8'],
    'declarations.q40c' => ['sheet' => 'C4', 'yes' => '6,15,46,3,7,48,47,8',   'no' => '10,3,46,3,11,4,47,8'],
];
