<template>
  <HrmbsboardLayout :title="`QS Evaluation — ${vacancy?.position_title ?? '…'}`" :vacancyId="props.vacancyId">
    <div class="space-y-6">

    <!-- Vacancy Banner -->
    <VacancyBanner
      :vacancy="vacancy"
      :stage="2"
      stageLabel="QS Screening"
      :loading="loading"
    />

    <!-- Lock banner -->
    <div v-if="qsLocked" class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl px-4 py-3">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
      </svg>
      QS evaluations have been locked by the Secretariat. No further edits are allowed.
    </div>

    <!-- Secretariat lock action -->
    <div v-if="isSecretariat && !qsLocked" class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-4 py-3">
      <div class="text-sm text-blue-800">
        <span class="font-semibold">Secretariat view:</span> You can see all evaluators' inputs. Lock when all evaluations are complete.
      </div>
      <button @click="confirmLock" :disabled="locking"
        class="px-4 py-1.5 text-sm bg-blue-700 hover:bg-blue-800 text-white font-semibold rounded-lg transition-colors disabled:opacity-60">
        {{ locking ? 'Locking…' : 'Lock QS List' }}
      </button>
    </div>

    <!-- Position Requirements Panel -->
    <div v-if="vacancy" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <button @click="reqExpanded = !reqExpanded"
        class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
        <div class="flex items-center gap-2.5">
          <div class="w-7 h-7 rounded-lg bg-[#1a5276]/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-[#1a5276]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">Position Minimum Qualifications</p>
            <p class="text-xs text-gray-400">{{ vacancy.position_title }} · SG-{{ vacancy.salary_grade }}</p>
          </div>
        </div>
        <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0"
          :class="reqExpanded ? 'rotate-180' : ''"
          fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
      </button>

      <div v-if="reqExpanded" class="border-t border-gray-100 px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div v-for="req in requirements" :key="req.key" class="flex gap-3">
          <div :class="req.iconBg" class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4" :class="req.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="req.icon"/>
            </svg>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">{{ req.label }}</p>
            <p class="text-sm text-gray-800 leading-relaxed">{{ req.value || 'Not specified' }}</p>
          </div>
        </div>
      </div>
    </div>


    <div v-if="loading" class="space-y-3">
      <div v-for="n in 4" :key="n" class="h-24 bg-white rounded-xl border border-gray-200 animate-pulse"></div>
    </div>

    <div v-else class="space-y-4">
      <div v-for="app in applications" :key="app.id"
        class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <!-- Applicant header -->
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
          <div class="flex items-center gap-3 min-w-0">
            <span class="font-semibold text-gray-900">
              {{ formatName(app.applicant) }}
            </span>
            <span class="text-xs text-gray-400">{{ app.documents_count }} document(s)</span>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <span :class="statusClass(app.status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
              {{ app.status.replace('_', ' ') }}
            </span>
            <!-- Profile viewer button -->
            <button @click="openProfile(app)"
              class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-[#1a5276] bg-[#1a5276]/5 hover:bg-[#1a5276]/10 rounded-md transition-colors border border-[#1a5276]/20">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              View Credentials
            </button>
          </div>
        </div>

        <!-- ── Evaluation body ────────────────────────────────────────── -->
        <div class="px-5 py-4 space-y-4">

          <!-- Awaiting secretariat (members only, secretariat hasn't evaluated yet) -->
          <div v-if="!isSecretariat && !app.secretariat_evaluated"
            class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
            <svg class="w-4 h-4 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Awaiting the Secretariat's initial evaluation before you can submit your assessment.</span>
          </div>

          <!-- Secretariat's evaluation reference panel (shown to members once available) -->
          <div v-if="!isSecretariat && app.secretariat_evaluation"
            class="rounded-lg border border-indigo-100 bg-indigo-50/50 px-4 py-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-400 mb-2">Secretariat's Initial Assessment</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-2">
              <div v-for="criterion in criteria" :key="criterion.key" class="flex items-center gap-1.5">
                <span v-if="app.secretariat_evaluation[criterion.key]"
                  class="w-4 h-4 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                  <svg class="w-2.5 h-2.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                </span>
                <span v-else
                  class="w-4 h-4 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                  <svg class="w-2.5 h-2.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </span>
                <span class="text-xs text-gray-600">{{ criterion.label }}</span>
              </div>
            </div>
            <span class="inline-flex items-center text-[11px] font-bold px-2 py-0.5 rounded-full"
              :class="app.secretariat_evaluation.overall_qualified
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-600'">
              {{ app.secretariat_evaluation.overall_qualified ? 'Qualified' : 'Disqualified' }}
            </span>
            <span v-if="app.secretariat_evaluation.remarks" class="ml-2 text-xs text-gray-500 italic">
              "{{ app.secretariat_evaluation.remarks }}"
            </span>
          </div>

          <!-- Evaluation form (secretariat always; members only after secretariat has evaluated) -->
          <div v-if="isSecretariat || app.secretariat_evaluated">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
              <div v-for="criterion in criteria" :key="criterion.key" class="flex flex-col gap-1.5">
                <span class="text-xs font-semibold text-gray-600">{{ criterion.label }}</span>
                <div class="text-xs text-gray-400 mb-1 leading-tight">{{ criterion.hint }}</div>
                <div class="flex gap-3">
                  <label class="flex items-center gap-1.5 text-sm cursor-pointer"
                    :class="{'opacity-50 pointer-events-none': qsLocked}">
                    <input type="radio" :name="`${app.id}_${criterion.key}`"
                      :value="true"
                      v-model="getForm(app.id)[criterion.key]"
                      class="text-green-600" />
                    <span class="text-green-700 text-xs font-medium">Meets</span>
                  </label>
                  <label class="flex items-center gap-1.5 text-sm cursor-pointer"
                    :class="{'opacity-50 pointer-events-none': qsLocked}">
                    <input type="radio" :name="`${app.id}_${criterion.key}`"
                      :value="false"
                      v-model="getForm(app.id)[criterion.key]"
                      class="text-red-500" />
                    <span class="text-red-600 text-xs font-medium">Does Not Meet</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 items-start">
              <div class="flex-1 w-full">
                <textarea
                  v-model="getForm(app.id).remarks"
                  :placeholder="remarksRequired(app.id) ? 'Remarks are required when any criterion is not met…' : 'Remarks (optional)'"
                  :disabled="qsLocked"
                  rows="2"
                  class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-[#1a5276] focus:outline-none resize-none disabled:bg-gray-50 disabled:text-gray-400"
                  :class="remarksRequired(app.id) && !getForm(app.id).remarks
                    ? 'border-red-300 bg-red-50'
                    : 'border-gray-300'">
                </textarea>
                <p v-if="remarksRequired(app.id) && !getForm(app.id).remarks"
                  class="text-xs text-red-500 mt-1">
                  Please provide a justification for disqualification.
                </p>
              </div>
              <button
                v-if="!qsLocked"
                @click="submitEvaluation(app)"
                :disabled="saving[app.id] || !isFormValid(app.id)"
                class="px-4 py-2 text-sm bg-[#1a5276] hover:bg-[#154360] text-white font-semibold rounded-lg disabled:opacity-50 transition-colors whitespace-nowrap">
                {{ saving[app.id] ? 'Saving…' : (app.my_evaluation ? 'Update' : 'Submit') }}
              </button>
            </div>

            <div v-if="app.my_evaluation" class="mt-2 text-xs text-gray-400 flex items-center gap-2">
              <span>Last saved: {{ formatDate(app.my_evaluation.evaluated_at) }}</span>
              <span :class="app.my_evaluation.overall_qualified ? 'text-green-600 font-medium' : 'text-red-500 font-medium'">
                · {{ app.my_evaluation.overall_qualified ? 'Qualified' : 'Disqualified' }}
              </span>
            </div>
          </div>

          <!-- Secretariat: consolidated member evaluations below their own form -->
          <div v-if="isSecretariat && app.evaluations?.length > 0"
            class="border-t border-gray-100 pt-4 mt-2">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">All Member Evaluations</p>
            <table class="w-full text-xs">
              <thead>
                <tr class="text-gray-500 font-semibold uppercase tracking-wider border-b border-gray-100">
                  <th class="pb-2 text-left">Evaluator</th>
                  <th class="pb-2 text-center">Educ.</th>
                  <th class="pb-2 text-center">Exp.</th>
                  <th class="pb-2 text-center">Training</th>
                  <th class="pb-2 text-center">Eligibility</th>
                  <th class="pb-2 text-center">Overall</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="ev in app.evaluations" :key="ev.id" class="text-gray-700">
                  <td class="py-2 font-medium">{{ ev.evaluator?.name }}</td>
                  <td class="py-2 text-center">{{ ev.education_meets ? '✓' : '✗' }}</td>
                  <td class="py-2 text-center">{{ ev.experience_meets ? '✓' : '✗' }}</td>
                  <td class="py-2 text-center">{{ ev.training_meets ? '✓' : '✗' }}</td>
                  <td class="py-2 text-center">{{ ev.eligibility_meets ? '✓' : '✗' }}</td>
                  <td class="py-2 text-center">
                    <span :class="ev.overall_qualified ? 'text-green-600 font-semibold' : 'text-red-500 font-semibold'">
                      {{ ev.overall_qualified ? 'Qualified' : 'Disqualified' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="mt-2 text-xs text-gray-500 flex items-center gap-3">
              <span>{{ app.evaluation_summary?.qualified }} qualified / {{ app.evaluation_summary?.total }} evaluators</span>
              <span v-if="app.evaluation_summary?.total > 0"
                :class="app.evaluation_summary?.qualified > app.evaluation_summary?.total / 2
                  ? 'text-green-600 font-semibold'
                  : 'text-red-500 font-semibold'">
                → {{ app.evaluation_summary?.qualified > app.evaluation_summary?.total / 2 ? 'QUALIFIED (majority)' : 'DISQUALIFIED (majority)' }}
              </span>
            </div>
          </div>

        </div>
      </div>

      <div v-if="!applications.length" class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-sm text-gray-400">
        No applications to evaluate for this vacancy.
      </div>
    </div>

    <!-- Lock confirmation modal -->
    <div v-if="showLockConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showLockConfirm = false"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-2">Lock QS Evaluations?</h3>
        <p class="text-sm text-gray-500 mb-6">
          This will permanently lock all QS evaluations and automatically update application statuses.
          This action <strong>cannot be undone</strong>.
        </p>
        <p v-if="lockError" class="mb-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
          {{ lockError }}
        </p>
        <div class="flex justify-end gap-3">
          <button @click="showLockConfirm = false"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="doLock" :disabled="locking"
            class="px-4 py-2 text-sm bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 disabled:opacity-60">
            {{ locking ? 'Locking…' : 'Confirm Lock' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Applicant Profile Drawer -->
    <ApplicantProfileDrawer
      v-model="drawerOpen"
      :application-id="drawerAppId"
      :display-code="drawerDisplayCode" />

    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import ApplicantProfileDrawer from '@/Components/Hrmpsb/ApplicantProfileDrawer.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import { formatName } from '@/utils/formatName'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const props = defineProps({ vacancyId: Number })

const loading         = ref(true)
const locking         = ref(false)
const vacancy         = ref(null)
const applications    = ref([])
const isSecretariat   = ref(false)
const qsLocked        = ref(false)
const saving          = reactive({})
const showLockConfirm = ref(false)
const lockError       = ref(null)
const forms           = reactive({})
const reqExpanded     = ref(true)

const drawerOpen        = ref(false)
const drawerAppId       = ref(null)
const drawerDisplayCode = ref('')

const criteria = [
  {
    key:   'education_meets',
    label: 'Education',
    hint:  'Meets the required degree/course',
  },
  {
    key:   'experience_meets',
    label: 'Experience',
    hint:  'Meets the required years/type of experience',
  },
  {
    key:   'training_meets',
    label: 'Training',
    hint:  'Meets the required training hours',
  },
  {
    key:   'eligibility_meets',
    label: 'Eligibility',
    hint:  'Has the required civil service eligibility',
  },
]

const requirements = computed(() => {
  if (!vacancy.value) return []
  return [
    {
      key:       'education',
      label:     'Education',
      value:     vacancy.value.education_req,
      icon:      'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
      iconBg:    'bg-blue-50',
      iconColor: 'text-blue-600',
    },
    {
      key:       'experience',
      label:     'Experience',
      value:     vacancy.value.experience_req,
      icon:      'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
      iconBg:    'bg-emerald-50',
      iconColor: 'text-emerald-600',
    },
    {
      key:       'training',
      label:     'Training',
      value:     vacancy.value.training_req,
      icon:      'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
      iconBg:    'bg-violet-50',
      iconColor: 'text-violet-600',
    },
    {
      key:       'eligibility',
      label:     'Eligibility',
      value:     vacancy.value.eligibility_req,
      icon:      'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
      iconBg:    'bg-amber-50',
      iconColor: 'text-amber-600',
    },
  ]
})

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function getForm(appId) {
  if (!forms[appId]) {
    forms[appId] = {
      education_meets:   null,
      experience_meets:  null,
      training_meets:    null,
      eligibility_meets: null,
      remarks:           '',
    }
  }
  return forms[appId]
}

function remarksRequired(appId) {
  const f = forms[appId]
  if (!f) return false
  return f.education_meets === false
    || f.experience_meets === false
    || f.training_meets === false
    || f.eligibility_meets === false
}

function isFormValid(appId) {
  const f = forms[appId]
  if (!f) return false
  const allAnswered = f.education_meets !== null
    && f.experience_meets !== null
    && f.training_meets !== null
    && f.eligibility_meets !== null
  if (!allAnswered) return false
  if (remarksRequired(appId) && !f.remarks?.trim()) return false
  return true
}

function statusClass(status) {
  const map = {
    qualified:    'bg-green-50 text-green-700',
    disqualified: 'bg-red-50 text-red-600',
    submitted:    'bg-gray-100 text-gray-600',
    under_review: 'bg-yellow-50 text-yellow-700',
  }
  return map[status] ?? 'bg-gray-100 text-gray-600'
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function openProfile(app) {
  drawerAppId.value       = app.id
  drawerDisplayCode.value = app.display_code ?? ''
  drawerOpen.value        = true
}

async function loadData() {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/qs-evaluations/${props.vacancyId}`, { headers: authHeaders() })
    vacancy.value       = data.vacancy
    applications.value  = data.applications
    isSecretariat.value = data.is_secretariat
    qsLocked.value      = data.qs_locked

    data.applications.forEach(app => {
      if (app.my_evaluation) {
        forms[app.id] = {
          education_meets:   app.my_evaluation.education_meets,
          experience_meets:  app.my_evaluation.experience_meets,
          training_meets:    app.my_evaluation.training_meets,
          eligibility_meets: app.my_evaluation.eligibility_meets,
          remarks:           app.my_evaluation.remarks ?? '',
        }
      }
    })
  } finally {
    loading.value = false
  }
}

async function submitEvaluation(app) {
  saving[app.id] = true
  try {
    const form = forms[app.id]
    const payload = { application_id: app.id, ...form }
    if (app.my_evaluation) {
      await axios.put(`/api/qs-evaluations/${app.my_evaluation.id}`, payload, { headers: authHeaders() })
      toast.success('Evaluation updated.')
    } else {
      await axios.post('/api/qs-evaluations', payload, { headers: authHeaders() })
      toast.success('Evaluation submitted.')
    }
    loadData()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to save evaluation.')
  } finally {
    saving[app.id] = false
  }
}

function confirmLock() {
  lockError.value = null
  showLockConfirm.value = true
}

async function doLock() {
  locking.value  = true
  lockError.value = null
  try {
    await axios.patch(`/api/qs-evaluations/${props.vacancyId}/lock`, {}, { headers: authHeaders() })
    showLockConfirm.value = false
    toast.success('QS evaluations locked successfully.')
    loadData()
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Failed to lock QS evaluations. Please try again.'
    lockError.value = msg
    toast.error(msg)
  } finally {
    locking.value = false
  }
}

onMounted(loadData)
</script>
