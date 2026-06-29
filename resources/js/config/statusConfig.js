export const STATUS_ORDER = [
  'submitted', 'under_review', 'screened', 'qualified', 'disqualified',
  'exam_scheduled', 'shortlisted', 'for_interview', 'interviewed',
  'recommended', 'appointed', 'completed', 'withdrawn', 'failed', 'passed',
]

export const PIPELINE = [
  { key: 'submitted',      label: 'Submitted',     short: 'Submit' },
  { key: 'under_review',   label: 'Review',        short: 'Review' },
  { key: 'screened',       label: 'Screened',      short: 'Screen' },
  { key: 'qualified',      label: 'Qualified',     short: 'Qualify' },
  { key: 'exam_scheduled', label: 'Exam',          short: 'Exam' },
  { key: 'shortlisted',    label: 'Shortlisted',   short: 'List' },
  { key: 'for_interview',  label: 'Interview',     short: 'Intv.' },
  { key: 'interviewed',    label: 'Interviewed',   short: 'Done' },
  { key: 'recommended',    label: 'Recommended',   short: 'Rec.' },
  { key: 'appointed',      label: 'Appointed',     short: 'Appoint' },
  { key: 'failed',         label: 'Not Passed',    short: 'Fail' },
]

export const PIPELINE_ORDER = PIPELINE.map(s => s.key)
export const TERMINAL_STATUSES = new Set(['appointed', 'completed', 'disqualified', 'withdrawn', 'failed'])

export const STATUS_LABELS = {
  submitted:      'Submitted',
  under_review:   'Under Review',
  screened:       'Screened',
  qualified:      'Qualified',
  disqualified:   'Disqualified',
  exam_scheduled: 'Exam Scheduled',
  shortlisted:    'Shortlisted',
  for_interview:  'For Interview',
  interviewed:    'Interviewed',
  recommended:    'Recommended',
  appointed:      'Appointed',
  completed:      'Completed',
  withdrawn:      'Withdrawn',
  failed:         'Not Passed',
  passed:         'Passed',
  draft:          'Draft',
  published:      'Published',
  closed:         'Closed',
  filled:         'Filled',
  archived:       'Archived',
}

export const STATUS_BADGE_CLASSES = {
  draft:          'bg-gray-100 text-gray-600',
  published:      'bg-green-100 text-green-700',
  closed:         'bg-gray-100 text-gray-500',
  filled:         'bg-teal-100 text-teal-700',
  archived:       'bg-yellow-100 text-yellow-700',
  submitted:      'bg-[#2a338f]/10 text-[#2a338f]',
  under_review:   'bg-purple-100 text-purple-700',
  screened:       'bg-sky-100 text-sky-700',
  qualified:      'bg-teal-100 text-teal-700',
  disqualified:   'bg-red-100 text-red-700',
  exam_scheduled: 'bg-orange-100 text-orange-700',
  shortlisted:    'bg-indigo-100 text-indigo-700',
  for_interview:  'bg-violet-100 text-violet-700',
  interviewed:    'bg-cyan-100 text-cyan-700',
  recommended:    'bg-lime-100 text-lime-700',
  appointed:      'bg-green-100 text-green-800',
  completed:      'bg-green-100 text-green-700',
  withdrawn:      'bg-gray-200 text-gray-500',
  failed:         'bg-red-100 text-red-700',
  passed:         'bg-green-100 text-green-700',
}

