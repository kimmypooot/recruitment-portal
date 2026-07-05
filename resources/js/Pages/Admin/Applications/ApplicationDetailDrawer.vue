<template>
  <Teleport to="body">
    <div v-if="app" class="fixed inset-0 z-50 flex justify-end">
      <div class="absolute inset-0 bg-black/40" @click="$emit('close')"></div>
      <div class="relative bg-white w-full max-w-md h-full flex flex-col shadow-2xl overflow-hidden">

        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary text-sm font-bold flex-shrink-0">
              {{ initials }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ applicantName }}</p>
              <p class="text-xs text-gray-400">{{ app.applicant?.user?.email ?? '' }}</p>
            </div>
          </div>
          <button @click="$emit('close')" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
            <Icon name="xmark" size="5" />
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Current Status</p>
            <StatusBadge :status="app.status" />
          </div>

          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Applied Position</p>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-sm font-semibold text-gray-900">{{ app.vacancy?.position_title ?? '—' }}</p>
              <p class="text-xs text-gray-500 mt-0.5">
                <span v-if="app.vacancy?.salary_grade">SG-{{ app.vacancy.salary_grade }}</span>
                <span v-if="app.vacancy?.place_of_assignment"> · {{ app.vacancy.place_of_assignment }}</span>
              </p>
            </div>
          </div>

          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Application Timeline</p>
            <StatusPipeline :status="app.status" />
          </div>

          <div v-if="app.remarks">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Remarks</p>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ app.remarks }}</p>
          </div>

          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Dates</p>
            <dl class="space-y-1.5">
              <div class="flex gap-3 text-xs">
                <dt class="w-28 text-gray-400 font-medium">Submitted</dt>
                <dd class="text-gray-700">{{ formatDate(app.submitted_at ?? app.created_at) }}</dd>
              </div>
              <div v-if="app.reviewed_at" class="flex gap-3 text-xs">
                <dt class="w-28 text-gray-400 font-medium">Last Updated</dt>
                <dd class="text-gray-700">{{ formatDate(app.reviewed_at) }}</dd>
              </div>
            </dl>
          </div>

          <!-- CS Forms -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">CS Forms</p>

            <div class="flex gap-2 mb-3">
              <select v-model="csForm.type"
                class="flex-1 px-2.5 pr-7 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
                <option value="33A">CS Form 33-A (Appointment)</option>
                <option value="33B">CS Form 33-B (Casual/Contractual)</option>
                <option value="form1">CS Form 1 (Personal Data Sheet)</option>
              </select>
              <button @click="generateForm" :disabled="csForm.generating"
                class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-primary text-white rounded-lg hover:bg-primary-dark disabled:opacity-60 transition-colors">
                <svg v-if="csForm.generating" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <Icon v-else name="plus" size="3" />
                Generate
              </button>
            </div>

            <p v-if="csForm.error" class="text-xs text-red-600 mb-2">{{ csForm.error }}</p>

            <div v-if="csForm.loading" class="space-y-2">
              <div v-for="n in 2" :key="n" class="h-10 bg-gray-100 rounded-lg animate-pulse"></div>
            </div>

            <div v-else-if="csForm.forms.length" class="space-y-2">
              <div v-for="f in csForm.forms" :key="f.id"
                class="flex items-center justify-between gap-2 p-2.5 bg-gray-50 rounded-lg border border-gray-100">
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-gray-800 truncate">{{ f.form_label }}</p>
                  <p class="text-[10px] text-gray-400 mt-0.5">
                    {{ formatDate(f.generated_at) }}
                    <span v-if="f.signed_at" class="ml-1 text-green-600 font-medium">· Signed</span>
                    <span v-if="f.submitted_to_csc_at" class="ml-1 text-blue-600 font-medium">· Submitted</span>
                  </p>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                  <button @click="downloadForm(f)"
                    class="p-1.5 text-gray-500 hover:text-primary hover:bg-primary/8 rounded-md transition-colors"
                    title="Download PDF">
                    <Icon name="download" size="3.5" />
                  </button>
                  <button v-if="!f.signed_at && csForm.pnpkiReady"
                    @click="signForm(f)" :disabled="csForm.actionId === f.id"
                    class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-md transition-colors disabled:opacity-50"
                    title="Sign via PNPKI">
                    <Icon name="document" size="3.5" />
                  </button>
                  <button v-if="!f.submitted_to_csc_at"
                    @click="markSubmitted(f)" :disabled="csForm.actionId === f.id"
                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors disabled:opacity-50"
                    title="Mark as submitted to CSC">
                    <Icon name="check" size="3.5" />
                  </button>
                </div>
              </div>
            </div>

            <p v-else class="text-xs text-gray-400 italic">No forms generated yet.</p>
          </div>

        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex-shrink-0">
          <button @click="$emit('update-status', app)"
            class="w-full py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg transition-colors">
            Update Status
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import axios from 'axios'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import StatusPipeline from '@/Components/UI/StatusPipeline.vue'
import Icon from '@/Components/UI/Icon.vue'
import { formatDate } from '@/utils/dates'

