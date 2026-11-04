<template>
  <AdminLayout title="Dashboard">

    <!-- Error banner -->
    <div v-if="loadError" role="alert"
      class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex items-center gap-3">
      <Icon name="alert" size="5" class="flex-shrink-0 text-red-500" />
      <span>{{ loadError }}</span>
      <button @click="loadDashboard" class="ml-auto text-sm font-medium text-red-700 underline hover:no-underline">Retry</button>
    </div>

    <!-- Stat cards -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-6">
      <div v-for="n in 4" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 flex items-start gap-4 shadow-sm animate-pulse">
        <div class="w-11 h-11 rounded-xl bg-gray-200 flex-shrink-0"></div>
        <div class="flex-1 space-y-2">
          <div class="h-3 bg-gray-200 rounded w-16"></div>
          <div class="h-7 bg-gray-200 rounded w-10"></div>
        </div>
      </div>
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-6">
      <Link v-for="(card, i) in statCards" :key="card.label" :href="card.link" :style="{ '--i': i }"
        class="rise-in card-lift bg-white rounded-xl border border-gray-200 p-4 sm:p-5 flex items-center sm:items-start gap-3 sm:gap-4 shadow-sm hover:border-primary/30 hover:shadow-md cursor-pointer">
        <div :class="card.iconBg" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center flex-shrink-0">
          <Icon :name="card.icon" size="5" :class="card.iconColor" />
        </div>
        <div class="min-w-0">
          <p class="text-xs text-gray-500 font-medium">{{ card.label }}</p>
          <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-0.5"><AnimatedNumber :value="Number(card.value) || 0" /></p>
          <p v-if="card.sub" class="text-xs text-gray-400 mt-0.5">{{ card.sub }}</p>
        </div>
      </Link>
    </div>

    <!-- Charts -->
    <div v-if="!loading && pipelineByVacancy.length" class="grid grid-cols-1 lg:grid-cols-5 gap-4 sm:gap-5 mb-6">
      <div class="rise-in lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5" style="--i: 4">
        <h2 class="text-sm font-semibold text-gray-900 mb-1">Applications by Status</h2>
        <p class="text-xs text-gray-400 mb-3">{{ totalApplications }} across all vacancies</p>
        <ChartCanvas type="doughnut" :data="statusChart" :options="donutOptions" :height="230"
          aria-label="Doughnut chart of applications by status" />
      </div>
      <div class="rise-in lg:col-span-3 bg-white rounded-xl border border-gray-200 shadow-sm p-5" style="--i: 5">
        <h2 class="text-sm font-semibold text-gray-900 mb-1">Busiest Vacancies</h2>
        <p class="text-xs text-gray-400 mb-3">Top {{ vacancyChart.labels.length }} by number of applications</p>
        <ChartCanvas type="bar" :data="vacancyChart" :options="barOptions" :height="230"
          aria-label="Bar chart of applications per vacancy" />
      </div>
    </div>

    <!-- Pipeline per vacancy -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
      <div class="p-5 pb-0">
        <h2 class="text-sm font-semibold text-gray-900">Application Pipeline by Vacancy</h2>
      </div>

      <!-- Skeleton matching table layout -->
      <div v-if="loading" class="animate-pulse p-5 space-y-4">
        <div v-for="n in 3" :key="n">
          <div class="flex items-start gap-3 pb-3 mb-3 border-b border-gray-100 last:border-0">
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 rounded w-2/3"></div>
              <div class="h-3 bg-gray-100 rounded w-1/3"></div>
            </div>
            <div class="h-5 w-8 bg-gray-200 rounded-full flex-shrink-0"></div>
            <div class="flex gap-1.5">
              <div class="h-6 w-16 bg-gray-100 rounded-full"></div>
              <div class="h-6 w-14 bg-gray-100 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="pipelineByVacancy.length" class="divide-y divide-gray-100">
        <div v-for="item in pipelineByVacancy" :key="item.vacancy_id" class="px-5 py-3.5 hover:bg-gray-50/50 transition-colors">
          <div class="flex items-start gap-2">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <p class="text-sm font-medium text-gray-900 truncate">{{ item.position_title }}</p>
                <span class="text-xs text-gray-400 shrink-0">({{ item.total }})</span>
              </div>
              <p v-if="item.place_of_assignment" class="text-xs text-gray-400 mt-0.5 truncate">{{ item.place_of_assignment }}</p>
            </div>
            <div class="flex flex-wrap gap-1.5 shrink-0">
              <span v-for="(count, status) in item.statuses" :key="status"
                :class="statusChipClass(status)"
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium border leading-tight">
                <span class="max-w-[72px] truncate">{{ statusLabel(status) }}</span>
                <span class="font-bold">{{ count }}</span>
              </span>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="px-5 py-8 text-center">
        <div v-if="!pipelineByVacancy.length && !loading">
          <p class="text-sm text-gray-400">No applications yet.</p>
        </div>
      </div>
    </div>

    <!-- Pipeline color legend -->
    <div class="mt-3 mb-6 flex flex-wrap gap-2 text-[10px] text-gray-400">
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
          <h2 class="text-sm font-semibold text-gray-900">Recent Applications</h2>
          <div class="flex flex-wrap items-center gap-2">
            <span v-if="lastRefreshed"
              class="text-xs transition-colors duration-500"
              :class="refreshFlash ? 'text-green-600 font-medium' : 'text-gray-400'">
              Updated {{ timeAgo(lastRefreshed) }}
            </span>
            <button @click="autoRefresh = !autoRefresh"
              class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-medium transition-colors"
              :class="autoRefresh ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
              <span class="w-1.5 h-1.5 rounded-full" :class="autoRefresh ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"></span>
              {{ autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh OFF' }}
            </button>
            <Link href="/admin/applications" class="text-xs text-primary hover:underline font-medium">View all</Link>
          </div>
        </div>

        <!-- Skeleton matching table rows -->
        <div v-if="loading" class="animate-pulse space-y-3">
          <div v-for="n in 5" :key="n" class="flex items-center gap-4 h-12 border-b border-gray-50">
            <div class="flex-1 space-y-1.5">
              <div class="h-3 bg-gray-100 rounded w-1/3"></div>
              <div class="h-2.5 bg-gray-100 rounded w-1/4"></div>
            </div>
            <div class="h-3 bg-gray-100 rounded w-24 hidden sm:block"></div>
            <div class="h-5 w-20 bg-gray-100 rounded-full"></div>
            <div class="h-3 bg-gray-100 rounded w-14"></div>
          </div>
        </div>

        <div v-else-if="recentApplications.length" class="-mx-5 sm:mx-0 overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                <th class="pb-2 px-5 sm:px-0 font-medium">Applicant</th>
                <th class="pb-2 font-medium min-w-[120px]">Position</th>
                <th class="pb-2 font-medium">Status</th>
                <th class="pb-2 px-5 sm:px-0 font-medium">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="app in recentApplications" :key="app.id" class="hover:bg-gray-50 transition-colors">
                <td class="py-2.5 px-5 sm:px-0 pr-4 text-gray-900 font-medium whitespace-nowrap">{{ formatApplicantName(app) }}</td>
                <td class="py-2.5 pr-4 text-gray-600 truncate max-w-[160px] sm:max-w-[200px]" :title="app.vacancy?.position_title ?? ''">{{ app.vacancy?.position_title ?? '—' }}</td>
                <td class="py-2.5 pr-4">
                  <StatusBadge :status="app.status" />
                </td>
                <td class="py-2.5 px-5 sm:px-0 text-gray-400 whitespace-nowrap text-xs">{{ formatDate(app.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-sm text-gray-400 text-center py-8">No applications yet.</p>
      </div>

      <!-- Upcoming Schedules (1/3) -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Upcoming Schedules</h2>

        <!-- Skeleton matching card layout -->
        <div v-if="scheduleLoading" class="animate-pulse space-y-3">
          <div v-for="n in 4" :key="n" class="rounded-lg border border-gray-100 p-3">
            <div class="flex items-center gap-2 mb-2">
              <div class="h-3 w-10 bg-gray-200 rounded"></div>
              <div class="h-3 w-20 bg-gray-100 rounded"></div>
            </div>
            <div class="h-3 bg-gray-100 rounded w-1/2 mb-1.5"></div>
            <div class="h-2.5 bg-gray-100 rounded w-1/3"></div>
          </div>
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
            <Icon name="calendar" size="5" class="text-gray-300" />
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
import Icon from '@/Components/UI/Icon.vue'
import AnimatedNumber from '@/Components/UI/AnimatedNumber.vue'
import ChartCanvas from '@/Components/UI/ChartCanvas.vue'
import { useTheme } from '@/composables/useTheme'
import { statusLabel, statusChipClass } from '@/config/statusConfig'
import { formatDate, formatDateTime, timeAgo } from '@/utils/dates'

const { isDark } = useTheme()
const loading         = ref(true)
const scheduleLoading = ref(true)
const loadError       = ref(null)
const stats           = ref({})
const pipelineByVacancy = ref([])
const recentApplications = ref([])
const upcoming        = ref({ exams: [], interviews: [] })
const autoRefresh     = ref(false)
const lastRefreshed   = ref(null)
const refreshFlash    = ref(false)
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
    icon: 'briefcase',
    iconBg: 'bg-primary/10', iconColor: 'text-primary',
  },
  {
    label: 'Closing Soon', value: stats.value.vacancies?.closing_soon ?? 0,
    sub: 'within 7 days',
    link: '/admin/vacancies?status=published',
    icon: 'clock',
    iconBg: 'bg-yellow-100', iconColor: 'text-yellow-700',
  },
  {
    label: 'Total Applications', value: stats.value.applications?.total ?? 0,
    sub: `${stats.value.applications?.this_month ?? 0} this month`,
    link: '/admin/applications',
    icon: 'document',
    iconBg: 'bg-green-100', iconColor: 'text-green-700',
  },
  {
    label: 'Pending Review', value: stats.value.applications?.pending ?? 0,
    sub: 'awaiting action',
    link: '/admin/applications?status=submitted',
    icon: 'alert',
    iconBg: 'bg-red-100', iconColor: 'text-red-700',
  },
])

// ── Charts ────────────────────────────────────────────────────────────────────
const STATUS_COLORS = {
  submitted: '#eab308', under_review: '#a855f7', screened: '#0ea5e9', qualified: '#14b8a6',
  disqualified: '#ef4444', exam_scheduled: '#f97316', shortlisted: '#6366f1', for_interview: '#8b5cf6',
  interviewed: '#3b82f6', recommended: '#22c55e', appointed: '#16a34a', completed: '#22c55e',
  withdrawn: '#6b7280', failed: '#dc2626', passed: '#10b981',
}

const statusTotals = computed(() => {
  const totals = {}
  for (const item of pipelineByVacancy.value) {
    for (const [status, count] of Object.entries(item.statuses ?? {})) {
      totals[status] = (totals[status] ?? 0) + Number(count)
    }
  }
  return Object.entries(totals).sort((a, b) => b[1] - a[1])
})

const totalApplications = computed(() => statusTotals.value.reduce((s, [, n]) => s + n, 0))

const statusChart = computed(() => ({
  labels: statusTotals.value.map(([s]) => statusLabel(s)),
  datasets: [{
    data: statusTotals.value.map(([, n]) => n),
    backgroundColor: statusTotals.value.map(([s]) => STATUS_COLORS[s] ?? '#9ca3af'),
    borderWidth: 2, borderColor: isDark.value ? '#181e33' : '#fff', hoverOffset: 6,
  }],
}))

const vacancyChart = computed(() => {
  const top = [...pipelineByVacancy.value].sort((a, b) => b.total - a.total).slice(0, 6)
  return {
    labels: top.map(v => v.position_title.length > 22 ? v.position_title.slice(0, 21) + '…' : v.position_title),
    datasets: [{
      data: top.map(v => v.total),
      backgroundColor: '#2a338f', hoverBackgroundColor: '#ec1c2d',
      borderRadius: 6, maxBarThickness: 36,
    }],
  }
})

const donutOptions = {
  cutout: '64%',
  plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 11 } } } },
}
const barOptions = computed(() => ({
  plugins: { legend: { display: false } },
  scales: {
    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: isDark.value ? '#2a3350' : '#f3f4f6' }, border: { display: false } },
  },
}))

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatApplicantName(app) {
  const p = app?.applicant
  if (p?.last_name && p?.first_name) {
    const middle = p.middle_name ? ' ' + p.middle_name.charAt(0).toUpperCase() + '.' : ''
    return `${p.last_name}, ${p.first_name}${middle}`
  }
  return p?.user?.full_name ?? '—'
}

// ── Data fetching ─────────────────────────────────────────────────────────────
async function loadDashboard() {
  loading.value = true
  scheduleLoading.value = true
  loadError.value = null
  try {
    const [statsRes, pipelineRes, recentRes] = await Promise.all([
      api.get('/dashboard/stats'),
      api.get('/dashboard/pipeline-by-vacancy'),
      api.get('/dashboard/recent-applications'),
    ])
    stats.value              = statsRes.data
    pipelineByVacancy.value  = pipelineRes.data
    recentApplications.value = recentRes.data
    lastRefreshed.value = new Date()
    if (autoRefresh.value) {
      refreshFlash.value = true
      setTimeout(() => { refreshFlash.value = false }, 1500)
    }
  } catch (e) {
    loadError.value = e?.response?.data?.message ?? 'Failed to load dashboard data.'
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
