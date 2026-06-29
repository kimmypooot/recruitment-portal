<template>
  <ApplicantLayout>
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">

      <!-- Page header -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">My Applications</h1>
          <p class="text-sm text-gray-500 mt-1">Track the status of all your submitted applications.</p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <button @click="fetchApplications"
            class="inline-flex items-center gap-2 px-3.5 py-2 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <Icon name="refresh" size="4" />
            Refresh
          </button>
          <Link href="/applicant/dashboard?tab=vacancies"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
            <Icon name="search" size="4" />
            Browse Vacancies
          </Link>
        </div>
      </div>

      <!-- Search + sort toolbar -->
      <div class="flex flex-wrap gap-2 mb-4">
        <div class="relative flex-1 min-w-48">
          <Icon name="search" size="4" color="text-gray-400" class="absolute left-3 top-1/2 -translate-y-1/2" />
          <input v-model="search" type="text" placeholder="Search by position title…"
            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
        </div>
        <select v-model="sortBy"
          class="text-sm border border-gray-300 rounded-lg px-3 pr-8 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-primary focus:outline-none">
          <option value="newest">Newest First</option>
          <option value="oldest">Oldest First</option>
          <option value="status">By Status</option>
        </select>
        <button v-if="search" @click="search = ''"
          class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-gray-600 transition-colors px-2">
          <Icon name="close" size="3.5" />
          Clear
        </button>
      </div>

      <!-- Status filter pills -->
      <div class="flex flex-wrap gap-1.5 mb-6">
        <button v-for="tab in statusTabs" :key="tab.value"
          @click="activeStatus = tab.value"
          class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors"
          :class="activeStatus === tab.value
            ? 'bg-primary text-white shadow-sm'
            : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
          {{ tab.label }}
          <span
            class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold"
            :class="activeStatus === tab.value ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'">
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Loading skeletons -->
      <div v-if="loading" class="space-y-4">
        <SkeletonLoader v-for="n in 3" :key="n" variant="application-row" />
      </div>

      <!-- Application cards -->
      <div v-else-if="filteredApplications.length" class="space-y-4">
        <div v-for="app in filteredApplications" :key="app.id"
          class="bg-white rounded-xl border border-gray-200 border-l-4 shadow-sm overflow-hidden hover:shadow-md transition-all"
          :class="statusBorderClass(app.status)">

          <!-- Card header -->
          <div class="p-5 flex items-start gap-4">

            <!-- Status icon -->
            <div :class="statusIcon(app.status).bg"
              class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg class="w-5 h-5" :class="statusIcon(app.status).color"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" :d="statusIcon(app.status).path"/>
              </svg>
            </div>

            <!-- Position info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-base font-semibold text-gray-900 leading-snug flex items-center gap-2 flex-wrap">
                    <span class="truncate">{{ app.vacancy?.position_title ?? 'Unknown Position' }}</span>
                    <span v-if="app.remarks"
                      class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-orange-100 text-orange-600 text-[10px] font-semibold rounded-full flex-shrink-0">
                      <span class="w-1.5 h-1.5 rounded-full bg-orange-400 animate-pulse"></span>
                      HR Note
                    </span>
                  </p>
                  <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                    <span>{{ app.vacancy?.place_of_assignment ?? '' }}</span>
                    <span v-if="app.vacancy?.salary_grade"
                      class="text-xs font-semibold px-1.5 py-0.5 bg-primary/8 text-primary rounded">
                      SG-{{ app.vacancy.salary_grade }}
                    </span>
                  </p>
                  <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-xs text-gray-400">
                    <span v-if="app.vacancy?.plantilla_no">Plantilla Item No.: {{ app.vacancy.plantilla_no }}</span>
                    <span v-if="app.vacancy?.position_level">{{ app.vacancy.position_level }}</span>
                    <span v-if="app.vacancy?.monthly_salary">₱ {{ Number(app.vacancy.monthly_salary).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                  </div>
                </div>
                <StatusBadge :status="app.status" class="flex-shrink-0 mt-0.5" />
              </div>

              <!-- Timestamps -->
              <div class="flex flex-wrap items-center gap-3 mt-3 text-xs text-gray-400">
                <span class="flex items-center gap-1">
                  <Icon name="calendar" size="3" />
                  Submitted {{ formatDate(app.submitted_at ?? app.created_at) }}
                </span>
                <span v-if="app.reviewed_at" class="flex items-center gap-1">
                  <Icon name="clock" size="3" />
                  Updated {{ formatDate(app.reviewed_at) }}
                </span>
                <span v-if="app.vacancy?.published_at" class="flex items-center gap-1">
                  <Icon name="calendar" size="3" />
                  Published {{ formatDate(app.vacancy.published_at) }}
                </span>
                <span v-if="app.vacancy?.deadline_at" class="flex items-center gap-1">
                  <Icon name="clock" size="3" />
                  Deadline {{ formatDate(app.vacancy.deadline_at) }}
                </span>
              </div>
            </div>
          </div>

          <!-- HR Remarks -->
          <div v-if="app.remarks"
            class="mx-5 mb-4 px-4 py-3 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-2.5">
            <Icon name="chat" size="4" color="text-blue-500" class="flex-shrink-0 mt-0.5" />
            <div>
              <p class="text-xs font-semibold text-blue-700 mb-0.5">Note from HR</p>
              <p class="text-xs text-blue-700 leading-relaxed">{{ app.remarks }}</p>
            </div>
          </div>

          <!-- Exam schedule banner -->
          <div v-if="app.status === 'exam_scheduled' && app.exam_schedule?.length"
            class="mx-5 mb-4 px-4 py-3 bg-orange-50 border border-orange-200 rounded-lg flex items-start gap-2.5">
            <Icon name="calendar" size="4" color="text-orange-500" class="flex-shrink-0 mt-0.5" />
            <div>
              <p class="text-xs font-semibold text-orange-800 mb-0.5">Exam Scheduled</p>
              <p class="text-xs text-orange-700">{{ formatDateTime(app.exam_schedule[0].scheduled_at) }}</p>
              <p v-if="app.exam_schedule[0].venue" class="text-xs text-orange-600 mt-0.5">Venue: {{ app.exam_schedule[0].venue }}</p>
              <p v-if="app.exam_schedule[0].notes" class="text-xs text-orange-500 mt-0.5 italic">{{ app.exam_schedule[0].notes }}</p>
            </div>
          </div>

          <!-- Interview schedule banner -->
          <div v-if="(app.status === 'for_interview' || app.status === 'interviewed') && app.interview_schedule?.length"
            class="mx-5 mb-4 px-4 py-3 bg-violet-50 border border-violet-200 rounded-lg flex items-start gap-2.5">
            <Icon name="user" size="4" color="text-violet-500" class="flex-shrink-0 mt-0.5" />
            <div>
              <p class="text-xs font-semibold text-violet-800 mb-0.5">Interview Scheduled</p>
              <p class="text-xs text-violet-700">{{ formatDateTime(app.interview_schedule[0].scheduled_at) }}</p>
              <p v-if="app.interview_schedule[0].venue" class="text-xs text-violet-600 mt-0.5">Venue: {{ app.interview_schedule[0].venue }}</p>
              <p v-if="app.interview_schedule[0].notes" class="text-xs text-violet-500 mt-0.5 italic">{{ app.interview_schedule[0].notes }}</p>
            </div>
          </div>

          <!-- Pipeline progress -->
          <div class="px-5 pb-5">
            <div class="flex items-start gap-0">
              <div v-for="(step, idx) in pipeline" :key="step.key" class="flex-1 flex flex-col items-center min-w-0">
                <!-- Dot row with half-connectors -->
                <div class="flex items-center w-full">
                  <div class="flex-1 h-0.5 transition-colors"
                    :class="idx === 0 ? 'invisible' :
                      isPipelinePast(step.key, app.status) || app.status === step.key ? 'bg-primary' : 'bg-gray-200'">
                   </div>
                   <div :class="[
                     'rounded-full transition-all flex-shrink-0 w-2.5 h-2.5',
                     app.status === step.key
                       ? 'bg-primary ring-2 ring-primary/30 ring-offset-1'
                       : isPipelinePast(step.key, app.status)
                         ? 'bg-primary'
                         : isTerminal(app.status)
                           ? 'bg-gray-100'
                           : 'bg-gray-200'
                   ]"></div>
                   <div class="flex-1 h-0.5 transition-colors"
                     :class="idx === pipeline.length - 1 ? 'invisible' :
                       isPipelinePast(pipeline[idx + 1].key, app.status) || app.status === pipeline[idx + 1].key ? 'bg-primary' : 'bg-gray-200'">
                   </div>
                </div>
                <!-- Short label (desktop only) -->
                <span class="mt-1 text-[8px] leading-tight text-center w-full hidden sm:block"
                  :class="app.status === step.key ? 'text-primary font-semibold' : 'text-gray-400'">
                  {{ step.short }}
                </span>
              </div>
            </div>

            <!-- Current stage label -->
            <p class="mt-2 text-xs text-gray-400">
              <span v-if="isTerminal(app.status)">
                <span v-if="app.status === 'withdrawn'" class="text-gray-500">Application withdrawn</span>
                <span v-else-if="app.status === 'appointed'" class="text-green-600 font-semibold">Congratulations — appointed!</span>
                <span v-else-if="app.status === 'completed'" class="text-green-600 font-medium">Process completed</span>
                <span v-else-if="app.status === 'disqualified'" class="text-red-500">Disqualified from selection</span>
              </span>
              <span v-else>
                Stage <strong class="text-gray-600">{{ pipelineStep(app.status) }}</strong> of {{ pipeline.length }}
                <span class="mx-1 text-gray-200">·</span>
                <span class="text-gray-500">{{ statusLabel(app.status) }}</span>
              </span>
            </p>
          </div>

          <!-- Withdraw footer (only for early-stage applications) -->
          <div v-if="canWithdraw(app.status)"
            class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">Changed your mind?</p>
            <button @click="confirmWithdraw(app)"
              class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">
              Withdraw Application
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="text-center py-20">
        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
          <Icon name="document" size="8" color="text-gray-300" />
        </div>
        <p class="text-sm font-semibold text-gray-500">
          {{ activeStatus === 'all' ? 'No applications yet' : `No "${statusLabel(activeStatus)}" applications` }}
        </p>
        <p class="text-xs text-gray-400 mt-1">Browse open vacancies and submit your first application.</p>
        <Link href="/applicant/dashboard?tab=vacancies"
          class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg transition-colors">
          Browse Vacancies
        </Link>
      </div>

    </div>
  </ApplicantLayout>

  <!-- ── Withdraw Confirm Modal ─────────────────────────────────────────────── -->
  <Teleport to="body">
  <div v-if="withdrawTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" @click="withdrawTarget = null"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
      <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
        <Icon name="alert" size="6" color="text-red-500" />
      </div>
      <h3 class="text-base font-semibold text-gray-900 text-center mb-1">Withdraw Application?</h3>
      <p class="text-sm text-gray-500 text-center mb-1">
        <strong class="text-gray-700">{{ withdrawTarget.vacancy?.position_title }}</strong>
      </p>
      <p class="text-xs text-gray-400 text-center mb-6">This action cannot be undone. You will not be able to reapply for this position.</p>
      <div class="mb-4">
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Reason for withdrawal (optional)</label>
        <textarea v-model="withdrawReason" rows="2" placeholder="Tell us why you're withdrawing..."
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none resize-none"></textarea>
      </div>
      <div class="flex gap-3">
        <button @click="withdrawTarget = null"
          class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
          Cancel
        </button>
        <button @click="doWithdraw" :disabled="withdrawing"
          class="flex-1 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-semibold disabled:opacity-60">
          <span v-if="withdrawing">Withdrawing…</span>
          <span v-else>Yes, Withdraw</span>
        </button>
      </div>
    </div>
  </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import { applicationApi, profileApi } from '@/services/api'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import ApplicantLayout from '@/Layouts/ApplicantLayout.vue'
import Icon from '@/Components/UI/Icon.vue'
import SkeletonLoader from '@/Components/UI/SkeletonLoader.vue'
import {
  pipelineStep, isPipelinePast, isTerminal, canWithdraw,
  statusLabel, statusIcon, statusBorderClass,
  PIPELINE as pipeline,
} from '@/config/statusConfig'
import { formatDate, formatDateLong, formatDateTime, formatDateRange, daysRemaining } from '@/utils/dates'
import { useToast } from '@/composables/useToast'

const toast = useToast()

// ── State ─────────────────────────────────────────────────────────────────────
const applications   = ref([])
const loading        = ref(true)
const activeStatus   = ref('all')
const withdrawTarget  = ref(null)
const withdrawing     = ref(false)
const withdrawReason  = ref('')
const search          = ref('')
const sortBy          = ref('newest')

watch(activeStatus, () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
})

