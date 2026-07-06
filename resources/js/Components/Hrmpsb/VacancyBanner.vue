<template>
  <div v-if="loading" class="h-40 bg-white rounded-2xl border border-gray-200 animate-pulse" />

  <div v-else-if="vacancy"
    class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition-all hover:shadow-md hover:border-gray-300">
    <div class="h-1.5 w-full" style="background: linear-gradient(90deg, var(--color-primary) 0%, #2980b9 100%)" />

    <div class="px-6 py-5">

      <!-- Clickable header area — navigates to current stage -->
      <div @click="goToStage(props.stageKey)" class="cursor-pointer transition-colors rounded-lg -mx-1 px-1 hover:bg-gray-50/50">
        <div class="flex items-start justify-between gap-3 flex-wrap">
          <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Recruitment Stage {{ stage }} — {{ stageLabel }}
          </span>
          <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0"
            :class="statusClass">
            <svg class="w-2 h-2" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
            {{ capitalize(vacancy.status) }}
          </span>
        </div>

        <p v-if="currentStageInfo" class="mt-1.5 text-xs text-gray-500 leading-relaxed">
          <span class="font-semibold text-gray-600">{{ currentStageInfo.full }}:</span>
          {{ currentStageInfo.blurb }}
        </p>

        <h1 class="mt-3 text-xl font-bold text-gray-900 leading-snug">{{ vacancy.position_title }}</h1>

        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Plantilla Item No.</p>
            <p class="mt-1 text-sm font-semibold text-gray-800 font-mono truncate">{{ vacancy.plantilla_no ?? '—' }}</p>
          </div>
          <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Salary Grade</p>
            <p class="mt-1 text-sm font-semibold text-gray-800">SG-{{ vacancy.salary_grade }}</p>
          </div>
          <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Publication Date</p>
            <p class="mt-1 text-sm font-semibold text-gray-800">{{ formatDate(vacancy.published_at) }}</p>
          </div>
          <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Place of Assignment</p>
            <p class="mt-1 text-sm font-semibold text-gray-800 truncate">{{ vacancy.place_of_assignment ?? '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Pipeline stepper — skeleton while stage flags are loading -->
      <div v-if="stageKey" class="mt-5 pt-4 border-t border-gray-100 w-full">
        <!-- Shimmer skeleton while flags load -->
        <ol v-if="flagsLoading" class="flex items-start w-full">
          <li v-for="i in 8" :key="i"
            class="flex items-center"
            :class="i < 8 ? 'flex-1' : 'flex-none'">
            <div class="flex flex-col items-center gap-1 flex-shrink-0">
              <span class="w-7 h-7 rounded-full bg-gray-200 animate-pulse" />
              <span class="h-2.5 w-10 rounded bg-gray-200 animate-pulse" />
            </div>
            <span v-if="i < 8" class="flex-1 h-0.5 mx-1.5 mt-3.5 bg-gray-200 animate-pulse" />
          </li>
        </ol>
        <!-- Real stepper once flags are known -->
        <ol v-else class="flex items-start w-full">
          <li v-for="(def, i) in PIPELINE_STAGES" :key="def.key"
            class="flex items-center"
            :class="i < PIPELINE_STAGES.length - 1 ? 'flex-1' : 'flex-none'">
            <div @click.stop="isStageAccessible(i) && goToStage(def.key)"
              class="flex flex-col items-center gap-1 flex-shrink-0 transition-all"
              :class="isStageAccessible(i)
                ? 'cursor-pointer hover:scale-110 active:scale-95'
                : 'cursor-not-allowed opacity-50'"
              :title="isStageAccessible(i) ? def.full : 'Not yet available — earlier stages must be completed first'">
              <span
                class="w-7 h-7 flex items-center justify-center rounded-full text-[11px] font-bold border-2 transition-all relative"
                :class="stepClass(i)">
                <template v-if="isStageAccessible(i)">{{ i + 1 }}</template>
                <svg v-else class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </span>
              <span class="inline-flex items-center gap-1 text-[9px] sm:text-[10px] font-semibold uppercase tracking-wide text-center leading-tight max-w-[4.5rem]"
                :class="i === currentStageIndex ? 'text-indigo-700' : 'text-gray-400'">
                {{ def.short }}
                <svg v-if="!isStageAccessible(i)" class="w-2.5 h-2.5 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </span>
            </div>
            <span v-if="i < PIPELINE_STAGES.length - 1" class="flex-1 h-0.5 mx-1.5 mt-3.5"
              :class="i < maxReachedIndex ? 'bg-indigo-300' : 'bg-gray-200'"></span>
          </li>
        </ol>
      </div>

      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import api from '@/services/api'

const props = defineProps({
  vacancy:    { type: Object,  default: null },
  stage:      { type: Number,  required: true },
  stageLabel: { type: String,  required: true },
  loading:    { type: Boolean, default: false },
  stageKey:   { type: String,  default: null },
})

const PIPELINE_STAGES = [
  { key: 'pre-assessment', short: 'Pre-Assess', full: 'Pre-Assessment Matrix (PAM)',
    blurb: 'Checks that the applicant\'s submitted documents (PDS, TOR, application letter, etc.) are complete before formal screening begins.' },
  { key: 'qs', short: 'QS', full: 'Qualification Standards (QS) Screening',
    blurb: 'Confirms the applicant meets the position\'s required education, experience, training, and eligibility.' },
  { key: 'twe', short: 'TWE', full: 'Technical/Written Examination (TWE)',
    blurb: 'A written exam measuring job-related knowledge. Applicants must pass this before moving to CBWE.' },
  { key: 'cbwe', short: 'CBWE', full: 'Competency-Based Work Evaluation (CBWE)',
    blurb: 'Board members rate the applicant against the core competencies required for the position.' },
  { key: 'bei', short: 'BEI', full: 'Behavioral Event Interview (BEI)',
    blurb: 'A structured panel interview exploring how the applicant has handled real work situations.' },
  { key: 'eopt', short: 'EOPT', full: 'Ethics-Oriented Personality Test (EOPT)',
    blurb: 'A personality/values assessment used to help gauge fit and integrity.' },
  { key: 'background', short: 'Bkgd Check', full: 'Background Investigation',
    blurb: 'Verifies employment history, education, and character references, including NBI clearance.' },
  { key: 'deliberation', short: 'Deliberation', full: 'Final Deliberation & Selection',
    blurb: 'The HRMPSB reviews all results together and endorses candidates for appointment.' },
]

const PREREQUISITE_FLAGS = {
  'qs':           'pre_assessment_exists',
  'twe':          'qs_locked',
  'cbwe':         'twe_exists',
  'bei':          'cbwe_locked',
  'eopt':         'bei_locked',
  'background':   'eopt_exists',
  'deliberation': 'background_check_locked',
}

const ROUTE_MAP = {
  'pre-assessment': '/hrmpsb/pre-assessment',
  'qs':             '/hrmpsb/qs-evaluation',
  'twe':            '/hrmpsb/exam-schedule',
  'cbwe':           '/hrmpsb/cbwe-rating',
  'bei':            '/hrmpsb/bei-rating',
  'eopt':           '/hrmpsb/eopt',
  'background':     '/hrmpsb/background-check',
  'deliberation':   '/hrmpsb/deliberation',
}

const pipelineFlags = ref(null)
const flagsLoading = ref(false)

async function fetchPipelineFlags() {
  if (!props.vacancy?.id || !props.stageKey) return
  flagsLoading.value = true
  try {
    const res = await api.get('/hrmpsb/pipeline-stages', {
      params: { vacancy_ids: [props.vacancy.id] },
    })
    pipelineFlags.value = res.data[props.vacancy.id] ?? null
  } catch {
    pipelineFlags.value = null
  } finally {
    flagsLoading.value = false
  }
}

watch(() => props.vacancy?.id, fetchPipelineFlags)
watch(() => props.stageKey, fetchPipelineFlags)
onMounted(fetchPipelineFlags)

const currentStageIndex = computed(() => PIPELINE_STAGES.findIndex(d => d.key === props.stageKey))
const currentStageInfo = computed(() => PIPELINE_STAGES[currentStageIndex.value] ?? null)

const maxReachedIndex = computed(() => {
  const flags = pipelineFlags.value
  if (!flags) return currentStageIndex.value

  let max = 0
  for (let i = 1; i < PIPELINE_STAGES.length; i++) {
    const flagName = PREREQUISITE_FLAGS[PIPELINE_STAGES[i].key]
    if (flags[flagName]) {
      max = i
    } else {
      break
    }
  }
  return max
})

function stepClass(i) {
  if (i === currentStageIndex.value) return 'bg-indigo-600 border-indigo-600 text-white'
  if (i <= maxReachedIndex.value) return 'bg-indigo-50 border-indigo-300 text-indigo-600'
  return 'bg-white border-gray-200 text-gray-400'
}

function isStageAccessible(i) {
  return i <= maxReachedIndex.value
}

const statusClass = computed(() => {
  const map = {
    published: 'bg-green-50 text-green-700 border border-green-200',
    closed:    'bg-gray-100 text-gray-600 border border-gray-200',
    filled:    'bg-blue-50 text-blue-700 border border-blue-200',
    draft:     'bg-amber-50 text-amber-700 border border-amber-200',
  }
  return map[props.vacancy?.status] ?? 'bg-gray-100 text-gray-500 border border-gray-200'
})

function capitalize(str) {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' })
}

function goToStage(key) {
  if (!props.vacancy?.id) return
  const path = ROUTE_MAP[key]
  if (path) {
    router.visit(`${path}/${props.vacancy.id}`)
  }
}
</script>
