<template>
  <HrmbsboardLayout title="Examination Results" :vacancyId="props.vacancyId">
    <div class="space-y-6 pb-20 sm:pb-6">

      <!-- ── Vacancy Banner ────────────────────────────────────────────── -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="stageNumber"
        :stageLabel="stageLabel"
        :loading="loading">
        <!-- Exam type tabs -->
        <div class="mt-4 flex items-center gap-1">
          <a :href="`/hrmpsb/exam-results/${props.vacancyId}?exam_type=TWE`"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors"
            :class="examType === 'TWE' ? 'bg-[#2a338f] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            TWE — Technical Written Exam
          </a>

        </div>
        <!-- Passing threshold note -->
        <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
          <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Passing threshold is
          <strong class="text-amber-600 font-bold">{{ passingThreshold }}%</strong>.
        </div>
      </VacancyBanner>

      <!-- ── Summary Cards ─────────────────────────────────────────────── -->
      <div v-if="!loading && applications.length" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-gray-900">{{ applications.length }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Qualified Applicants</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Proceeded to Exam Stage</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-green-700">{{ passedCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Passed {{ examType }}</p>
          <p class="text-[10px] text-gray-400 mt-0.5">≥ {{ passingThreshold }}%</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-red-500">{{ failedCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Failed {{ examType }}</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Below threshold</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-amber-500">{{ pendingCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Pending Encoding</p>
          <p class="text-[10px] text-gray-400 mt-0.5">No scores recorded yet</p>
        </div>
      </div>

      <!-- ── Encode Panel (secretariat only) ──────────────────────────── -->
      <div v-if="canEncode" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Panel header with toggle -->
        <button
          @click="encodeOpen = !encodeOpen"
          class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-[#2a338f] flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            <div class="text-left">
              <p class="text-sm font-semibold text-gray-900">Encode Examination Score</p>
              <p class="text-xs text-gray-400 mt-0.5">HRMPSB Member — enter raw score and max items for TWE</p>
            </div>
          </div>
          <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0"
            :class="encodeOpen ? 'rotate-180' : ''"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
          </svg>
        </button>

        <div v-show="encodeOpen" class="border-t border-gray-100 px-6 pb-6 pt-5">
          <form @submit.prevent="submitScore">
            <!-- Row 1 -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
              <div>
                <label class="field-label">Applicant (Blind Code)</label>
                <select v-model="form.application_id" class="input" required>
                  <option value="">— Select blind code —</option>
                  <option v-for="app in applications" :key="app.id" :value="app.id">
                    {{ appLabel(app) }}
                  </option>
                </select>
              </div>
              <div>
                <label class="field-label">Examination Type</label>
                <select v-model="form.exam_type" class="input" required>
                  <option value="TWE">TWE — Technical Written Examination</option>
                </select>
              </div>
              <div>
                <label class="field-label">No. of Items / Max Score</label>
                <input v-model.number="form.max_score" type="number" step="1" min="1" max="100" class="input" required />
              </div>
            </div>

            <!-- Row 2 -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
              <div>
                <label class="field-label">Raw Score</label>
                <input
                  v-model.number="form.raw_score"
                  type="number" step="0.01" min="0"
                  :max="form.max_score"
                  class="input"
                  :class="scoreError ? 'border-red-400 bg-red-50 focus:ring-red-400' : ''"
                  required />
                <p v-if="scoreError" class="text-xs text-red-500 mt-1">{{ scoreError }}</p>
              </div>

              <!-- Live preview -->
              <div class="sm:col-span-2">
                <div v-if="form.raw_score !== '' && form.max_score"
                  class="flex items-center gap-3 px-4 py-3 rounded-xl border"
                  :class="Number(previewPct) >= passingThreshold
                    ? 'bg-green-50 border-green-200'
                    : 'bg-red-50 border-red-200'">
                  <div>
                    <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider">Score Preview</p>
                    <p class="text-2xl font-bold mt-0.5"
                      :class="Number(previewPct) >= passingThreshold ? 'text-green-700' : 'text-red-600'">
                      {{ previewPct }}%
                    </p>
                  </div>
                  <div class="ml-auto">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold"
                      :class="Number(previewPct) >= passingThreshold
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-700'">
                      {{ Number(previewPct) >= passingThreshold ? '✓ PASS' : '✗ FAIL' }}
                    </span>
                  </div>
                </div>
                <div v-else class="h-[66px] rounded-xl border border-dashed border-gray-200 flex items-center justify-center">
                  <p class="text-xs text-gray-400">Enter raw score to preview result</p>
                </div>
              </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <p v-if="encodeError" class="text-sm text-red-600">{{ encodeError }}</p>
              <div v-else></div>
              <button type="submit" :disabled="submitting || !!scoreError" class="btn-primary">
                <svg v-if="submitting" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ submitting ? 'Saving…' : 'Save Examination Score' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── Results Table ─────────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Examination Results — Ranked List</h2>
            <p class="text-xs text-gray-400 mt-0.5">Sorted by highest exam percentage (descending)</p>
          </div>
          <div class="flex items-center gap-2 text-xs text-gray-500">
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>Passed
            </span>
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>Failed
            </span>
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>Pending
            </span>
          </div>
        </div>

        <div v-if="loading" class="divide-y divide-gray-100">
          <div v-for="n in 5" :key="n" class="px-6 py-4 flex items-center gap-4">
            <div class="w-8 h-8 rounded-full bg-gray-100 animate-pulse flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-100 rounded w-1/4 animate-pulse"></div>
              <div class="h-3 bg-gray-50 rounded w-1/3 animate-pulse"></div>
            </div>
            <div class="w-20 h-8 bg-gray-100 rounded animate-pulse"></div>
            <div class="w-20 h-8 bg-gray-100 rounded animate-pulse"></div>
            <div class="w-16 h-6 bg-gray-100 rounded-full animate-pulse"></div>
          </div>
        </div>

        <div v-else-if="!applications.length"
          class="flex flex-col items-center justify-center py-16 text-center">
          <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-3">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-500">No applicants in the examination stage</p>
          <p class="text-xs text-gray-400 mt-1">QS results must be locked before applicants appear here.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/70">
                <th class="pl-6 pr-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider w-14">Rank</th>
                <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Blind Code / Applicant</th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                  <div>{{ examType }}</div>
                  <div class="font-normal normal-case text-gray-300">{{ examType === 'TWE' ? 'Technical Written Exam' : 'Competency-Based Written Exam' }}</div>
                </th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Score %</th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Result</th>
                <th v-if="canEncode" class="pl-3 pr-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="(app, idx) in applications" :key="app.id"
                class="group transition-colors"
                :class="{
                  'bg-green-50/30 hover:bg-green-50/60': app.overall_passed && app.exam_results.length,
                  'bg-red-50/20 hover:bg-red-50/40':    !app.overall_passed && app.exam_results.length,
                  'hover:bg-gray-50':                     !app.exam_results.length,
                }">

                <!-- Rank -->
                <td class="pl-6 pr-3 py-4 text-center">
                  <div v-if="app.exam_results.length" class="flex items-center justify-center">
                    <span v-if="idx === 0"
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-amber-100 text-amber-700 ring-2 ring-amber-300">
                      1
                    </span>
                    <span v-else-if="idx === 1"
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 text-gray-600 ring-2 ring-gray-300">
                      2
                    </span>
                    <span v-else-if="idx === 2"
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-orange-100 text-orange-700 ring-2 ring-orange-300">
                      3
                    </span>
                    <span v-else
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold text-gray-400 bg-gray-50">
                      {{ idx + 1 }}
                    </span>
                  </div>
                  <span v-else class="text-gray-300 text-xs">—</span>
                </td>

                <!-- Blind Code / Name -->
                <td class="px-3 py-4">
                  <div>
                    <p v-if="app.unmasked" class="text-sm font-semibold text-gray-900">{{ app.name }}</p>
                    <p v-else class="text-sm font-bold font-mono text-[#2a338f]">{{ app.token ?? '—' }}</p>
                    <span class="inline-flex items-center gap-1 text-[10px] font-medium px-1.5 py-0.5 rounded mt-1"
                      :class="app.status === 'qualified'
                        ? 'bg-blue-50 text-blue-600'
                        : 'bg-gray-100 text-gray-500'">
                      {{ app.status.replace('_', ' ').replace(/^\w/, c => c.toUpperCase()) }}
                    </span>
                  </div>
                </td>

                <!-- Exam Score -->
                <td class="px-3 py-4 text-center">
                  <div v-if="getScore(app, examType)" class="inline-flex flex-col items-center gap-1">
                    <span class="text-base font-bold"
                      :class="getScore(app, examType).passed ? 'text-green-700' : 'text-red-600'">
                      {{ getScore(app, examType).percentage }}%
                    </span>
                    <span class="text-[10px] text-gray-400">
                      {{ getScore(app, examType).raw_score }} / {{ getScore(app, examType).max_score }} items
                    </span>
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                      :class="getScore(app, examType).passed
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-600'">
                      {{ getScore(app, examType).passed ? 'PASS' : 'FAIL' }}
                    </span>
                  </div>
                  <div v-else class="inline-flex flex-col items-center gap-1 text-gray-300">
                    <span class="text-sm">—</span>
                    <span class="text-[10px]">Not yet encoded</span>
                  </div>
                </td>

                <!-- Result -->
                <td class="px-3 py-4 text-center">
                  <span v-if="getScore(app, examType)"
                    class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1 rounded-full"
                    :class="getScore(app, examType).passed
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-700'">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                      <path v-if="getScore(app, examType).passed" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ getScore(app, examType).passed ? 'PASSED' : 'FAILED' }}
                  </span>
                  <span v-else class="inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-100">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pending
                  </span>
                </td>

                <!-- Actions -->
                <td v-if="canEncode" class="pl-3 pr-6 py-4 text-right">
                  <button
                    v-if="app.exam_results.length"
                    @click="deleteScore(app)"
                    :disabled="deleting[app.id]"
                    class="text-xs text-gray-400 hover:text-red-600 hover:underline disabled:opacity-50 transition-colors">
                    {{ deleting[app.id] ? 'Clearing…' : 'Clear scores' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Table footer legend -->
        <div v-if="!loading && applications.length"
          class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-400 flex-wrap gap-2">
          <span>{{ applications.length }} applicant{{ applications.length !== 1 ? 's' : '' }} in this stage</span>
          <span>Blind Codes are used to preserve applicant anonymity during examination assessment.</span>
        </div>
      </div>

      <!-- ── Navigation ────────────────────────────────────────────────── -->
      <div class="flex items-center justify-between pt-2">
        <a :href="`/hrmpsb/qs-results/${props.vacancyId}`" class="btn-secondary">
          <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
          </svg>
          QS Results
        </a>
        <a :href="`/hrmpsb/cbwe-rating/${props.vacancyId}`" class="btn-primary">
          CBWE Rating →
        </a>
      </div>

    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import { useConfirm } from '@/composables/useConfirm'
import api from '@/services/api'

const { confirm } = useConfirm()

const props = defineProps({ vacancyId: Number, exam_type: { type: String, default: 'TWE' } })

const examType = computed(() => props.exam_type ?? 'TWE')
const stageNumber = 2
const stageLabel = 'Qualifying Exam (TWE)'

const loading          = ref(true)
const submitting       = ref(false)
const encodeOpen       = ref(true)
const encodeError      = ref(null)
const vacancy          = ref(null)
const applications     = ref([])
const canEncode        = ref(false)
const passingThreshold = ref(70)
const deleting         = reactive({})

const form = ref({
  application_id: '',
  exam_type:      'TWE',
  raw_score:      '',
  max_score:      100,
})

// ── Computed ──────────────────────────────────────────────────────────────

const scoreError = computed(() => {
  if (form.value.raw_score === '' || !form.value.max_score) return null
  if (Number(form.value.raw_score) > Number(form.value.max_score)) {
    return `Raw score cannot exceed max score (${form.value.max_score})`
  }
  return null
})

const previewPct = computed(() => {
  if (!form.value.max_score || form.value.raw_score === '') return '—'
  return ((form.value.raw_score / form.value.max_score) * 100).toFixed(2)
})

const passedCount  = computed(() => applications.value.filter(a => getScore(a, examType.value)?.passed).length)
const failedCount  = computed(() => applications.value.filter(a => getScore(a, examType.value) && !getScore(a, examType.value).passed).length)
const pendingCount = computed(() => applications.value.filter(a => !getScore(a, examType.value)).length)

// ── Helpers ───────────────────────────────────────────────────────────────

function appLabel(app) {
  if (app.token) return app.token
  return 'Pending-' + app.id
}

function getScore(app, type) {
  return app.exam_results?.find(r => r.exam_type === type) ?? null
}

function avgPct(app) {
  if (!app.exam_results?.length) return null
  const sum = app.exam_results.reduce((acc, r) => acc + Number(r.percentage), 0)
  return (sum / app.exam_results.length).toFixed(2)
}

// ── Data ──────────────────────────────────────────────────────────────────

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/exam-results/${props.vacancyId}`)
    vacancy.value          = data.vacancy
    applications.value     = data.applications
    canEncode.value        = data.can_encode ?? false
    passingThreshold.value = data.passing_threshold ?? 70
  } catch (e) {
    encodeError.value = e.response?.data?.message ?? 'Failed to load exam results.'
  } finally {
    loading.value = false
  }
}

async function submitScore() {
  if (scoreError.value) return
  submitting.value = true
  encodeError.value = null
  try {
    await api.post('/exam-results', {
      application_id: form.value.application_id,
      exam_type:      form.value.exam_type,
      raw_score:      form.value.raw_score,
      max_score:      form.value.max_score,
      vacancy_id:     props.vacancyId,
    })
    form.value = { application_id: '', exam_type: 'TWE', raw_score: '', max_score: 100 }
    await load()
  } catch (e) {
    encodeError.value = e.response?.data?.message ?? 'Failed to save score.'
  } finally {
    submitting.value = false
  }
}

async function deleteScore(app) {
  const ok = await confirm(`Clear all exam scores for this applicant? This cannot be undone.`)
  if (!ok) return
  deleting[app.id] = true
  try {
    for (const r of app.exam_results) {
      await api.delete(`/exam-results/${r.id}`)
    }
    await load()
  } finally {
    deleting[app.id] = false
  }
}

onMounted(load)
</script>

<style scoped>
@reference "../../../css/app.css";
.field-label {
  @apply block text-xs font-semibold text-gray-600 mb-1.5;
}
.input {
  @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-transparent outline-none transition;
}
.btn-primary {
  @apply inline-flex items-center px-5 py-2.5 bg-[#2a338f] text-white text-sm font-semibold rounded-xl hover:bg-[#1e2570] disabled:opacity-50 transition shadow-sm;
}
.btn-secondary {
  @apply inline-flex items-center px-5 py-2.5 border border-gray-300 text-sm font-semibold rounded-xl text-gray-700 hover:bg-gray-50 transition;
}
</style>