async function fetchApplications() {
  if (!localStorage.getItem('auth_token')) return
  try {
    const { data } = await applicationApi.myApplications()
    applications.value = data
  } catch { /* 401 handled by interceptor */ }
}

function onVisibilityChange() {
  if (document.visibilityState === 'visible') fetchApplications()
}

onMounted(async () => {
  if (!localStorage.getItem('auth_token')) { router.visit('/login'); return }
  try {
    const { data } = await profileApi.show()
    localStorage.setItem('profile_complete', data.is_complete)
    window.dispatchEvent(new CustomEvent('profile-complete-changed'))
    if (!data.is_complete) {
      window.location.href = '/applicant/complete-profile'
      return
    }
  } catch {
    window.location.href = '/login'
    return
  }
  await fetchApplications()
  loading.value = false
  document.addEventListener('visibilitychange', onVisibilityChange)
})

onBeforeUnmount(() => {
  document.removeEventListener('visibilitychange', onVisibilityChange)
})

// ── Status tabs ───────────────────────────────────────────────────────────────
const statusTabs = computed(() => {
  const list = applications.value
  const count = (statuses) =>
    Array.isArray(statuses)
      ? list.filter(a => statuses.includes(a.status)).length
      : list.filter(a => a.status === statuses).length

  return [
    { value: 'all',         label: 'All',          count: list.length },
    { value: 'in_progress', label: 'In Progress',   count: count(['submitted', 'under_review', 'screened', 'qualified']) },
    { value: 'selection',   label: 'Selection',     count: count(['exam_scheduled', 'shortlisted', 'for_interview', 'interviewed', 'recommended']) },
    { value: 'appointed',   label: 'Appointed',     count: count('appointed') },
    { value: 'disqualified', label: 'Disqualified', count: count('disqualified') },
    { value: 'failed',      label: 'Failed',        count: count('failed') },
    { value: 'withdrawn',   label: 'Withdrawn',     count: count('withdrawn') },
  ].filter(t => t.value === 'all' || t.count > 0 || activeStatus.value === t.value)
})

