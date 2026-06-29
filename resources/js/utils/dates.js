export function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', {
    month: 'short', day: 'numeric', year: 'numeric',
  })
}

export function formatDateLong(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric',
  })
}

export function formatDateTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: 'numeric', minute: '2-digit', hour12: true,
  })
}

export function formatDateRange(start, end, isPresent = false) {
  if (!start) return '—'
  const MONTHS = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'Jun.',
                  'Jul.', 'Aug.', 'Sep.', 'Oct.', 'Nov.', 'Dec.']
  const parse = (str) => {
    const [y, m, d] = str.split('-').map(Number)
    return { y, m: m - 1, d }
  }
  const f = parse(start)
  const fromStr = `${MONTHS[f.m]} ${f.d}`
  if (isPresent) return `${fromStr}, ${f.y} – Present`
  if (!end) return `${fromStr}, ${f.y}`
  const t = parse(end)
  if (f.m === t.m && f.y === t.y) {
    if (f.d === t.d) return `${fromStr}, ${f.y}`
    return `${MONTHS[f.m]} ${f.d}-${t.d}, ${f.y}`
  }
  if (f.y === t.y) {
    return `${fromStr} - ${MONTHS[t.m]} ${t.d}, ${f.y}`
  }
  return `${fromStr}, ${f.y} - ${MONTHS[t.m]} ${t.d}, ${t.y}`
}

export function daysRemaining(dateStr) {
  if (!dateStr) return null
  const ms = new Date(dateStr) - new Date()
  return ms < 0 ? -1 : Math.ceil(ms / (1000 * 60 * 60 * 24))
}

export function isPastDeadline(dateStr) {
  if (!dateStr) return false
  return new Date(dateStr) < new Date()
}

export function timeAgo(date) {
  if (!date) return ''
  const diff = (Date.now() - new Date(date).getTime()) / 1000
  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return `${Math.floor(diff / 86400)}d ago`
}
