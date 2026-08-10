<?php

declare(strict_types=1);

/**
 * Settings for CS Form No. 212 (Revised 2026) generation.
 */
return [

    /**
     * LibreOffice is used to convert the filled workbook to PDF.
     *
     * PhpSpreadsheet's own PDF writers were evaluated and rejected: they render
     * the sheet as HTML, which drops all 35 form-control checkboxes and paginates
     * the form to 7 pages instead of 4. A PDS with no tick boxes is not a PDS.
     *
     * Set PDS_SOFFICE_PATH when the binary is not on PATH or in a default
     * install location.
     */
    'soffice_path' => env('PDS_SOFFICE_PATH'),

    /** Candidate locations probed when `soffice_path` is not set. */
    'soffice_candidates' => [
        'soffice',
        'libreoffice',
        'C:\Program Files\LibreOffice\program\soffice.exe',
        'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
        '/usr/bin/soffice',
        '/usr/bin/libreoffice',
        '/opt/libreoffice/program/soffice',
        '/Applications/LibreOffice.app/Contents/MacOS/soffice',
    ],

    /** Seconds to allow for a single conversion. */
    'conversion_timeout' => (int) env('PDS_CONVERSION_TIMEOUT', 120),

    /** The form is exactly four pages; used as an output sanity check. */
    'expected_pages' => 4,

    /**
     * HRMPSB board members and the admin module can read any candidate's PDS.
     *
     * While true, that access follows the system's existing anonymisation
     * contract: an application still masked for blind deliberation is withheld
     * from reviewers who do not hold the `unmask-identities` gate, because the
     * PDS carries the candidate's name, address and family details — exactly
     * what AnonymizationToken exists to hide at that stage. It has no effect at
     * any other stage, where no token is active.
     *
     * Set to false to give the board unconditional access at every stage.
     */
    'board_access_respects_anonymization' => (bool) env('PDS_BOARD_RESPECTS_ANONYMIZATION', true),
];
