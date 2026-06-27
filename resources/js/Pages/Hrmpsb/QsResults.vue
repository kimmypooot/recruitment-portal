<template>
  <HrmbsboardLayout :title="`QS Results — ${vacancy?.position_title ?? '…'}`" :vacancyId="props.vacancyId">
    <div class="space-y-6">

      <!-- ── Vacancy Banner ──────────────────────────────────────────────── -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="2"
        stageLabel="QS Results"
        :loading="loading"
      >
        <div v-if="qsLocked" class="mt-4 flex items-center gap-2 text-xs text-amber-700">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          <span class="font-semibold">QS Evaluations are locked.</span>
          <span class="text-amber-600">Results below are final.</span>
        </div>
      </VacancyBanner>

      <!-- ── Summary Cards ─────────────────────────────────────────────── -->
      <div v-if="!loading && applications.length" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-gray-900">{{ applications.length }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Total Applicants</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-green-700">{{ qualifiedCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Qualified</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Proceed to Exam Stage</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-red-500">{{ disqualifiedCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Disqualified</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Did not meet QS</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-amber-500">{{ pendingCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Pending</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Awaiting evaluation</p>
        </div>
      </div>

      <!-- ── Results Table ─────────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Consolidated QS Evaluation Results</h2>
            <p class="text-xs text-gray-400 mt-0.5">Based on majority votes from HRMPSB evaluators</p>
          </div>
          <div class="flex items-center gap-2 text-xs text-gray-500">
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>Qualified
            </span>
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>Disqualified
            </span>
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-amber-300 inline-block"></span>Pending
            </span>
          </div>
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" class="divide-y divide-gray-100">
          <div v-for="n in 5" :key="n" class="px-6 py-4 flex items-center gap-4">
            <div class="w-6 h-6 rounded-full bg-gray-100 animate-pulse flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-100 rounded w-1/3 animate-pulse"></div>
            </div>
            <div class="w-16 h-6 bg-gray-100 rounded-full animate-pulse"></div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="!applications.length"
          class="flex flex-col items-center justify-center py-16 text-center">
          <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-3">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-500">No applications found</p>
          <p class="text-xs text-gray-400 mt-1">Applications will appear here once evaluations are submitted.</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/70">
                <th class="pl-6 pr-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider w-10">#</th>
                <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Applicant</th>
                <template v-if="isSecretariat">
                  <th class="px-2 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    <div>Educ.</div>
                  </th>
                  <th class="px-2 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    <div>Exp.</div>
                  </th>
                  <th class="px-2 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    <div>Training</div>
                  </th>
                  <th class="px-2 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    <div>Eligibility</div>
                  </th>
                </template>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Votes</th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Result</th>
                <th v-if="isSecretariat" class="pl-3 pr-6 py-3 w-8"></th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(app, idx) in sortedApplications" :key="app.id">
                <!-- Main row -->
                <tr
                  class="transition-colors border-b border-gray-50"
                  :class="{
                    'bg-green-50/40 hover:bg-green-50/70': app.status === 'qualified',
                    'bg-red-50/30 hover:bg-red-50/50':    app.status === 'disqualified',
                    'hover:bg-gray-50':                    !['qualified','disqualified'].includes(app.status),
                  }">

                  <!-- # -->
                  <td class="pl-6 pr-3 py-4 text-center text-xs text-gray-400 font-medium">{{ idx + 1 }}</td>

                  <!-- Applicant name -->
                  <td class="px-3 py-4">
                    <p class="text-sm font-semibold text-gray-900">{{ formatName(app.applicant) }}</p>
                  </td>

                  <!-- Criteria vote columns (secretariat only) -->
                  <template v-if="isSecretariat">
                    <td v-for="criterion in criteria" :key="criterion.key" class="px-2 py-4 text-center">
                      <div v-if="app.evaluations?.length" class="flex flex-col items-center gap-0.5">
                        <span class="text-xs font-bold"
                          :class="criteriaVotes(app, criterion.key).met > criteriaVotes(app, criterion.key).total / 2
                            ? 'text-green-700' : 'text-red-500'">
                          {{ criteriaVotes(app, criterion.key).met }}/{{ criteriaVotes(app, criterion.key).total }}
                        </span>
                        <svg v-if="criteriaVotes(app, criterion.key).met > criteriaVotes(app, criterion.key).total / 2"
                          class="w-3.5 h-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg v-else class="w-3.5 h-3.5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </div>
                      <span v-else class="text-gray-300 text-xs">—</span>
                    </td>
                  </template>

                  <!-- Vote count -->
                  <td class="px-3 py-4 text-center">
                    <span v-if="app.evaluation_summary?.total" class="text-sm">
                      <span class="font-bold text-green-700">{{ app.evaluation_summary.qualified }}</span>
                      <span class="text-gray-400 text-xs"> / {{ app.evaluation_summary.total }}</span>
                    </span>
                    <span v-else class="text-gray-300 text-xs">—</span>
                  </td>

                  <!-- Result badge -->
                  <td class="px-3 py-4 text-center">
                    <span v-if="app.status === 'qualified'"
                      class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1 rounded-full bg-green-100 text-green-800">
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                      Qualified
                    </span>
                    <span v-else-if="app.status === 'disqualified'"
                      class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1 rounded-full bg-red-100 text-red-700">
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                      Disqualified
                    </span>
                    <span v-else
                      class="inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-100">
                      Pending
                    </span>
                  </td>

                  <!-- Expand toggle (secretariat only) -->
                  <td v-if="isSecretariat" class="pl-3 pr-6 py-4 text-center">
                    <button
                      v-if="app.evaluations?.length"
                      @click="expanded[app.id] = !expanded[app.id]"
                      class="text-gray-400 hover:text-indigo-600 transition-colors">
                      <svg class="w-4 h-4 transition-transform"
                        :class="expanded[app.id] ? 'rotate-180' : ''"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                      </svg>
                    </button>
                  </td>
                </tr>

                <!-- Expanded evaluator breakdown -->
                <tr v-if="isSecretariat && expanded[app.id]" :key="'exp-' + app.id">
                  <td :colspan="isSecretariat ? 9 : 4" class="px-6 pb-4 pt-0 bg-gray-50/80">
                    <div class="rounded-xl border border-gray-200 overflow-hidden">
                      <table class="w-full text-xs">
                        <thead class="bg-gray-100">
                          <tr class="text-gray-500 font-semibold uppercase tracking-wider">
                            <th class="px-4 py-2 text-left">Evaluator</th>
                            <th class="px-3 py-2 text-center">Education</th>
                            <th class="px-3 py-2 text-center">Experience</th>
                            <th class="px-3 py-2 text-center">Training</th>
                            <th class="px-3 py-2 text-center">Eligibility</th>
                            <th class="px-3 py-2 text-center">Overall</th>
                            <th class="px-4 py-2 text-left">Remarks</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                          <tr v-for="ev in app.evaluations" :key="ev.id" class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-medium text-gray-800">{{ ev.evaluator?.full_name ?? '—' }}</td>
                            <td v-for="criterion in criteria" :key="criterion.key" class="px-3 py-2.5 text-center">
                              <span v-if="ev[criterion.key]" class="text-green-600 font-bold">✓</span>
                              <span v-else class="text-red-400 font-bold">✗</span>
                            </td>
                            <td class="px-3 py-2.5 text-center">
                              <span class="font-bold px-2 py-0.5 rounded-full text-[10px]"
                                :class="ev.overall_qualified ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                                {{ ev.overall_qualified ? 'Qualified' : 'Disqualified' }}
                              </span>
                            </td>
                            <td class="px-4 py-2.5 text-gray-500 italic">{{ ev.remarks || '—' }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Footer -->
        <div v-if="!loading && applications.length"
          class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 text-xs text-gray-400">
          {{ applications.length }} applicant{{ applications.length !== 1 ? 's' : '' }} evaluated
          <span v-if="isSecretariat"> — click the chevron on any row to view individual evaluator votes</span>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex items-center justify-between pt-2">
        <a :href="`/hrmpsb/qs-evaluation/${props.vacancyId}`" class="btn-secondary">
          <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
          </svg>
          QS Evaluation
        </a>
        <a :href="`/hrmpsb/exam-results/${props.vacancyId}`" class="btn-primary">
          Exam Results
          <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
          </svg>
        </a>
      </div>

    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import { formatName } from '@/utils/formatName'

const props = defineProps({ vacancyId: Number })

const loading      = ref(true)
const vacancy      = ref(null)
const applications = ref([])
const qsLocked     = ref(false)
const isSecretariat = ref(false)
const expanded     = reactive({})

const criteria = [
  { key: 'education_meets',   label: 'Education' },
  { key: 'experience_meets',  label: 'Experience' },
  { key: 'training_meets',    label: 'Training' },
  { key: 'eligibility_meets', label: 'Eligibility' },
]

const qualifiedCount   = computed(() => applications.value.filter(a => a.status === 'qualified').length)
const disqualifiedCount = computed(() => applications.value.filter(a => a.status === 'disqualified').length)
const pendingCount     = computed(() => applications.value.filter(a => !['qualified','disqualified'].includes(a.status)).length)

const sortedApplications = computed(() => {
  const order = { qualified: 0, disqualified: 1 }
  return [...applications.value].sort((a, b) => (order[a.status] ?? 2) - (order[b.status] ?? 2))
})

function criteriaVotes(app, key) {
  const evs = app.evaluations ?? []
  return { met: evs.filter(e => e[key]).length, total: evs.length }
}

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function loadData() {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/qs-evaluations/${props.vacancyId}`, { headers: authHeaders() })
    vacancy.value       = data.vacancy
    applications.value  = data.applications
    qsLocked.value      = data.qs_locked
    isSecretariat.value = data.is_secretariat
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
@reference "../../../css/app.css";
.btn-primary {
  @apply inline-flex items-center px-5 py-2.5 bg-[#2a338f] text-white text-sm font-semibold rounded-xl hover:bg-[#1e2570] disabled:opacity-50 transition shadow-sm;
}
.btn-secondary {
  @apply inline-flex items-center px-5 py-2.5 border border-gray-300 text-sm font-semibold rounded-xl text-gray-700 hover:bg-gray-50 transition;
}
</style>