const props = defineProps({
  app: { type: Object, default: null },
})
defineEmits(['close', 'update-status'])

const csForm = reactive({ type: '33A', forms: [], loading: false, generating: false, actionId: null, pnpkiReady: false, error: '' })

const initials = computed(() => {
  const user = props.app?.applicant?.user
  if (user) {
    return ((user.first_name?.[0] ?? '') + (user.last_name?.[0] ?? '')).toUpperCase() || '?'
  }
  return '?'
})

const applicantName = computed(() => {
  const p = props.app?.applicant
  if (p?.last_name && p?.first_name) {
    const middle = p.middle_name ? ' ' + p.middle_name.charAt(0).toUpperCase() + '.' : ''
    return `${p.last_name}, ${p.first_name}${middle}`
  }
  return p?.user?.full_name ?? '—'
})

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

watch(() => props.app, (app) => {
  csForm.forms = []
  csForm.error = ''
  if (app) loadForms(app.id)
})

async function loadForms(applicationId) {
  csForm.loading = true
  try {
    const { data } = await axios.get(`/api/applications/${applicationId}/forms`, { headers: authHeaders() })
    csForm.forms = data.forms ?? []
    csForm.pnpkiReady = data.pnpki_ready ?? false
  } catch {
    csForm.forms = []
  } finally {
    csForm.loading = false
  }
}

async function generateForm() {
  csForm.generating = true
  csForm.error = ''
  try {
    await axios.post(`/api/applications/${props.app.id}/forms`, { form_type: csForm.type }, { headers: authHeaders() })
    await loadForms(props.app.id)
  } catch (e) {
    csForm.error = e.response?.data?.message ?? 'Generation failed.'
  } finally {
    csForm.generating = false
  }
}

async function signForm(form) {
  csForm.actionId = form.id
  csForm.error = ''
  try {
    await axios.patch(`/api/forms/${form.id}/sign`, {}, { headers: authHeaders() })
    await loadForms(props.app.id)
  } catch (e) {
    csForm.error = e.response?.data?.message ?? 'Signing failed.'
  } finally {
    csForm.actionId = null
  }
}

async function downloadForm(form) {
  try {
    const { data } = await axios.get(`/api/forms/${form.id}/download`, {
      headers: { ...authHeaders() },
      responseType: 'blob',
    })
    const url = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
    const a = document.createElement('a')
    a.href = url
    a.download = `CSForm-${form.form_type}-App${props.app.id}.pdf`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    csForm.error = 'Download failed.'
  }
}

async function markSubmitted(form) {
  csForm.actionId = form.id
  csForm.error = ''
  try {
    await axios.patch(`/api/forms/${form.id}/mark-submitted`, {}, { headers: authHeaders() })
    await loadForms(props.app.id)
  } catch (e) {
    csForm.error = e.response?.data?.message ?? 'Action failed.'
  } finally {
    csForm.actionId = null
  }
}
</script>
