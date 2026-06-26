<template>
  <AdminLayout title="Dashboard">

    <!-- Stat cards -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
      <Link v-for="card in statCards" :key="card.label" :href="card.link"
        class="bg-white rounded-xl border border-gray-200 p-5 flex items-start gap-4 shadow-sm hover:border-[#2a338f]/30 hover:shadow-md transition-all cursor-pointer">
        <div :class="card.iconBg" class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5" :class="card.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon"/>
          </svg>
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium">{{ card.label }}</p>
          <p v-if="!loading" class="text-2xl font-bold text-gray-900 mt-0.5">{{ card.value }}</p>
          <div v-else class="h-7 w-14 bg-gray-200 rounded animate-pulse mt-0.5"></div>
          <p v-if="card.sub" class="text-xs text-gray-400 mt-0.5">{{ card.sub }}</p>
        </div>
      </Link>
    </div>

    <!-- Pipeline summary chips -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
      <h2 class="text-sm font-semibold text-gray-900 mb-3">Application Pipeline</h2>
      <div v-if="loading" class="flex flex-wrap gap-2">
        <div v-for="n in 8" :key="n" class="h-7 w-24 bg-gray-100 rounded-full animate-pulse"></div>
      </div>
      <div v-else-if="sortedPipeline.length" class="flex flex-wrap gap-2">
        <div v-for="item in sortedPipeline" :key="item.status"
          :class="statusChipClass(item.status)"
          class="flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-medium">
          <span>{{ statusLabel(item.status) }}</span>
          <span class="font-bold">{{ item.count }}</span>
        </div>
      </div>
      <p v-else class="text-sm text-gray-400">No application data yet.</p>
    </div>

    <!-- Pipeline color legend -->
    <div class="mt-3 flex flex-wrap gap-2 text-[10px] text-gray-400">
      <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-yellow-500"></span> Submitted</span>
      <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500"></span> Under Review</span>
      <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-500"></span> Exam Scheduled</span>
      <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> Completed</span>
      <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Disqualified</span>
      <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-500"></span> Withdrawn</span>
    </div>

    <!-- Bottom row: Recent Applications + Upcoming Schedules -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Recent Applications (2/3) -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-sm font-semibold text-gray-900">Recent Applications</h2>
          <div class="flex items-center gap-2">
            <button @click="autoRefresh = !autoRefresh"
              class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-medium transition-colors"
              :class="autoRefresh ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
              <span class="w-1.5 h-1.5 rounded-full" :class="autoRefresh ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"></span>
              {{ autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh OFF' }}
            </button>
            <Link href="/admin/applications" class="text-xs text-[#2a338f] hover:underline font-medium">View all</Link>
          </div>
        </div>
        <div v-if="loading" class="space-y-3">
          <div v-for="n in 5" :key="n" class="h-10 bg-gray-100 rounded animate-pulse"></div>
        </div>
        <div v-else-if="recentApplications.length" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                <th class="pb-2 font-medium">Applicant</th>
                <th class="pb-2 font-medium">Position</th>
                <th class="pb-2 font-medium">Status</th>
                <th class="pb-2 font-medium">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="app in recentApplications" :key="app.id" class="hover:bg-gray-50 transition-colors">
                <td class="py-2.5 pr-4 text-gray-900 font-medium">{{ formatApplicantName(app) }}</td>
                <td class="py-2.5 pr-4 text-gray-600 truncate max-w-[140px]">{{ app.vacancy?.position_title ?? '—' }}</td>
                <td class="py-2.5 pr-4">
                  <StatusBadge :status="app.status" />
                </td>
                <td class="py-2.5 text-gray-400 whitespace-nowrap text-xs">{{ formatDate(app.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-sm text-gray-400 text-center py-8">No applications yet.</p>
      </div>

      <!-- Upcoming Schedules (1/3) -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Upcoming Schedules</h2>
        <div v-if="scheduleLoading" class="space-y-2.5">
          <div v-for="n in 4" :key="n" class="h-[60px] bg-gray-100 rounded-lg animate-pulse"></div>
        </div>
        <div v-else-if="upcomingAll.length" class="space-y-2.5">
          <div v-for="item in upcomingAll" :key="item.type + item.id"
            class="rounded-lg border p-3 text-xs"
            :class="item.type === 'exam' ? 'bg-orange-50 border-orange-200' : 'bg-violet-50 border-violet-200'">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-[10px] font-bold uppercase tracking-wider"
                :class="item.type === 'exam' ? 'text-orange-700' : 'text-violet-700'">
                {{ item.type === 'exam' ? 'Exam' : 'Interview' }}
              </span>
              <span class="truncate" :class="item.type === 'exam' ? 'text-orange-600' : 'text-violet-600'">
                {{ formatDateTime(item.scheduled_at) }}
              </span>
            </div>
            <p class="font-medium text-gray-900 truncate">{{ formatApplicantName(item.application) }}</p>
            <p class="text-gray-500 truncate mt-0.5">{{ item.application?.vacancy?.position_title ?? '—' }}</p>
          </div>
        </div>
        <div v-else class="flex flex-col items-center justify-center py-10 text-center">
          <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <p class="text-sm text-gray-400">No upcoming schedules</p>
        </div>
      </div>

    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'
import api from '@/services/api'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

const loading         = ref(true)
const scheduleLoading = ref(true)
const stats           = ref({})
const pipeline        = ref([])
const recentApplications = ref([])
const upcoming        = ref({ exams: [], interviews: [] })
const autoRefresh     = ref(false)
let refreshInterval   = null

watch(autoRefresh, (val) => {
  if (val) {
    refreshInterval = setInterval(loadDashboard, 30000)
  } else {
    clearInterval(refreshInterval)
  }
})

onBeforeUnmount(() => {
  clearInterval(refreshInterval)
})

// ── Pipeline ──────────────────────────────────────────────────────────────────
const STATUS_ORDER = [
  'submitted', 'under_review', 'screened', 'qualified', 'disqualified',
  'exam_scheduled', 'shortlisted', 'for_interview', 'interviewed',
  'recommended', 'appointed', 'completed', 'withdrawn',
]
const STATUS_LABELS = {
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
}
const STATUS_CHIP = {
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
}

function statusLabel(status) {
  return STATUS_LABELS[status] ?? status.replaceAll('_', ' ')
}
function statusChipClass(status) {
  return STATUS_CHIP[status] ?? 'bg-gray-50 text-gray-500 border-gray-200'
}

const sortedPipeline = computed(() =>
  [...pipeline.value].sort((a, b) => {
    const ai = STATUS_ORDER.indexOf(a.status)
    const bi = STATUS_ORDER.indexOf(b.status)
    return (ai === -1 ? 99 : ai) - (bi === -1 ? 99 : bi)
  })
)

// ── Upcoming ──────────────────────────────────────────────────────────────────
const upcomingAll = computed(() => {
  const exams      = (upcoming.value.exams ?? []).map(e => ({ ...e, type: 'exam' }))
  const interviews = (upcoming.value.interviews ?? []).map(i => ({ ...i, type: 'interview' }))
  return [...exams, ...interviews].sort(
    (a, b) => new Date(a.scheduled_at) - new Date(b.scheduled_at)
  )
})

// ── Stat cards ────────────────────────────────────────────────────────────────
const statCards = computed(() => [
  {
    label: 'Total Vacancies', value: stats.value.vacancies?.total ?? 0,
    sub: `${stats.value.vacancies?.published ?? 0} published`,
    link: '/admin/vacancies',
    icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    iconBg: 'bg-[#2a338f]/10', iconColor: 'text-[#2a338f]',
  },
  {
    label: 'Closing Soon', value: stats.value.vacancies?.closing_soon ?? 0,
    sub: 'within 7 days',
    link: '/admin/vacancies?status=published',
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    iconBg: 'bg-yellow-100', iconColor: 'text-yellow-700',
  },
  {
    label: 'Total Applications', value: stats.value.applications?.total ?? 0,
    sub: `${stats.value.applications?.this_month ?? 0} this month`,
    link: '/admin/applications',
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    iconBg: 'bg-green-100', iconColor: 'text-green-700',
  },
  {
    label: 'Pending Review', value: stats.value.applications?.pending ?? 0,
    sub: 'awaiting action',
    link: '/admin/applications?status=submitted',
    icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    iconBg: 'bg-red-100', iconColor: 'text-red-700',
  },
])

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatApplicantName(app) {
  const p = app?.applicant
  if (p?.last_name && p?.first_name) {
    const middle = p.middle_name ? ' ' + p.middle_name.charAt(0).toUpperCase() + '.' : ''
    return `${p.last_name}, ${p.first_name}${middle}`
  }
  return p?.user?.name ?? '—'
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function formatDateTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-PH', {
    month: 'short', day: 'numeric',
    hour: 'numeric', minute: '2-digit', hour12: true,
  })
}

// ── Data fetching ─────────────────────────────────────────────────────────────
async function loadDashboard() {
  loading.value = true
  scheduleLoading.value = true
  try {
    const [statsRes, pipelineRes, recentRes] = await Promise.all([
      api.get('/dashboard/stats'),
      api.get('/dashboard/pipeline'),
      api.get('/dashboard/recent-applications'),
    ])
    stats.value              = statsRes.data
    pipeline.value           = pipelineRes.data
    recentApplications.value = recentRes.data
  } catch (e) {
    console.error('Dashboard load failed', e)
  } finally {
    loading.value = false
  }

  try {
    const { data } = await api.get('/dashboard/upcoming')
    upcoming.value = data
  } catch {
    upcoming.value = { exams: [], interviews: [] }
  } finally {
    scheduleLoading.value = false
  }
}

onMounted(loadDashboard)
</script>