export const STATUS_ICONS = {
  submitted:      { bg: 'bg-yellow-50',       color: 'text-yellow-500',   path: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
  under_review:   { bg: 'bg-purple-50',       color: 'text-purple-500',   path: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' },
  screened:       { bg: 'bg-sky-50',          color: 'text-sky-500',      path: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
  qualified:      { bg: 'bg-teal-50',         color: 'text-teal-500',     path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
  disqualified:   { bg: 'bg-red-50',          color: 'text-red-400',      path: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
  exam_scheduled: { bg: 'bg-orange-50',       color: 'text-orange-500',   path: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
  shortlisted:    { bg: 'bg-indigo-50',       color: 'text-indigo-500',   path: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
  for_interview:  { bg: 'bg-violet-50',       color: 'text-violet-500',   path: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
  interviewed:    { bg: 'bg-green-50',        color: 'text-green-500',    path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
  recommended:    { bg: 'bg-lime-50',         color: 'text-lime-600',     path: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z' },
  appointed:      { bg: 'bg-[#2a338f]/10',    color: 'text-[#2a338f]',    path: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z' },
  completed:      { bg: 'bg-green-50',        color: 'text-green-500',    path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
  withdrawn:      { bg: 'bg-gray-50',         color: 'text-gray-400',     path: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' },
  failed:         { bg: 'bg-red-50',          color: 'text-red-400',      path: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
}

export const STATUS_BORDER_CLASSES = {
  submitted:      'border-l-amber-400',
  under_review:   'border-l-purple-400',
  screened:       'border-l-sky-400',
  qualified:      'border-l-teal-400',
  exam_scheduled: 'border-l-orange-400',
  shortlisted:    'border-l-indigo-400',
  for_interview:  'border-l-violet-400',
  interviewed:    'border-l-green-400',
  recommended:    'border-l-lime-500',
  appointed:      'border-l-[#2a338f]',
  completed:      'border-l-green-500',
  withdrawn:      'border-l-gray-300',
  failed:         'border-l-red-400',
  disqualified:   'border-l-red-500',
}

export const STATUS_CHIP_CLASSES = {
  submitted:      'bg-yellow-50 text-yellow-700 border-yellow-200',
  under_review:   'bg-purple-50 text-purple-700 border-purple-200',
  screened:       'bg-sky-50 text-sky-700 border-sky-200',
  qualified:      'bg-teal-50 text-teal-700 border-teal-200',
  disqualified:   'bg-red-50 text-red-600 border-red-200',
  exam_scheduled: 'bg-orange-50 text-orange-700 border-orange-200',
  shortlisted:    'bg-indigo-50 text-indigo-700 border-indigo-200',
  for_interview:  'bg-violet-50 text-violet-700 border-violet-200',
  interviewed:    'bg-cyan-50 text-cyan-700 border-cyan-200',
  recommended:    'bg-lime-50 text-lime-700 border-lime-200',
  appointed:      'bg-[#2a338f]/10 text-[#2a338f] border-[#2a338f]/20',
  completed:      'bg-green-50 text-green-700 border-green-200',
  withdrawn:      'bg-gray-50 text-gray-500 border-gray-200',
  failed:         'bg-red-50 text-red-600 border-red-200',
}

export function statusLabel(status) {
  return STATUS_LABELS[status] ?? status?.replace(/_/g, ' ')
}

export function statusBadgeClass(status) {
  return STATUS_BADGE_CLASSES[status] ?? 'bg-gray-100 text-gray-600'
}

export function statusIcon(status) {
  return STATUS_ICONS[status] ?? { bg: 'bg-gray-50', color: 'text-gray-400', path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
}

export function statusBorderClass(status) {
  return STATUS_BORDER_CLASSES[status] ?? 'border-l-gray-200'
}

export function statusChipClass(status) {
  return STATUS_CHIP_CLASSES[status] ?? 'bg-gray-50 text-gray-500 border-gray-200'
}

export function pipelineStep(status) {
  const idx = PIPELINE_ORDER.indexOf(status)
  return idx >= 0 ? idx + 1 : '?'
}

export function isPipelinePast(key, currentStatus) {
  return PIPELINE_ORDER.indexOf(key) < PIPELINE_ORDER.indexOf(currentStatus)
}

export function isTerminal(status) {
  return TERMINAL_STATUSES.has(status)
}

export function canWithdraw(status) {
  return ['submitted', 'under_review', 'screened'].includes(status)
}
