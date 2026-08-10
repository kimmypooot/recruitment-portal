<?php

declare(strict_types=1);

/**
 * Cell addresses for CS Form No. 212 (Revised 2026).
 *
 * Derived by inspecting resources/templates/CS_Form_212_Revised_2026.xlsx —
 * sheets C1..C4 are pages 1..4. Row/column positions come from the BLANK
 * template's merge ranges, not from any filled-in copy: filled copies in
 * circulation have shifted rows on pages 2 and 3.
 *
 * `direct`  — write the value into the cell.
 * `append`  — the form has no separate answer cell; the value is appended to
 *             the printed label (e.g. "If YES, give details: ____"), which is
 *             how the form is filled by hand.
 * `tables`  — repeating sections: a first row, a fixed row count matching the
 *             printed form, and one column letter per field.
 */
return [

    'direct' => [

        // --- Sheet C1 / page 1 : Section I ------------------------------
        'personal_information.surname'        => 'C1!D10',
        'personal_information.first_name'     => 'C1!D11',
        'personal_information.middle_name'    => 'C1!D12',
        'personal_information.date_of_birth'  => 'C1!D13',
        'personal_information.place_of_birth' => 'C1!D15',
        'personal_information.height_m'       => 'C1!D22',
        'personal_information.weight_kg'      => 'C1!D24',
        'personal_information.blood_type'     => 'C1!D25',

        'personal_information.umid_id_no'         => 'C1!D27',
        'personal_information.pagibig_id_no'      => 'C1!D29',
        'personal_information.philhealth_no'      => 'C1!D31',
        'personal_information.philsys_number'     => 'C1!D32',
        'personal_information.tin_no'             => 'C1!D33',
        'personal_information.agency_employee_no' => 'C1!D34',

        'personal_information.residential_address.house_block_lot'   => 'C1!I17',
        'personal_information.residential_address.street'            => 'C1!L17',
        'personal_information.residential_address.subdivision'       => 'C1!I19',
        'personal_information.residential_address.barangay'          => 'C1!L19',
        'personal_information.residential_address.city_municipality' => 'C1!I22',
        'personal_information.residential_address.province'          => 'C1!L22',
        'personal_information.residential_address.zip_code'          => 'C1!I24',

        'personal_information.permanent_address.house_block_lot'   => 'C1!I25',
        'personal_information.permanent_address.street'            => 'C1!L25',
        'personal_information.permanent_address.subdivision'       => 'C1!I27',
        'personal_information.permanent_address.barangay'          => 'C1!L27',
        'personal_information.permanent_address.city_municipality' => 'C1!I29',
        'personal_information.permanent_address.province'          => 'C1!L29',
        'personal_information.permanent_address.zip_code'          => 'C1!I31',

        'personal_information.telephone_no' => 'C1!I32',
        'personal_information.mobile_no'    => 'C1!I33',
        'personal_information.email'        => 'C1!I34',

        // --- Sheet C1 / page 1 : Section II -----------------------------
        'family_background.spouse.surname'          => 'C1!D36',
        'family_background.spouse.first_name'       => 'C1!D37',
        'family_background.spouse.middle_name'      => 'C1!D38',
        'family_background.spouse.occupation'       => 'C1!D39',
        'family_background.spouse.employer'         => 'C1!D40',
        'family_background.spouse.business_address' => 'C1!D41',
        'family_background.spouse.telephone'        => 'C1!D42',

        'family_background.father.surname'     => 'C1!D43',
        'family_background.father.first_name'  => 'C1!D44',
        'family_background.father.middle_name' => 'C1!D45',

        'family_background.mother_maiden.surname'     => 'C1!D47',
        'family_background.mother_maiden.first_name'  => 'C1!D48',
        'family_background.mother_maiden.middle_name' => 'C1!D49',

        // --- Sheet C4 / page 4 : item 42 --------------------------------
        'declarations.government_id_type'   => 'C4!D61',
        'declarations.government_id_number' => 'C4!D62',
    ],

    /**
     * Values appended to a printed label because the form provides a ruled line
     * rather than its own cell.
     */
    'append' => [
        'declarations.q34_details'     => 'C4!G10',
        'declarations.q35a_details'    => 'C4!G14',
        'declarations.q35b_details'    => 'C4!G19',
        'declarations.q35b_date_filed'  => 'C4!H20',
        'declarations.q35b_case_status' => 'C4!G21',
        'declarations.q36_details'     => 'C4!G24',
        'declarations.q37_details'     => 'C4!G28',
        'declarations.q38a_details'    => 'C4!G32',
        'declarations.q38b_details'    => 'C4!G35',
        'declarations.q39_country'     => 'C4!G38',
        'declarations.q40a_group_name' => 'C4!G44',
        'declarations.q40b_id_no'      => 'C4!G46',
        'declarations.q40c_id_no'      => 'C4!G48',

        // Spouse/father name extensions share their printed label; the form has
        // no dedicated cell for them.
        'family_background.spouse.name_extension' => 'C1!G37',
        'family_background.father.name_extension' => 'C1!G44',

        // "Date/Place of Issuance:" is a single line on the form.
        'declarations.government_id_issuance' => 'C4!D64',
    ],

    /**
     * Repeating sections. `rows` matches the number of rows printed on the
     * blank form; PdsDataAssembler pads and truncates to the same counts.
     */
    'tables' => [

        'family_background.children' => [
            'sheet'     => 'C1',
            'first_row' => 37,
            'rows'      => 12,
            'columns'   => ['full_name' => 'I', 'date_of_birth' => 'M'],
        ],

        'civil_service_eligibility' => [
            'sheet'     => 'C2',
            'first_row' => 5,
            'rows'      => 7,
            'columns'   => [
                'eligibility_name'    => 'A',
                'rating'              => 'F',
                'examination_date'    => 'G',
                'examination_place'   => 'I',
                'license_number'      => 'L',
                'license_valid_until' => 'M',
            ],
        ],

        'work_experience' => [
            'sheet'     => 'C2',
            'first_row' => 18,
            'rows'      => 28,
            'columns'   => [
                'date_from'          => 'A',
                'date_to'            => 'C',
                'position_title'     => 'D',
                'department_agency'  => 'G',
                'monthly_salary'     => 'J',
                'salary_grade'       => 'K',
                'appointment_status' => 'L',
                'government_service' => 'M',
            ],
        ],

        'voluntary_work' => [
            'sheet'     => 'C3',
            'first_row' => 6,
            'rows'      => 7,
            'columns'   => [
                'organization_name_address' => 'A',
                'date_from'                 => 'E',
                'date_to'                   => 'F',
                'number_of_hours'           => 'G',
                'position_nature_of_work'   => 'H',
            ],
        ],

        'learning_development' => [
            'sheet'     => 'C3',
            'first_row' => 18,
            'rows'      => 21,
            'columns'   => [
                'title'           => 'A',
                'date_from'       => 'E',
                'date_to'         => 'F',
                'number_of_hours' => 'G',
                'ld_type'         => 'H',
                'conducted_by'    => 'I',
            ],
        ],

        'declarations.references' => [
            'sheet'     => 'C4',
            'first_row' => 52,
            'rows'      => 3,
            'columns'   => [
                'name'                       => 'A',
                'office_residential_address' => 'F',
                'contact_no_or_email'        => 'G',
            ],
        ],
    ],

    /**
     * Section VIII prints three independent single-column lists side by side,
     * so they are handled as scalar lists rather than row-shaped tables.
     */
    'lists' => [
        'other_information.special_skills_hobbies'    => ['sheet' => 'C3', 'column' => 'A', 'first_row' => 42, 'rows' => 7],
        'other_information.non_academic_distinctions' => ['sheet' => 'C3', 'column' => 'C', 'first_row' => 42, 'rows' => 7],
        'other_information.memberships'              => ['sheet' => 'C3', 'column' => 'I', 'first_row' => 42, 'rows' => 7],
    ],

    'education_rows' => [
        'sheet'   => 'C1',
        'levels'  => [
            'elementary'       => 54,
            'secondary'        => 55,
            'vocational_trade' => 56,
            'college'          => 57,
            'graduate_studies' => 58,
        ],
        'columns' => [
            'school_name'    => 'D',
            'degree_course'  => 'G',
            'period_from'    => 'J',
            'period_to'      => 'K',
            'units_earned'   => 'L',
            'year_graduated' => 'M',
            'honors'         => 'N',
        ],
    ],
];
