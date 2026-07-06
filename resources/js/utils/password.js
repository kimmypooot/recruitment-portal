// Single source of truth for the password policy shown across Register,
// ResetPassword, and the Change Password modal — matches the server-side
// rule Password::min(8)->letters()->mixedCase()->numbers().
export function passwordRequirements(password) {
  const p = password ?? ''
  return [
    { label: 'At least 8 characters',         met: p.length >= 8 },
    { label: 'At least one uppercase letter', met: /[A-Z]/.test(p) },
    { label: 'At least one lowercase letter', met: /[a-z]/.test(p) },
    { label: 'At least one number',           met: /[0-9]/.test(p) },
  ]
}

export function unmetPasswordRequirements(password) {
  return passwordRequirements(password).filter(r => !r.met).map(r => r.label.replace(/^At least /, ''))
}
