<template>
  <ApplicantLayout>
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">

      <!-- Page header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">My Applications</h1>
          <p class="text-sm text-gray-500 mt-1">Track the status of all your submitted applications.</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="fetchApplications"
            class="inline-flex items-center gap-2 px-3.5 py-2 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
            </svg>
            Refresh
          </button>
          <Link href="/applicant/dashboard?tab=vacancies"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Browse Vacancies
          </Link>
        </div>
      </div>

      <!-- Status filter pills -->
      <div class="flex flex-wrap gap-1.5 mb-6">
        <button v-for="tab in statusTabs" :key="tab.value"
          @click="activeStatus = tab.value"
          class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors"
          :class="activeStatus === tab.value
            ? 'bg-[#2a338f] text-white shadow-sm'
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
        <div v-for="n in 3" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
              <div class="h-3 bg-gray-100 rounded w-1/3"></div>
              <div class="h-3 bg-gray-100 rounded w-1/4 mt-2"></div>
            </div>
            <div class="h-5 w-20 bg-gray-100 rounded-full flex-shrink-0"></div>
          </div>
        </div>
      </div>

      <!-- Application cards -->
      <div v-else-if="filteredApplications.length" class="space-y-4">
        <div v-for="app in filteredApplications" :key="app.id"
          class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:border-[#2a338f]/30 hover:shadow-md transition-all">

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
                  <p class="text-base font-semibold text-gray-900 leading-snug truncate">
                    {{ app.vacancy?.position_title ?? 'Unknown Position' }}
                  </p>
                  <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                    <span>{{ app.vacancy?.place_of_assignment ?? '' }}</span>
                    <span v-if="app.vacancy?.salary_grade"
                      class="text-xs font-semibold px-1.5 py-0.5 bg-[#2a338f]/8 text-[#2a338f] rounded">
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
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  Submitted {{ formatDate(app.submitted_at ?? app.created_at) }}
                </span>
                <span v-if="app.reviewed_at" class="flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Updated {{ formatDate(app.reviewed_at) }}
                </span>
                <span v-if="app.vacancy?.published_at" class="flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  Published {{ formatDate(app.vacancy.published_at) }}
                </span>
                <span v-if="app.vacancy?.deadline_at" class="flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Deadline {{ formatDate(app.vacancy.deadline_at) }}
                </span>
              </div>
            </div>
          </div>

          <!-- HR Remarks -->
          <div v-if="app.remarks"
            class="mx-5 mb-4 px-4 py-3 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-2.5">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <div>
              <p class="text-xs font-semibold text-blue-700 mb-0.5">Note from HR</p>
              <p class="text-xs text-blue-700 leading-relaxed">{{ app.remarks }}</p>
            </div>
          </div>

          <!-- Exam schedule banner -->
          <div v-if="app.status === 'exam_scheduled' && app.exam_schedule?.length"
            class="mx-5 mb-4 px-4 py-3 bg-orange-50 border border-orange-200 rounded-lg flex items-start gap-2.5">
            <svg class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
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
            <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
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
                      isPipelinePast(step.key, app.status) || app.status === step.key ? 'bg-[#2a338f]' : 'bg-gray-200'">
                  </div>
                  <div :class="[
                    'rounded-full transition-all flex-shrink-0 w-2.5 h-2.5',
                    app.status === step.key
                      ? 'bg-[#2a338f] ring-2 ring-[#2a338f]/30 ring-offset-1'
                      : isPipelinePast(step.key, app.status)
                        ? 'bg-[#2a338f]'
                        : isTerminal(app.status)
                          ? 'bg-gray-100'
                          : 'bg-gray-200'
                  ]"></div>
                  <div class="flex-1 h-0.5 transition-colors"
                    :class="idx === pipeline.length - 1 ? 'invisible' :
                      isPipelinePast(pipeline[idx + 1].key, app.status) || app.status === pipeline[idx + 1].key ? 'bg-[#2a338f]' : 'bg-gray-200'">
                  </div>
                </div>
                <!-- Short label (desktop only) -->
                <span class="mt-1 text-[8px] leading-tight text-center w-full hidden sm:block"
                  :class="app.status === step.key ? 'text-[#2a338f] font-semibold' : 'text-gray-400'">
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
          <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-gray-500">
          {{ activeStatus === 'all' ? 'No applications yet' : `No "${statusLabel(activeStatus)}" applications` }}
        </p>
        <p class="text-xs text-gray-400 mt-1">Browse open vacancies and submit your first application.</p>
        <Link href="/applicant/dashboard?tab=vacancies"
          class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors">
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
        <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>
      <h3 class="text-base font-semibold text-gray-900 text-center mb-1">Withdraw Application?</h3>
      <p class="text-sm text-gray-500 text-center mb-1">
        <strong class="text-gray-700">{{ withdrawTarget.vacancy?.position_title }}</strong>
      </p>
      <p class="text-xs text-gray-400 text-center mb-6">This action cannot be undone. You will not be able to reapply for this position.</p>
      <div class="mb-4">
        <label class="block text-xs font-medium text-gray-600 mb-1.5">Reason for withdrawal (optional)</label>
        <textarea v-model="withdrawReason" rows="2" placeholder="Tell us why you're withdrawing..."
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none resize-none"></textarea>
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
import { useToast } from '@/composables/useToast'

const toast = useToast()

// ── State ─────────────────────────────────────────────────────────────────────
const applications   = ref([])
const loading        = ref(true)
const activeStatus   = ref('all')
const withdrawTarget  = ref(null)
const withdrawing     = ref(false)
const withdrawReason  = ref('')

watch(activeStatus, () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
})

// ── Auth ──────────────────────────────────────────────────────────────────────
function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

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

// ── Pipeline definition (ordered) ────────────────────────────────────────────
const pipeline = [
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

const pipelineOrder = pipeline.map(s => s.key)
const terminalStatuses = new Set(['appointed', 'completed', 'disqualified', 'withdrawn', 'failed'])

function pipelineStep(status) {
  const idx = pipelineOrder.indexOf(status)
  return idx >= 0 ? idx + 1 : '?'
}

function isPipelinePast(key, currentStatus) {
  return pipelineOrder.indexOf(key) < pipelineOrder.indexOf(currentStatus)
}

function isTerminal(status) { return terminalStatuses.has(status) }

function canWithdraw(status) {
  return ['submitted', 'under_review', 'screened'].includes(status)
}

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
  const list = applications.value
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
  return group ? list.filter(a => group.includes(a.status)) : list
})

// ── Helpers ───────────────────────────────────────────────────────────────────
const statusLabels = {
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
}

function statusLabel(status) {
  return statusLabels[status] ?? status.replace(/_/g, ' ')
}

function statusIcon(status) {
  const map = {
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
  return map[status] ?? { bg: 'bg-gray-50', color: 'text-gray-400', path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
}

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatDateTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: 'numeric', minute: '2-digit', hour12: true,
  })
}

// ── Withdraw ──────────────────────────────────────────────────────────────────
function confirmWithdraw(app) { withdrawTarget.value = app }

async function doWithdraw() {
  withdrawing.value = true
  try {
    await axios.patch(
      `/api/applications/${withdrawTarget.value.id}/status`,
      { status: 'withdrawn', remarks: withdrawReason.value || null },
      { headers: authHeaders() },
    )
    toast.success('Application withdrawn successfully.')
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
