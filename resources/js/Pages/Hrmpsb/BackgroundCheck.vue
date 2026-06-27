<template>
  <HrmbsboardLayout title="Background Investigation" :vacancyId="props.vacancyId">
    <div class="space-y-4">

      <VacancyBanner
        :vacancy="vacancy"
        :stage="7"
        stageLabel="Background Investigation"
        :loading="loading"
      />

      <div v-if="error &amp;&amp; !lockError" class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700">
        {{ error }}
      </div>

      <!-- Lock banner -->
      <div v-if="locked" class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl px-4 py-3">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        Background investigation has been locked. No further actions are allowed.
      </div>

      <!-- Secretariat lock action -->
      <div v-if="isSecretariat && !locked" class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-4 py-3">
        <div class="text-sm text-blue-800">
          <span class="font-semibold">Secretariat view:</span> You can manage investigation links below. Lock when all reports are complete.
        </div>
        <button @click="confirmLock" :disabled="locking"
          class="px-4 py-1.5 text-sm bg-blue-700 hover:bg-blue-800 text-white font-semibold rounded-lg transition-colors disabled:opacity-60">
          {{ locking ? 'Locking…' : 'Lock Background Check' }}
        </button>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 class="font-semibold text-gray-800">Applicant Background Investigation Reports</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">#</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Applicant</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Investigator</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Report</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">I. On Competencies</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">II. On Performance</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-if="loading">
                <td colspan="8" class="px-4 py-8 text-center text-gray-400">Loading…</td>
              </tr>
              <tr v-else-if="applications.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-400">No applications for background investigation.</td>
              </tr>
              <tr v-for="(app, idx) in applications" :key="app.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 font-mono text-xs align-top">
                  {{ idx + 1 }}
                </td>
                <td class="px-4 py-3 font-medium text-gray-800 align-top whitespace-nowrap">
                  {{ app.applicant?.last_name }}, {{ app.applicant?.first_name }}{{ middleInitial(app.applicant) }}
                </td>
                <td class="px-4 py-3 align-top">
                  <template v-if="reportFor(app)">
                    <p class="text-gray-800">{{ reportFor(app).investigator_name }}</p>
                    <p class="text-xs text-gray-400">{{ reportFor(app).investigator_email }}</p>
                  </template>
                  <span v-else class="text-gray-400 italic">—</span>
                </td>
                <td class="px-4 py-3 text-center align-top">
                  <span v-if="reportFor(app)?.submitted_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Submitted
                  </span>
                  <span v-else-if="reportFor(app)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Link Sent
                  </span>
                  <span v-else class="text-gray-400 italic">Not Requested</span>
                </td>
                <td class="px-4 py-3 text-center align-top">
                  <template v-if="reportFor(app)?.submitted_at">
                    <a
                      :href="pdfUrl(reportFor(app).id)"
                      target="_blank"
                      class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-xs font-medium"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                      View PDF
                    </a>
                  </template>
                  <span v-else class="text-gray-300 italic">—</span>
                </td>
                <td class="px-4 py-3 align-top max-w-xs">
                  <template v-if="reportFor(app)?.submitted_at">
                    <p class="text-xs text-gray-600 leading-relaxed whitespace-pre-wrap line-clamp-3">{{ reportFor(app).on_competencies }}</p>
                    <button
                      v-if="reportFor(app).on_competencies?.length > 300"
                      @click="toggleExpand(app.id, 'competencies')"
                      class="text-xs text-indigo-600 hover:text-indigo-800 mt-1"
                    >
                      {{ expanded[app.id]?.competencies ? 'Show less' : 'Read more' }}
                    </button>
                    <div v-if="expanded[app.id]?.competencies" class="mt-2 text-xs text-gray-600 whitespace-pre-wrap leading-relaxed bg-gray-50 rounded p-2 max-h-60 overflow-y-auto">
                      {{ reportFor(app).on_competencies }}
                    </div>
                  </template>
                  <span v-else class="text-gray-300 italic">—</span>
                </td>
                <td class="px-4 py-3 align-top max-w-xs">
                  <template v-if="reportFor(app)?.submitted_at">
                    <p class="text-xs text-gray-600 leading-relaxed whitespace-pre-wrap line-clamp-3">{{ reportFor(app).on_performance }}</p>
                    <button
                      v-if="reportFor(app).on_performance?.length > 300"
                      @click="toggleExpand(app.id, 'performance')"
                      class="text-xs text-indigo-600 hover:text-indigo-800 mt-1"
                    >
                      {{ expanded[app.id]?.performance ? 'Show less' : 'Read more' }}
                    </button>
                    <div v-if="expanded[app.id]?.performance" class="mt-2 text-xs text-gray-600 whitespace-pre-wrap leading-relaxed bg-gray-50 rounded p-2 max-h-60 overflow-y-auto">
                      {{ reportFor(app).on_performance }}
                    </div>
                  </template>
                  <span v-else class="text-gray-300 italic">—</span>
                </td>
                <td class="px-4 py-3 text-center align-top whitespace-nowrap">
                  <button
                    v-if="isSecretariat && !locked && !reportFor(app)"
                    @click="openGenerateModal(app)"
                    class="text-xs px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"
                  >Generate Link</button>
                  <span v-else-if="isSecretariat && !locked && reportFor(app) && !reportFor(app).submitted_at" class="inline-flex items-center gap-1.5">
                    <span class="text-xs text-yellow-600">Awaiting submission</span>
                    <button
                      @click="resendLink(reportFor(app))"
                      :disabled="resending[reportFor(app).id] || locked"
                      class="text-xs px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50 transition"
                    >{{ resending[reportFor(app).id] ? '…' : 'Resend' }}</button>
                    <button
                      @click="revokeLink(reportFor(app))"
                      :disabled="revoking[reportFor(app).id] || locked"
                      class="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 transition"
                    >{{ revoking[reportFor(app).id] ? '…' : 'Revoke' }}</button>
                  </span>
                  <span v-else-if="reportFor(app) && !reportFor(app).submitted_at" class="text-xs text-gray-400 italic">
                    Awaiting submission
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Generate Link Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeModal">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Generate Upload Link</h3>
          <p class="text-sm text-gray-500 mb-4">
            For: <span class="font-medium text-gray-700">{{ modalApplicantName }}</span>
          </p>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Investigator Name</label>
              <input
                v-model="investigatorName"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Full name of the investigator"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Investigator Email</label>
              <input
                v-model="investigatorEmail"
                type="email"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="email@example.com"
              />
            </div>
          </div>
          <p v-if="modalError" class="text-xs text-red-500 mt-3">{{ modalError }}</p>
          <div class="flex justify-end gap-3 mt-6">
            <button @click="closeModal" class="btn-secondary">Cancel</button>
            <button @click="generateLink" :disabled="generating" class="btn-primary">
              {{ generating ? 'Sending…' : 'Send Link' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Lock confirmation modal -->
      <div v-if="showLockConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showLockConfirm = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <h3 class="text-base font-semibold text-gray-900 mb-2">Lock Background Investigation?</h3>
          <p class="text-sm text-gray-500 mb-6">
            This will permanently lock the background investigation stage and update applicant statuses. This action <strong>cannot be undone</strong>.
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

      <div class="flex justify-between pt-2">
        <a :href="`/hrmpsb/eopt/${vacancyId}`" class="btn-secondary">← EOPT Assessment</a>
        <a :href="`/hrmpsb/deliberation/${vacancyId}`" class="btn-primary">Deliberation →</a>
      </div>
    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import api from '@/services/api'

const { confirm } = useConfirm()

const props = defineProps({ vacancyId: Number })

const toast = useToast()

const loading = ref(true)
const error = ref(null)
const vacancy = ref(null)
const applications = ref([])
const isSecretariat = ref(false)
const locked = ref(false)
const locking = ref(false)
const showLockConfirm = ref(false)
const lockError = ref(null)

const expanded = reactive({})

const showModal = ref(false)
const modalApplicationId = ref(null)
const modalApplicantName = ref('')
const investigatorName = ref('')
const investigatorEmail = ref('')
const generating = ref(false)
const modalError = ref('')
const resending = reactive({})
const revoking = reactive({})

function middleInitial(applicant) {
  return applicant?.middle_name ? ' ' + applicant.middle_name.charAt(0) + '.' : ''
}

function reportFor(app) {
  return app.background_investigation_reports?.[0] ?? null
}

function pdfUrl(reportId) {
  const token = window.localStorage.getItem('auth_token') || ''
  return `/background-investigation/pdf/${reportId}?token=${encodeURIComponent(token)}`
}

function toggleExpand(appId, section) {
  if (!expanded[appId]) expanded[appId] = {}
  expanded[appId][section] = !expanded[appId][section]
}

function openGenerateModal(app) {
  const p = app.applicant
  const mi = p?.middle_name ? ' ' + p.middle_name.charAt(0) + '.' : ''
  modalApplicantName.value = `${p?.last_name}, ${p?.first_name}${mi}`
  modalApplicationId.value = app.id
  investigatorName.value = ''
  investigatorEmail.value = ''
  modalError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  modalApplicationId.value = null
}

async function resendLink(report) {
  resending[report.id] = true
  error.value = null
  try {
    const { data } = await api.post(`/background-investigation/resend-link/${report.id}`)
    toast.success(data.message ?? 'Link resent to the investigator.')
    await load()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to resend link.'
    toast.error(e.response?.data?.message ?? 'Failed to resend link.')
  } finally {
    resending[report.id] = false
  }
}

async function revokeLink(report) {
  const ok = await confirm('Revoke this upload link? The investigator will no longer be able to submit. You can generate a new link afterward.')
  if (!ok) return
  revoking[report.id] = true
  error.value = null
  try {
    const { data } = await api.delete(`/background-investigation/revoke-link/${report.id}`)
    toast.success(data.message ?? 'Link revoked.')
    await load()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to revoke link.'
    toast.error(e.response?.data?.message ?? 'Failed to revoke link.')
  } finally {
    revoking[report.id] = false
  }
}

async function generateLink() {
  if (!investigatorName.value.trim() || !investigatorEmail.value.trim()) {
    modalError.value = 'Please fill in both fields.'
    return
  }
  generating.value = true
  modalError.value = ''
  try {
    const { data } = await api.post('/background-investigation/generate-link', {
      application_id: modalApplicationId.value,
      investigator_name: investigatorName.value.trim(),
      investigator_email: investigatorEmail.value.trim(),
    })
    closeModal()
    toast.success(data.message ?? 'Upload link sent to the investigator.')
    await load()
  } catch (e) {
    modalError.value = e.response?.data?.message ?? 'Failed to generate link.'
    toast.error(e.response?.data?.message ?? 'Failed to generate link.')
  } finally {
    generating.value = false
  }
}

async function load() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get(`/background-investigation/${props.vacancyId}`)
    vacancy.value = data.vacancy
    applications.value = data.applications
    isSecretariat.value = data.is_secretariat
    locked.value = data.locked ?? false
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load data.'
  } finally {
    loading.value = false
  }
}

function confirmLock() {
  lockError.value = null
  showLockConfirm.value = true
}

async function doLock() {
  locking.value = true
  lockError.value = null
  try {
    await api.patch(`/background-checks/${props.vacancyId}/lock`)
    showLockConfirm.value = false
    toast.success('Background investigation locked successfully.')
    await load()
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Failed to lock background investigation. Please try again.'
    lockError.value = msg
    toast.error(msg)
  } finally {
    locking.value = false
  }
}

onMounted(load)
</script>

<style scoped>
@reference "../../../css/app.css";
.btn-primary {
  @apply inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition;
}
.btn-secondary {
  @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 transition;
}
</style>
