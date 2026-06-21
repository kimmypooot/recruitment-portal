<template>
  <HrmbsboardLayout :title="`QS Evaluation — ${vacancy?.position_title ?? '…'}`">

    <!-- Lock banner -->
    <div v-if="qsLocked" class="mb-5 flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl px-4 py-3">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
      </svg>
      QS evaluations have been locked by the Secretariat. No further edits are allowed.
    </div>

    <!-- Secretariat lock action -->
    <div v-if="isSecretariat && !qsLocked" class="mb-5 flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-4 py-3">
      <div class="text-sm text-blue-800">
        <span class="font-semibold">Secretariat view:</span> You can see all evaluators' inputs. Lock when all evaluations are complete.
      </div>
      <button @click="confirmLock" :disabled="locking"
        class="px-4 py-1.5 text-sm bg-blue-700 hover:bg-blue-800 text-white font-semibold rounded-lg transition-colors disabled:opacity-60">
        {{ locking ? 'Locking…' : 'Lock QS List' }}
      </button>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="n in 4" :key="n" class="h-24 bg-white rounded-xl border border-gray-200 animate-pulse"></div>
    </div>

    <div v-else class="space-y-4">
      <div v-for="app in applications" :key="app.id"
        class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <!-- Applicant header -->
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
          <div>
            <span class="font-semibold text-gray-900">{{ app.applicant?.first_name }} {{ app.applicant?.last_name }}</span>
            <span class="ml-2 text-xs text-gray-400">{{ app.documents_count }} document(s) submitted</span>
          </div>
          <span :class="statusClass(app.status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
            {{ app.status.replace('_', ' ') }}
          </span>
        </div>

        <!-- Secretariat: all evaluations -->
        <div v-if="isSecretariat && app.evaluations?.length" class="px-5 py-4">
          <table class="w-full text-xs">
            <thead>
              <tr class="text-gray-500 font-semibold uppercase tracking-wider border-b border-gray-100">
                <th class="pb-2 text-left">Evaluator</th>
                <th class="pb-2 text-center">Education</th>
                <th class="pb-2 text-center">Experience</th>
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
          <div class="mt-2 text-xs text-gray-500">
            {{ app.evaluation_summary?.qualified }} qualified / {{ app.evaluation_summary?.total }} evaluators
          </div>
        </div>

        <!-- Member: own evaluation form -->
        <div v-else class="px-5 py-4">
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
            <div v-for="criterion in criteria" :key="criterion.key" class="flex flex-col gap-1.5">
              <span class="text-xs font-semibold text-gray-600">{{ criterion.label }}</span>
              <div class="flex gap-3">
                <label class="flex items-center gap-1.5 text-sm cursor-pointer"
                  :class="{'opacity-50 pointer-events-none': qsLocked}">
                  <input type="radio" :name="`${app.id}_${criterion.key}`"
                    :value="true"
                    v-model="getForm(app.id)[criterion.key]"
                    class="text-green-600" />
                  <span class="text-green-700">Meets</span>
                </label>
                <label class="flex items-center gap-1.5 text-sm cursor-pointer"
                  :class="{'opacity-50 pointer-events-none': qsLocked}">
                  <input type="radio" :name="`${app.id}_${criterion.key}`"
                    :value="false"
                    v-model="getForm(app.id)[criterion.key]"
                    class="text-red-500" />
                  <span class="text-red-600">Does Not Meet</span>
                </label>
              </div>
            </div>
          </div>
          <div class="flex flex-col sm:flex-row gap-3 items-start">
            <textarea
              v-model="getForm(app.id).remarks"
              placeholder="Remarks (optional)"
              :disabled="qsLocked"
              rows="2"
              class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1a5276] focus:outline-none resize-none disabled:bg-gray-50 disabled:text-gray-400">
            </textarea>
            <button
              v-if="!qsLocked"
              @click="submitEvaluation(app)"
              :disabled="saving[app.id] || !isFormComplete(app.id)"
              class="px-4 py-2 text-sm bg-[#1a5276] hover:bg-[#154360] text-white font-semibold rounded-lg disabled:opacity-50 transition-colors whitespace-nowrap">
              {{ saving[app.id] ? 'Saving…' : (app.my_evaluation ? 'Update' : 'Submit') }}
            </button>
          </div>
          <div v-if="app.my_evaluation" class="mt-2 text-xs text-gray-400">
            Last saved: {{ formatDate(app.my_evaluation.evaluated_at) }} ·
            <span :class="app.my_evaluation.overall_qualified ? 'text-green-600 font-medium' : 'text-red-500 font-medium'">
              {{ app.my_evaluation.overall_qualified ? 'Qualified' : 'Disqualified' }}
            </span>
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

  </HrmbsboardLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'

const props = defineProps({ vacancyId: Number })

const loading         = ref(true)
const locking         = ref(false)
const vacancy         = ref(null)
const applications    = ref([])
const isSecretariat   = ref(false)
const qsLocked        = ref(false)
const saving          = reactive({})
const showLockConfirm = ref(false)
const forms           = reactive({})

const criteria = [
  { key: 'education_meets',   label: 'Education' },
  { key: 'experience_meets',  label: 'Experience' },
  { key: 'training_meets',    label: 'Training' },
  { key: 'eligibility_meets', label: 'Eligibility' },
]

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function getForm(appId) {
  if (!forms[appId]) {
    forms[appId] = { education_meets: null, experience_meets: null, training_meets: null, eligibility_meets: null, remarks: '' }
  }
  return forms[appId]
}

function isFormComplete(appId) {
  const f = forms[appId]
  return f && f.education_meets !== null && f.experience_meets !== null && f.training_meets !== null && f.eligibility_meets !== null
}

function statusClass(status) {
  const map = {
    qualified: 'bg-green-50 text-green-700',
    disqualified: 'bg-red-50 text-red-600',
    submitted: 'bg-gray-100 text-gray-600',
    under_review: 'bg-yellow-50 text-yellow-700',
  }
  return map[status] ?? 'bg-gray-100 text-gray-600'
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

async function loadData() {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/qs-evaluations/${props.vacancyId}`, { headers: authHeaders() })
    vacancy.value       = data.vacancy
    applications.value  = data.applications
    isSecretariat.value = data.is_secretariat
    qsLocked.value      = data.qs_locked

    // Pre-fill forms from existing evaluations
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
    } else {
      await axios.post('/api/qs-evaluations', payload, { headers: authHeaders() })
    }
    loadData()
  } finally {
    saving[app.id] = false
  }
}

function confirmLock() { showLockConfirm.value = true }

async function doLock() {
  locking.value = true
  try {
    await axios.patch(`/api/qs-evaluations/${props.vacancyId}/lock`, {}, { headers: authHeaders() })
    showLockConfirm.value = false
    loadData()
  } finally {
    locking.value = false
  }
}

onMounted(loadData)
</script>
