// Canonical CSC RO VIII place-of-assignment options.
// Keep in sync with App\Models\Vacancy::PLACES_OF_ASSIGNMENT on the backend.

export const CSC_FIELD_OFFICES = [
  'CSC Field Office - Leyte I',
  'CSC Field Office - Leyte II',
  'CSC Field Office - Southern Leyte',
  'CSC Field Office - Biliran',
  'CSC Satellite Office - Western Leyte',
  'CSC Field Office - Samar',
  'CSC Field Office - Eastern Samar',
  'CSC Field Office - Northern Samar',
]

export const REGIONAL_SUPPORT_UNITS = [
  'Office of the Regional Director (ORD)',
  'Human Resource Division (HRD)',
  'Management Resource Division (MSD)',
  'Public Assistance and Liaison Division (PALD)',
  'Policies and Systems Evaluation Division (PSED)',
  'Examination Services Division (ESD)',
  'Legal Services Division (LSD)',
]

export const PLACES_OF_ASSIGNMENT = [...CSC_FIELD_OFFICES, ...REGIONAL_SUPPORT_UNITS]
