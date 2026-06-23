<template>
  <HrmbsboardLayout title="Dashboard">
    <div class="max-w-5xl mx-auto">

      <!-- Board role banner -->
      <div v-if="myRole" class="mb-6 bg-[#1a5276]/5 border border-[#1a5276]/20 rounded-xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-[#1a5276] flex items-center justify-center text-white font-bold text-base flex-shrink-0">
          {{ initials }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs text-gray-400 font-medium">Your Board Role</p>
          <p class="text-sm font-semibold text-gray-900">{{ roleLabel(myRole.hrmpsb_role) }}</p>
          <p class="text-xs text-gray-500 capitalize mt-0.5">{{ myRole.member_type }} member</p>
        </div>
<<<<<<< HEAD
        <span v-if="pendingActions > 0"
          class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
          {{ pendingActions }} action{{ pendingActions !== 1 ? 's' : '' }} pending
        </span>
        <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
=======
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
          <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse block"></span>
          Active
        </span>
      </div>

      <!-- No board role notice -->
      <div v-else-if="!loading"
        class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
          <p class="text-sm font-semibold text-amber-800">No active board role assigned</p>
          <p class="text-xs text-amber-700 mt-0.5">Contact the administrator to assign you to the HRMPSB composition.</p>
        </div>
      </div>

      <!-- Section header -->
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-sm font-semibold text-gray-900">Vacancies for Evaluation</h2>
          <p class="text-xs text-gray-400 mt-0.5">Published and closed positions requiring HRMPSB action</p>
        </div>
        <span v-if="!loading" class="text-xs text-gray-400 font-medium">
          {{ vacancies.length }} position{{ vacancies.length !== 1 ? 's' : '' }}
        </span>
      </div>

      <!-- Loading skeletons -->
<<<<<<< HEAD
      <div v-if="loading" class="space-y-4">
        <div v-for="n in 3" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
          <div class="h-3 bg-gray-100 rounded w-1/3 mb-4"></div>
          <div class="flex gap-2">
            <div v-for="i in 5" :key="i" class="h-8 flex-1 bg-gray-100 rounded-lg"></div>
=======
      <div v-if="loading" class="space-y-3">
        <div v-for="n in 3" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
              <div class="h-3 bg-gray-100 rounded w-1/3"></div>
            </div>
            <div class="flex gap-2">
              <div v-for="i in 3" :key="i" class="h-8 w-24 bg-gray-100 rounded-lg"></div>
            </div>
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
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
<<<<<<< HEAD
      <div v-else class="space-y-4">
        <div v-for="v in vacancies" :key="v.id"
          class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

          <!-- Card header -->
          <div class="px-5 pt-5 pb-4">
            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-[#1a5276] text-white">
                    SG-{{ v.salary_grade }}
                  </span>
                  <span :class="v.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium capitalize">
                    {{ v.status }}
                  </span>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 leading-snug">{{ v.position_title }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ v.place_of_assignment }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Deadline: {{ formatDate(v.deadline_at) }}</p>
              </div>

              <!-- Current stage badge -->
              <div v-if="stages[v.id]" class="flex-shrink-0">
                <span :class="currentStageBadge(stages[v.id]).class"
                  class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full">
                  <span class="w-1.5 h-1.5 rounded-full inline-block" :class="currentStageBadge(stages[v.id]).dot"></span>
                  {{ currentStageBadge(stages[v.id]).label }}
                </span>
              </div>
            </div>
          </div>

          <!-- Process Stepper -->
          <div v-if="stages[v.id]" class="px-5 pb-3">
            <div class="flex items-center gap-0 text-xs overflow-x-auto">
              <template v-for="(step, idx) in getSteps(v.id)" :key="step.key">
                <div class="flex flex-col items-center flex-shrink-0 min-w-[60px]">
                  <div :class="[
                    'w-7 h-7 rounded-full flex items-center justify-center font-bold text-[10px] flex-shrink-0',
                    step.done   ? 'bg-green-500 text-white' :
                    step.active ? 'bg-[#1a5276] text-white ring-2 ring-[#1a5276]/30' :
                                  'bg-gray-100 text-gray-400'
                  ]">
                    <svg v-if="step.done" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span v-else>{{ idx + 1 }}</span>
                  </div>
                  <span class="text-center mt-1 leading-tight text-[10px]"
                    :class="step.done ? 'text-green-600 font-medium' : step.active ? 'text-[#1a5276] font-semibold' : 'text-gray-400'">
                    {{ step.label }}
                  </span>
                </div>
                <!-- Connector line -->
                <div v-if="idx < getSteps(v.id).length - 1"
                  class="flex-1 h-px mx-1 flex-shrink min-w-[8px]"
                  :class="step.done ? 'bg-green-400' : 'bg-gray-200'">
                </div>
              </template>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="px-5 pb-5 flex flex-wrap gap-2">
            <template v-if="stages[v.id]">
              <!-- Pre-Assessment Matrix — viewable by all members; editable by secretariat -->
              <NavBtn :href="`/hrmpsb/pre-assessment/${v.id}`"
                variant="outline"
                icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                Pre-Assessment
              </NavBtn>

              <!-- QS Evaluation — always accessible if there are applications -->
              <NavBtn :href="`/hrmpsb/qs-evaluation/${v.id}`" variant="primary" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                QS Evaluation
              </NavBtn>

              <!-- QS Results — after at least one QS exists -->
              <NavBtn :href="`/hrmpsb/qs-results/${v.id}`"
                :disabled="!stages[v.id].qs_exists"
                :tooltip="!stages[v.id].qs_exists ? 'No QS evaluations submitted yet' : null"
                icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                QS Results
              </NavBtn>

              <!-- Exam Scheduler — after QS is locked -->
              <NavBtn :href="`/hrmpsb/exam-schedule/${v.id}`"
                :disabled="!stages[v.id].qs_locked"
                :tooltip="!stages[v.id].qs_locked ? 'QS must be locked before scheduling exams' : null"
                variant="outline"
                icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                Exam Scheduler
              </NavBtn>

              <!-- Exam Results — after QS is locked -->
              <NavBtn :href="`/hrmpsb/exam-results/${v.id}`"
                :disabled="!stages[v.id].qs_locked"
                :tooltip="!stages[v.id].qs_locked ? 'QS must be locked before encoding exam results' : null"
                icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                Exam Results
              </NavBtn>

              <!-- BEI Scheduler — after exam scores exist -->
              <NavBtn :href="`/hrmpsb/bei-schedule/${v.id}`"
                :disabled="!stages[v.id].exam_exists"
                :tooltip="!stages[v.id].exam_exists ? 'Exam scores must be encoded before scheduling BEI' : null"
                variant="outline"
                icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                BEI Scheduler
              </NavBtn>

              <!-- BEI Rating — after QS locked and exam scores exist -->
              <NavBtn :href="`/hrmpsb/bei-rating/${v.id}`"
                :disabled="!stages[v.id].qs_locked || !stages[v.id].exam_exists"
                :tooltip="!stages[v.id].qs_locked ? 'QS must be locked first'
                  : !stages[v.id].exam_exists ? 'Exam scores must be encoded before BEI'
                  : null"
                icon="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                BEI Rating
              </NavBtn>

              <!-- Deliberation — chair/secretariat only, after BEI is locked -->
              <NavBtn v-if="isChairOrSecretary"
                :href="`/hrmpsb/deliberation/${v.id}`"
                :disabled="!stages[v.id].bei_locked"
                :tooltip="!stages[v.id].bei_locked ? 'BEI ratings must be locked before deliberation' : null"
                variant="outline"
                icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                Deliberation
              </NavBtn>
            </template>

            <!-- Stages not loaded yet -->
            <template v-else>
              <div class="flex gap-2">
                <div v-for="n in 4" :key="n" class="h-8 w-24 bg-gray-100 rounded-lg animate-pulse"></div>
              </div>
            </template>
          </div>

=======
      <div v-else class="space-y-3">
        <div v-for="v in vacancies" :key="v.id"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 transition-shadow hover:shadow-md">

          <div class="flex flex-col sm:flex-row sm:items-start gap-4">

            <!-- Vacancy info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-[#1a5276] text-white">
                  SG-{{ v.salary_grade }}
                </span>
                <span :class="v.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                  class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium capitalize">
                  {{ v.status }}
                </span>
              </div>
              <h3 class="text-sm font-semibold text-gray-900 leading-snug">{{ v.position_title }}</h3>
              <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ v.place_of_assignment }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">Deadline: {{ formatDate(v.deadline_at) }}</p>
            </div>

            <!-- Evaluation action buttons -->
            <div class="flex flex-wrap gap-2 flex-shrink-0">
              <a :href="`/hrmpsb/qs-evaluation/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-[#1a5276] hover:bg-[#154360] text-white rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                QS Evaluation
              </a>
              <a :href="`/hrmpsb/qs-results/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                QS Results
              </a>
              <a :href="`/hrmpsb/exam-results/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Exam Results
              </a>
              <a :href="`/hrmpsb/bei-rating/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                BEI Rating
              </a>
              <a v-if="isChairOrSecretary" :href="`/hrmpsb/deliberation/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold border border-[#1a5276] text-[#1a5276] hover:bg-[#1a5276]/5 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Deliberation
              </a>
            </div>

          </div>
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
        </div>
      </div>

    </div>
  </HrmbsboardLayout>
</template>

<script setup>
<<<<<<< HEAD
import { ref, computed, onMounted, h, resolveComponent } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'

/* ── Inline NavBtn sub-component ─────────────────────────────────────────── */
const NavBtn = {
  props: {
    href:     { type: String, required: true },
    disabled: { type: Boolean, default: false },
    tooltip:  { type: String, default: null },
    variant:  { type: String, default: 'default' },
    icon:     { type: String, required: true },
  },
  setup(props, { slots }) {
    const baseClass = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors relative group'
    const variantClass = computed(() => {
      if (props.disabled) return 'bg-gray-100 text-gray-400 cursor-not-allowed'
      if (props.variant === 'primary') return 'bg-[#1a5276] hover:bg-[#154360] text-white'
      if (props.variant === 'outline') return 'border border-[#1a5276] text-[#1a5276] hover:bg-[#1a5276]/5'
      return 'border border-gray-300 text-gray-700 hover:bg-gray-50'
    })

    return () => h(
      props.disabled ? 'span' : 'a',
      {
        ...(props.disabled ? {} : { href: props.href }),
        class: [baseClass, variantClass.value],
        title: props.tooltip ?? undefined,
      },
      [
        h('svg', { class: 'w-3.5 h-3.5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: props.icon }),
        ]),
        slots.default?.(),
        props.tooltip ? h('span', {
          class: 'absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10',
        }, props.tooltip) : null,
      ]
    )
  },
}

/* ── State ───────────────────────────────────────────────────────────────── */
const loading    = ref(true)
const myRole     = ref(null)
const roleLabels = ref({})
const authUser   = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
const vacancies  = ref([])
const stages     = ref({})

/* ── Computed ────────────────────────────────────────────────────────────── */
=======
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'

const loading   = ref(true)
const myRole    = ref(null)
const roleLabels = ref({})
const authUser  = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
const vacancies = ref([])

// Derive initials from composition user if loaded, else from localStorage
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
const initials = computed(() => {
  const name = myRole.value?.user?.name ?? authUser?.name ?? ''
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() || '?'
})

const isChairOrSecretary = computed(() =>
  myRole.value && ['chairperson', 'secretariat'].includes(myRole.value.hrmpsb_role)
)

<<<<<<< HEAD
const pendingActions = computed(() => {
  return vacancies.value.filter(v => {
    const s = stages.value[v.id]
    if (!s) return false
    if (!s.qs_exists) return true
    if (s.qs_locked && !s.exam_exists) return true
    if (s.exam_exists && s.qs_locked && !s.bei_exists) return true
    return false
  }).length
})

/* ── Helpers ─────────────────────────────────────────────────────────────── */
=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
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

<<<<<<< HEAD
function getSteps(vacancyId) {
  const s = stages.value[vacancyId] ?? {}
  return [
    { key: 'qs',            label: 'QS\nEvaluation',    done: s.qs_locked,            active: s.qs_exists && !s.qs_locked },
    { key: 'exam_sched',    label: 'Exam\nScheduling',   done: s.exam_scheduled,       active: s.qs_locked && !s.exam_scheduled },
    { key: 'exam',          label: 'Exam\nResults',      done: s.exam_exists,          active: s.exam_scheduled && !s.exam_exists },
    { key: 'bei_sched',     label: 'BEI\nScheduling',    done: s.bei_scheduled,        active: s.exam_exists && !s.bei_scheduled },
    { key: 'bei',           label: 'BEI\nRating',        done: s.bei_locked,           active: s.bei_scheduled && !s.bei_locked },
    { key: 'deliberation',  label: 'Deliberation',       done: s.deliberation_exists,  active: s.bei_locked && !s.deliberation_exists },
  ]
}

function currentStageBadge(s) {
  if (s.deliberation_exists) return { label: 'Deliberation Done',    class: 'bg-indigo-100 text-indigo-700', dot: 'bg-indigo-500' }
  if (s.bei_locked)          return { label: 'Awaiting Deliberation', class: 'bg-violet-100 text-violet-700', dot: 'bg-violet-500' }
  if (s.bei_exists)          return { label: 'BEI In Progress',       class: 'bg-blue-100 text-blue-700',    dot: 'bg-blue-500 animate-pulse' }
  if (s.bei_scheduled)       return { label: 'BEI Scheduled',         class: 'bg-sky-100 text-sky-700',      dot: 'bg-sky-500' }
  if (s.exam_exists)         return { label: 'Exam Encoded',          class: 'bg-orange-100 text-orange-700',dot: 'bg-orange-500' }
  if (s.exam_scheduled)      return { label: 'Exam Scheduled',        class: 'bg-teal-100 text-teal-700',    dot: 'bg-teal-500' }
  if (s.qs_locked)           return { label: 'QS Locked',             class: 'bg-amber-100 text-amber-700',  dot: 'bg-amber-500' }
  if (s.qs_exists)           return { label: 'QS In Progress',        class: 'bg-yellow-100 text-yellow-700',dot: 'bg-yellow-500 animate-pulse' }
  return                            { label: 'Not Started',            class: 'bg-gray-100 text-gray-500',    dot: 'bg-gray-400' }
}

/* ── Data loading ────────────────────────────────────────────────────────── */
=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
async function loadData() {
  loading.value = true
  try {
    const [roleRes, vacRes] = await Promise.all([
      axios.get('/api/hrmpsb/my-role', { headers: authHeaders() }),
      axios.get('/api/vacancies', {
        headers: authHeaders(),
        params: { status: 'published,closed', per_page: 100 },
      }),
    ])

<<<<<<< HEAD
    myRole.value     = roleRes.data.composition
=======
    myRole.value    = roleRes.data.composition
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
    roleLabels.value = roleRes.data.roles ?? {}
    vacancies.value  = vacRes.data.data ?? []
  } catch (e) {
    console.error('Failed to load HRMPSB dashboard', e)
  } finally {
    loading.value = false
  }
<<<<<<< HEAD

  // Load pipeline stages for all vacancies in a second request
  if (vacancies.value.length) {
    try {
      const ids = vacancies.value.map(v => v.id)
      const params = new URLSearchParams()
      ids.forEach(id => params.append('vacancy_ids[]', id))
      const { data } = await axios.get(`/api/hrmpsb/pipeline-stages?${params}`, { headers: authHeaders() })
      stages.value = data
    } catch (e) {
      console.error('Failed to load pipeline stages', e)
    }
  }
=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
}

onMounted(loadData)
</script>
