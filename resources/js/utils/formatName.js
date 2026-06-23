export function formatName(applicant) {
  if (!applicant) return ''
  const mi = applicant.middle_name
    ? ' ' + applicant.middle_name.charAt(0).toUpperCase() + '.'
    : ''
  return `${applicant.last_name}, ${applicant.first_name}${mi}`
}
