export const ROLE_LABELS = {
  applicant: 'Applicant',
  hrmpsb:    'HRMPSB',
  admin:     'Admin',
}

export function roleLabel(role) {
  return ROLE_LABELS[role] ?? role
}
