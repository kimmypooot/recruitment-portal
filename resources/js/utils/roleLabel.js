export const ROLE_LABELS = {
  applicant:              'Applicant',
  'hr-officer':           'HR Officer',
  'hr-manager':           'HR Manager',
  admin:                  'Admin',
  'hrmpsb-member':        'HRMPSB Member',
  'hrmpsb-secretariat':   'HRMPSB Secretariat',
  'appointing-authority': 'Appointing Authority',
}

export function roleLabel(role) {
  return ROLE_LABELS[role] ?? role
}
