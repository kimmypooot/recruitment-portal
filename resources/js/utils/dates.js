export function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', {
    month: 'short', day: 'numeric', year: 'numeric',
  })
}

export function formatDateTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-PH', {
    month: 'short', day: 'numeric',
    hour: 'numeric', minute: '2-digit', hour12: true,
  })
}

export function formatDateRange(start, end) {
  if (!start || !end) return '—'
  const s = new Date(start)
  const e = new Date(end)
  const opts = { month: 'short', day: 'numeric', year: 'numeric' }
  return `${s.toLocaleDateString('en-PH', opts)} — ${e.toLocaleDateString('en-PH', opts)}`
}

export function daysRemaining(dateStr) {
  if (!dateStr) return null
  const diff = new Date(dateStr) - new Date()
  return Math.ceil(diff / (1000 * 60 * 60 * 24))
}

export function isPastDeadline(dateStr) {
  if (!dateStr) return false
  return new Date(dateStr) < new Date()
}