const filteredApplications = computed(() => {
  const groupMap = {
    all:          null,
    in_progress:  ['submitted', 'under_review', 'screened', 'qualified'],
    selection:    ['exam_scheduled', 'shortlisted', 'for_interview', 'interviewed', 'recommended'],
    appointed:    ['appointed', 'completed'],
    disqualified: ['disqualified'],
    failed:       ['failed'],
    withdrawn:    ['withdrawn'],
  }
  const group = groupMap[activeStatus.value]
  let list = group ? applications.value.filter(a => group.includes(a.status)) : [...applications.value]

  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(a => a.vacancy?.position_title?.toLowerCase().includes(q))
  }

  if (sortBy.value === 'oldest')
    list = [...list].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
  else if (sortBy.value === 'newest')
    list = [...list].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  else if (sortBy.value === 'status')
    list = [...list].sort((a, b) => a.status.localeCompare(b.status))

  return list
})

// ── Withdraw ──────────────────────────────────────────────────────────────────
function confirmWithdraw(app) { withdrawTarget.value = app }

async function doWithdraw() {
  withdrawing.value = true
  try {
    await axios.patch(
      `/api/applications/${withdrawTarget.value.id}/withdraw`,
      { remarks: withdrawReason.value || null },
    )
    toast.error('Application withdrawn.')
    const idx = applications.value.findIndex(a => a.id === withdrawTarget.value.id)
    if (idx >= 0) applications.value[idx].status = 'withdrawn'
    withdrawTarget.value = null
    withdrawReason.value = ''
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to withdraw application.')
  } finally {
    withdrawing.value = false
  }
}
</script>
