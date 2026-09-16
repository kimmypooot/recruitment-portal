// Shared labels/order for the CS Form No. 212 (PDS) completeness checklist
// returned by GET /profile/pds/completeness (PdsCompletenessChecker on the
// backend). Kept in one place so the Documents-tab checklist (GeneratePdsPanel)
// and the profile-wide readiness panel (ProfileReadinessPanel) never drift
// out of sync with each other or with the backend's section keys.

/** Printed order of the form, so any checklist built from this reads like the PDS itself. */
export const SECTION_LABELS = {
  personal_information:      'I. Personal Information',
  family_background:         'II. Family Background',
  educational_background:    'III. Educational Background',
  civil_service_eligibility: 'IV. Civil Service Eligibility',
  work_experience:           'V. Work Experience',
  voluntary_work:            'VI. Voluntary Work',
  learning_development:      'VII. Learning and Development',
  other_information:         'VIII. Other Information',
  declarations:              'Declarations (items 34–42)',
  references:                'References (item 41)',
}

/** Short label variant for compact UI (tab jump chips, summary rows) where
 * the roman-numeral/item-number form is too verbose. */
export const SECTION_SHORT_LABELS = {
  personal_information:      'Personal Information',
  family_background:         'Family Background',
  educational_background:    'Educational Background',
  civil_service_eligibility: 'Civil Service Eligibility',
  work_experience:           'Work Experience',
  voluntary_work:            'Voluntary Work',
  learning_development:      'Learning and Development',
  other_information:         'Other Information',
  declarations:              'Declarations',
  references:                'References',
}

/** Where a section's fields live in the Complete Profile page's tabs, so a
 * "Complete now" link knows where to send the applicant. `anchor`, when set,
 * is a DOM id inside the PDS Details tab to scroll to after switching tabs. */
export const SECTION_TARGETS = {
  personal_information:      { tab: 'personal' }, // refined per-field below
  family_background:         { tab: 'pds', anchor: 'section-family' },
  educational_background:    { tab: 'qualifications' },
  civil_service_eligibility: { tab: 'pds', anchor: 'section-eligibility' },
  work_experience:           { tab: 'qualifications' },
  voluntary_work:            { tab: 'pds', anchor: 'section-voluntary' },
  learning_development:      { tab: 'qualifications' },
  other_information:         { tab: 'pds', anchor: 'section-other-info' },
  declarations:               { tab: 'pds', anchor: 'section-declarations' },
  references:                 { tab: 'pds', anchor: 'section-references' },
}

/** personal_information fields that live on the PDS Details tab rather than
 * the Personal Info tab — used to decide which tab a "Complete now" link for
 * that section should jump to. */
const PDS_TAB_PERSONAL_FIELDS = new Set([
  'place_of_birth', 'citizenship', 'residential_address', 'permanent_address',
])

export function targetForSection(key, missing = []) {
  if (key === 'personal_information' && missing.some(f => PDS_TAB_PERSONAL_FIELDS.has(f))) {
    return { tab: 'pds', anchor: 'section-personal-extra' }
  }
  return SECTION_TARGETS[key] ?? { tab: 'personal' }
}

export const FIELD_LABELS = {
  place_of_birth:      'place of birth',
  citizenship:         'citizenship',
  residential_address: 'residential address',
  permanent_address:   'permanent address',
  sex_at_birth:        'sex at birth',
  civil_status:        'civil status',
  date_of_birth:       'date of birth',
  mobile_no:           'mobile number',
  father_name:         "father's name",
  mother_maiden_name:  "mother's maiden name",
  elementary:          'elementary',
  secondary:           'secondary',
}

/** Human-readable summary of what's missing in a section, shared by every
 * checklist UI that renders `check().sections`. */
export function describeMissing(key, section) {
  if (key === 'declarations') {
    const n = section.missing.length
    return `${n} question${n === 1 ? '' : 's'} still unanswered`
  }

  if (key === 'references') {
    const need = section.missing[0]?.match(/need_(\d+)_more/)?.[1]
    return need ? `${need} more reference${need === '1' ? '' : 's'} required` : 'Incomplete'
  }

  if (section.optional) return 'Nothing recorded yet'

  return 'Missing: ' + section.missing.map(f => FIELD_LABELS[f] ?? f.replace(/_/g, ' ')).join(', ')
}

export function orderedSectionEntries(sections = {}) {
  return Object.keys(SECTION_LABELS)
    .filter(key => sections[key])
    .map(key => ({ key, label: SECTION_LABELS[key], ...sections[key] }))
}
