<template>
  <HrmbsboardLayout title="Dashboard">
    <div class="space-y-6">

      <!-- Board role banner -->
      <div v-if="myRole" class="bg-[#1a5276]/5 border border-[#1a5276]/20 rounded-xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-[#1a5276] flex items-center justify-center text-white font-bold text-base flex-shrink-0">
          {{ initials }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs text-gray-400 font-medium">Your Board Role</p>
          <p class="text-sm font-semibold text-gray-900">{{ roleLabel(myRole.hrmpsb_role) }}</p>
          <p class="text-xs text-gray-500 capitalize mt-0.5">{{ myRole.member_type }} member</p>
        </div>
        <span v-if="pendingActions > 0"
          class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
          {{ pendingActions }} action{{ pendingActions !== 1 ? 's' : '' }} pending
        </span>
        <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
          <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse block"></span>
          Active
        </span>
      </div>

      <!-- No board role notice -->
      <div v-else-if="!loading"
        class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
          <p class="text-sm font-semibold text-amber-800">No active board role assigned</p>
          <p class="text-xs text-amber-700 mt-0.5">Contact the administrator to assign you to the HRMPSB composition.</p>
        </div>
      </div>

      <!-- Section header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-sm font-semibold text-gray-900">Vacancies for Evaluation</h2>
          <p class="text-xs text-gray-400 mt-0.5">Published and closed positions requiring HRMPSB action</p>
        </div>
        <div class="flex items-center gap-3">
          <span v-if="!loading" class="text-xs text-gray-400 font-medium">
            {{ vacancies.length }} position{{ vacancies.length !== 1 ? 's' : '' }}
          </span>
          <button @click="autoRefresh = !autoRefresh"
            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors"
            :class="autoRefresh ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
            <span class="w-1.5 h-1.5 rounded-full" :class="autoRefresh ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"></span>
            {{ autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh OFF' }}
          </button>
        </div>
      </div>

      <!-- Loading skeletons -->
      <div v-if="loading" class="space-y-3">
        <div v-for="n in 3" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
          <div class="h-3 bg-gray-100 rounded w-1/3 mb-4"></div>
          <div class="flex gap-1.5">
            <div v-for="i in 8" :key="i" class="h-2 flex-1 bg-gray-100 rounded"></div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="vacancies.length === 0"
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-gray-900 mb-1">No vacancies for evaluation</p>
        <p class="text-xs text-gray-400">There are no published or closed positions requiring evaluation at this time.</p>
      </div>

      <!-- Vacancy cards -->
      <div v-else class="space-y-3">
        <div v-for="v in vacancies" :key="v.id"
          class="bg-white rounded-xl border border-gray-200 shadow-sm">

          <div class="px-5 py-4">
            <!-- Header row -->
            <div class="flex items-start gap-4">
              <!-- SG badge -->
              <div class="w-10 h-10 rounded-lg bg-[#1a5276] flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                SG-{{ v.salary_grade }}
              </div>

              <!-- Meta -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <h3 class="text-sm font-semibold text-gray-900">{{ v.position_title }}</h3>
                  <span :class="v.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                    class="px-2 py-0.5 rounded-full text-[10px] font-medium capitalize">{{ v.status }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ v.place_of_assignment }} &middot; {{ v.plantilla_no ?? '—' }} &middot; Published {{ formatDate(v.published_at) }}</p>
              </div>

              <!-- Stage badge -->
              <div v-if="stages[v.id]" class="flex-shrink-0 hidden sm:block">
                <span :class="currentStageBadge(stages[v.id]).class"
                  class="inline-flex items-center gap-1.5 text-[10px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                  <span class="w-1.5 h-1.5 rounded-full inline-block" :class="currentStageBadge(stages[v.id]).dot"></span>
                  {{ currentStageBadge(stages[v.id]).label }}
                </span>
              </div>

              <!-- Actions dropdown -->
              <div class="relative flex-shrink-0 flex items-center gap-2" v-if="stages[v.id]">
                <a :href="`/hrmpsb/applicants/${v.id}`"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors border border-[#1a5276] text-[#1a5276] hover:bg-[#1a5276]/5">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Applicants & Docs
                </a>
                <button @click.stop="toggleOpen(v.id)"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors bg-[#1a5276] text-white hover:bg-[#154360]">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                  </svg>
                  Phase Actions
                  <svg class="w-3 h-3 transition-transform" :class="openDropdown === v.id ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                  </svg>
                </button>

                <!-- Dropdown menu -->
                <div v-if="openDropdown === v.id"
                  class="absolute right-0 top-full mt-1 w-56 bg-white rounded-xl border border-gray-200 shadow-lg z-50 py-1 max-h-80 overflow-y-auto">
                  <div v-for="group in actionGroups(v.id)" :key="group.label">
                    <p class="px-4 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ group.label }}</p>
                    <a v-for="item in group.items" :key="item.href"
                      :href="item.href"
                      :class="[
                        'flex items-center gap-2.5 w-full px-4 py-2 text-xs transition-colors',
                        item.disabled
                          ? 'text-gray-300 cursor-not-allowed'
                          : 'text-gray-700 hover:bg-gray-50'
                      ]"
                      :title="item.tooltip ?? undefined"
                      @click="item.disabled ? $event.preventDefault() : (openDropdown = null)">
                      <svg v-if="item.icon" class="w-3.5 h-3.5 flex-shrink-0" :class="item.iconCls ?? 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
                      </svg>
                      <span>{{ item.label }}</span>
                      <span v-if="item.badge" class="ml-auto text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                        :class="item.badgeCls ?? 'bg-gray-100 text-gray-500'">{{ item.badge }}</span>
                    </a>
                  </div>

                </div>
              </div>

              <!-- Stages not loaded — placeholder -->
              <div v-else class="w-20 h-8 bg-gray-100 rounded-lg animate-pulse flex-shrink-0"></div>
            </div>

            <!-- Phase meter -->
            <div v-if="stages[v.id]" class="mt-3">
              <div class="flex items-center gap-0.5 mb-1.5">
                <div v-for="(step, idx) in steps" :key="step.key"
                  @click="scrollToPhase(v.id, step.key)"
                  class="flex-1 h-1.5 rounded-full transition-colors cursor-pointer"
                  :class="phaseMeterClass(step, idx, v.id)"
                  :title="step.label">
                </div>
              </div>
              <div class="flex items-center gap-0.5 text-[9px] text-gray-400 font-medium">
                <span v-for="(step, idx) in steps" :key="step.key"
                  class="flex-1 truncate text-center"
                  :class="phaseLabelClass(step, idx, v.id)">
                  {{ step.label }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'

/* ── State ───────────────────────────────────────────────────────────────── */
const loading     = ref(true)
const myRole      = ref(null)
const roleLabels  = ref({})
const authUser    = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
const vacancies   = ref([])
const stages      = ref({})
const autoRefresh = ref(false)
const openDropdown = ref(null)
let refreshInterval = null

/* ── Phase definitions (shared by meter & dropdowns) ──────────────────────── */
const steps = [
  { key: 'pre_assessment', label: 'Pre-Assessment', phases: ['pre_assessment'] },
  { key: 'qs',             label: 'QS Screening',   phases: ['qs'] },
  { key: 'twe',            label: 'TWE',             phases: ['twe'] },
  { key: 'cbwe',           label: 'CBWE',            phases: ['cbwe'] },
  { key: 'bei',            label: 'BEI',             phases: ['bei'] },
  { key: 'eopt',           label: 'EOPT',            phases: ['eopt'] },
  { key: 'background',     label: 'Background Check',phases: ['background'] },
  { key: 'deliberation',   label: 'Deliberation',    phases: ['deliberation'] },
  { key: 'appointing_authority', label: 'Appointing Authority', phases: ['appointing_authority'] },
]

/* ── Computed ────────────────────────────────────────────────────────────── */
const initials = computed(() => {
  const name = myRole.value?.user?.name ?? authUser?.name ?? ''
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() || '?'
})

const isChairOrSecretary = computed(() => {
  if (authUser.role === 'admin') return true
  return myRole.value && ['chairperson', 'secretariat', 'appointing-authority'].includes(myRole.value.hrmpsb_role)
})

const canSchedule = computed(() => {
  if (authUser.role === 'admin') return true
  return myRole.value && ['secretariat', 'hr-chief'].includes(myRole.value.hrmpsb_role)
})

const pendingActions = computed(() => {
  return vacancies.value.filter(v => {
    const s = stages.value[v.id]
    if (!s) return false
    if (!s.pre_assessment_exists) return true
    if (!s.qs_locked) return true
    if (s.qs_locked && !s.twe_exists) return true
    if (s.twe_exists && !s.cbwe_exists) return true
    if (s.cbwe_exists && !s.bei_locked) return true
    if (s.bei_locked && !s.eopt_exists) return true
    if (s.eopt_exists && !s.background_check_locked) return true
    if (s.deliberation_exists && !s.appointing_authority_exists) return true
    return false
  }).length
})

/* ── Phase meter helpers ──────────────────────────────────────────────────── */
function phaseComplete(step, s) {
  const map = {
    pre_assessment: s.pre_assessment_exists,
    qs:             s.qs_locked,
    twe:            s.twe_exists,
    cbwe:           s.cbwe_exists,
    bei:            s.bei_locked,
    eopt:           s.eopt_exists,
    background:     s.background_check_locked,
    deliberation:   s.deliberation_exists,
    appointing_authority: s.appointing_authority_exists,
  }
  return map[step.key] ?? false
}

function lastCompletedIdx(vacancyId) {
  const s = stages.value[vacancyId] ?? {}
  for (let i = steps.length - 1; i >= 0; i--) {
    if (phaseComplete(steps[i], s)) return i
  }
  return -1
}

function phaseMeterClass(step, idx, vacancyId) {
  const last = lastCompletedIdx(vacancyId)
  if (idx < last) return 'bg-green-400'
  if (idx === last) return 'bg-[#1a5276]'
  return 'bg-gray-200'
}

function phaseLabelClass(step, idx, vacancyId) {
  const last = lastCompletedIdx(vacancyId)
  if (idx === last) return 'text-[#1a5276] font-semibold'
  if (idx < last) return 'text-green-600'
  return 'text-gray-300'
}

/* ── Action groups for the dropdown (phase-aware) ────────────────────────── */
function actionGroups(vacancyId) {
  const s = stages.value[vacancyId] ?? {}
  const v = vacancies.value.find(x => x.id === vacancyId)
  const groups = []

  // Pre-Assessment
  groups.push({
    label: 'Screening',
    items: [
      {
        label: 'Pre-Assessment',
        href: `/hrmpsb/pre-assessment/${vacancyId}`,
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        badge: !s.pre_assessment_exists ? 'Start' : 'View',
        badgeCls: !s.pre_assessment_exists ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700',
      },
      {
        label: 'QS Evaluation',
        href: `/hrmpsb/qs-evaluation/${vacancyId}`,
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        badge: s.qs_exists ? (s.qs_locked ? 'Locked' : 'Edit') : 'Start',
        badgeCls: s.qs_locked ? 'bg-blue-100 text-blue-700' : s.qs_exists ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500',
      },
      {
        label: 'QS Results',
        href: `/hrmpsb/qs-results/${vacancyId}`,
        disabled: !s.qs_exists,
        tooltip: !s.qs_exists ? 'No QS evaluations yet' : null,
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        iconCls: 'text-gray-400',
      },
    ],
  })

  // TWE
  const tweItems = []
  if (canSchedule.value) {
    tweItems.push({
      label: 'Schedule TWE',
      href: `/hrmpsb/exam-schedule/${vacancyId}?exam_type=TWE`,
      disabled: !s.qs_locked,
      tooltip: !s.qs_locked ? 'QS must be locked first' : null,
      icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
      badge: s.twe_scheduled ? 'Scheduled' : null,
      badgeCls: 'bg-cyan-100 text-cyan-700',
      iconCls: s.qs_locked ? 'text-gray-400' : 'text-gray-300',
    })
  }
  tweItems.push({
    label: 'TWE Results',
    href: `/hrmpsb/exam-results/${vacancyId}?exam_type=TWE`,
    disabled: !s.qs_locked,
    tooltip: !s.qs_locked ? 'QS must be locked first' : null,
    icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    badge: s.twe_exists ? 'Done' : null,
    badgeCls: 'bg-green-100 text-green-700',
    iconCls: s.qs_locked ? 'text-gray-400' : 'text-gray-300',
  })
  groups.push({ label: 'TWE (Written Exam)', items: tweItems })

  // CBWE
  const cbweItems = []
  if (canSchedule.value) {
    cbweItems.push({
      label: 'Schedule CBWE',
      href: `/hrmpsb/exam-schedule/${vacancyId}?exam_type=CBWE`,
      disabled: !s.twe_exists,
      tooltip: !s.twe_exists ? 'TWE must be completed first' : null,
      icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
      badge: s.cbwe_scheduled ? 'Scheduled' : null,
      badgeCls: 'bg-amber-100 text-amber-700',
      iconCls: s.twe_exists ? 'text-gray-400' : 'text-gray-300',
    })
  }
  cbweItems.push({
    label: 'CBWE Results',
    href: `/hrmpsb/exam-results/${vacancyId}?exam_type=CBWE`,
    disabled: !s.twe_exists,
    tooltip: !s.twe_exists ? 'TWE must be completed first' : null,
    icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    badge: s.cbwe_exists ? 'Done' : null,
    badgeCls: 'bg-green-100 text-green-700',
    iconCls: s.twe_exists ? 'text-gray-400' : 'text-gray-300',
  })
  groups.push({ label: 'CBWE (Written Exam)', items: cbweItems })

  // BEI
  const beiItems = []
  if (canSchedule.value) {
    beiItems.push({
      label: 'Schedule BEI',
      href: `/hrmpsb/bei-schedule/${vacancyId}`,
      disabled: !s.cbwe_exists,
      tooltip: !s.cbwe_exists ? 'CBWE must be completed first' : null,
      icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
      badge: s.bei_scheduled ? 'Scheduled' : null,
      badgeCls: 'bg-sky-100 text-sky-700',
      iconCls: s.cbwe_exists ? 'text-gray-400' : 'text-gray-300',
    })
  }
  beiItems.push({
    label: 'BEI Rating',
    href: `/hrmpsb/bei-rating/${vacancyId}`,
    disabled: !s.qs_locked || !s.cbwe_exists,
    tooltip: !s.qs_locked ? 'QS must be locked first' : !s.cbwe_exists ? 'CBWE must be completed first' : null,
    icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
    badge: s.bei_locked ? 'Locked' : s.bei_exists ? 'In Progress' : null,
    badgeCls: s.bei_locked ? 'bg-blue-100 text-blue-700' : s.bei_exists ? 'bg-amber-100 text-amber-700' : undefined,
    iconCls: s.cbwe_exists ? 'text-gray-400' : 'text-gray-300',
  })
  groups.push({ label: 'Interview', items: beiItems })

  // EOPT
  groups.push({
    label: 'Post-Interview',
    items: [
      {
        label: 'EOPT Assessment',
        href: `/hrmpsb/eopt/${vacancyId}`,
        disabled: !s.bei_locked,
        tooltip: !s.bei_locked ? 'BEI ratings must be locked first' : null,
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        badge: s.eopt_exists ? 'Done' : null,
        badgeCls: 'bg-green-100 text-green-700',
        iconCls: s.bei_locked ? 'text-gray-400' : 'text-gray-300',
      },
      {
        label: 'Background Investigation',
        href: `/hrmpsb/background-check/${vacancyId}`,
        disabled: !s.eopt_exists,
        tooltip: !s.eopt_exists ? 'EOPT must be completed first' : null,
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        badge: s.background_check_locked ? 'Done' : s.background_check_exists ? 'In Progress' : null,
        badgeCls: s.background_check_locked ? 'bg-green-100 text-green-700' : s.background_check_exists ? 'bg-amber-100 text-amber-700' : undefined,
        iconCls: s.eopt_exists ? 'text-gray-400' : 'text-gray-300',
      },
    ],
  })

  // Deliberation
  if (isChairOrSecretary.value) {
    groups.push({
      label: 'Decision',
      items: [
        {
          label: 'Deliberation',
          href: `/hrmpsb/deliberation/${vacancyId}`,
          disabled: !s.background_check_locked,
          tooltip: !s.background_check_locked ? 'Background investigation must be completed first' : null,
          icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
          badge: s.deliberation_exists ? 'Done' : null,
          badgeCls: 'bg-green-100 text-green-700',
          iconCls: s.background_check_locked ? 'text-gray-400' : 'text-gray-300',
        },
      ],
    })
  }

  // Appointing Authority
  if (isChairOrSecretary.value) {
    groups.push({
      label: 'Final Decision',
      items: [
        {
          label: 'Appointing Authority',
          href: `/hrmpsb/appointing-authority/${vacancyId}`,
          disabled: !s.deliberation_exists,
          tooltip: !s.deliberation_exists ? 'Deliberation must be completed first' : null,
          icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
          badge: s.appointing_authority_exists ? 'Done' : null,
          badgeCls: 'bg-green-100 text-green-700',
          iconCls: s.deliberation_exists ? 'text-gray-400' : 'text-gray-300',
        },
      ],
    })
  }

  return groups
}

/* ── Dropdown toggle ──────────────────────────────────────────────────────── */
function toggleOpen(id) {
  openDropdown.value = openDropdown.value === id ? null : id
}

function scrollToPhase(vacancyId, key) {
  const s = stages.value[vacancyId]
  if (!s) return
  const isComplete = {
    pre_assessment: s.pre_assessment_exists,
    qs: s.qs_locked,
    twe: s.twe_exists,
    cbwe: s.cbwe_exists,
    bei: s.bei_locked,
    eopt: s.eopt_exists,
    background: s.background_check_locked,
    deliberation: s.deliberation_exists,
  }[key]
  if (!isComplete) {
    const hrefs = {
      pre_assessment: `/hrmpsb/pre-assessment/${vacancyId}`,
      qs: `/hrmpsb/qs-evaluation/${vacancyId}`,
      twe: `/hrmpsb/exam-schedule/${vacancyId}?exam_type=TWE`,
      cbwe: `/hrmpsb/exam-schedule/${vacancyId}?exam_type=CBWE`,
      bei: `/hrmpsb/bei-rating/${vacancyId}`,
      eopt: `/hrmpsb/eopt/${vacancyId}`,
      background: `/hrmpsb/background-check/${vacancyId}`,
      deliberation: `/hrmpsb/deliberation/${vacancyId}`,
      appointing_authority: `/hrmpsb/appointing-authority/${vacancyId}`,
    }
    if (hrefs[key]) window.location.href = hrefs[key]
  }
}

/* ── Helpers ─────────────────────────────────────────────────────────────── */
function roleLabel(key) {
  return roleLabels.value[key] ?? key
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function currentStageBadge(s) {
  if (s.appointing_authority_exists) return { label: 'Awaiting Appointing Authority', class: 'bg-rose-100 text-rose-700',    dot: 'bg-rose-500' }
  if (s.deliberation_exists)       return { label: 'Deliberation Done',           class: 'bg-indigo-100 text-indigo-700', dot: 'bg-indigo-500' }
  if (s.background_check_locked)   return { label: 'Awaiting Deliberation',       class: 'bg-violet-100 text-violet-700', dot: 'bg-violet-500' }
  if (s.background_check_exists)   return { label: 'BG Check In Progress',       class: 'bg-purple-100 text-purple-700', dot: 'bg-purple-500 animate-pulse' }
  if (s.eopt_exists)               return { label: 'EOPT Rated',                  class: 'bg-emerald-100 text-emerald-700', dot: 'bg-emerald-500' }
  if (s.bei_locked)                return { label: 'Awaiting EOPT',               class: 'bg-fuchsia-100 text-fuchsia-700', dot: 'bg-fuchsia-500' }
  if (s.bei_exists)                return { label: 'BEI In Progress',             class: 'bg-blue-100 text-blue-700',    dot: 'bg-blue-500 animate-pulse' }
  if (s.bei_scheduled)             return { label: 'BEI Scheduled',               class: 'bg-sky-100 text-sky-700',      dot: 'bg-sky-500' }
  if (s.cbwe_exists)               return { label: 'CBWE Completed',              class: 'bg-orange-100 text-orange-700',dot: 'bg-orange-500' }
  if (s.cbwe_scheduled)            return { label: 'CBWE Scheduled',              class: 'bg-amber-100 text-amber-700',  dot: 'bg-amber-500' }
  if (s.twe_exists)                return { label: 'TWE Completed',               class: 'bg-teal-100 text-teal-700',    dot: 'bg-teal-500' }
  if (s.twe_scheduled)             return { label: 'TWE Scheduled',               class: 'bg-cyan-100 text-cyan-700',    dot: 'bg-cyan-500' }
  if (s.qs_locked)                 return { label: 'QS Locked',                   class: 'bg-amber-100 text-amber-700',  dot: 'bg-amber-500' }
  if (s.qs_exists)                 return { label: 'QS In Progress',              class: 'bg-yellow-100 text-yellow-700',dot: 'bg-yellow-500 animate-pulse' }
  if (s.pre_assessment_exists)     return { label: 'Pre-Assessed',                class: 'bg-slate-100 text-slate-700',  dot: 'bg-slate-500' }
  return                                  { label: 'Not Started',                  class: 'bg-gray-100 text-gray-500',    dot: 'bg-gray-400' }
}

/* ── Auto-refresh ─────────────────────────────────────────────────────────── */
watch(autoRefresh, (val) => {
  if (val) {
    refreshInterval = setInterval(loadData, 30000)
  } else {
    clearInterval(refreshInterval)
  }
})

onBeforeUnmount(() => {
  clearInterval(refreshInterval)
  document.removeEventListener('click', handleOutsideClick)
})

/* ── Click outside to close dropdown ──────────────────────────────────────── */
function handleOutsideClick(e) {
  if (openDropdown.value && !e.target.closest('.relative')) {
    openDropdown.value = null
  }
}

onMounted(() => {
  document.addEventListener('click', handleOutsideClick)
})

/* ── Data loading ────────────────────────────────────────────────────────── */
async function loadData(initial = false) {
  if (initial) loading.value = true
  try {
    const [roleRes, vacRes] = await Promise.all([
      axios.get('/api/hrmpsb/my-role', { headers: authHeaders() }),
      axios.get('/api/vacancies', {
        headers: authHeaders(),
        params: { status: 'published,closed', per_page: 100 },
      }),
    ])

    myRole.value     = roleRes.data.composition
    roleLabels.value = roleRes.data.roles ?? {}
    vacancies.value  = vacRes.data.data ?? []

    if (vacancies.value.length) {
      const ids = vacancies.value.map(v => v.id)
      const params = new URLSearchParams()
      ids.forEach(id => params.append('vacancy_ids[]', id))
      const { data } = await axios.get(`/api/hrmpsb/pipeline-stages?${params}`, { headers: authHeaders() })
      stages.value = data
    }
  } catch (e) {
    console.error('Failed to load HRMPSB dashboard data', e)
  } finally {
    if (initial) loading.value = false
  }
}

onMounted(() => loadData(true))
</script>
