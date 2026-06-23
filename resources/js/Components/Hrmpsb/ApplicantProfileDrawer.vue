<template>
  <!-- Backdrop -->
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0">
    <div v-if="modelValue" class="fixed inset-0 z-40 bg-black/40" @click="$emit('update:modelValue', false)" />
  </Transition>

  <!-- Drawer panel -->
  <Transition
    enter-active-class="transition-transform duration-250 ease-out"
    enter-from-class="translate-x-full"
    enter-to-class="translate-x-0"
    leave-active-class="transition-transform duration-200 ease-in"
    leave-from-class="translate-x-0"
    leave-to-class="translate-x-full">
    <div v-if="modelValue"
      class="fixed inset-y-0 right-0 z-50 w-full max-w-lg bg-white shadow-2xl flex flex-col">

      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-[#1a5276] text-white flex-shrink-0">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-white/70">Applicant Credentials</p>
          <p v-if="profile && profile.unmasked" class="text-base font-bold mt-0.5">{{ profile.name }}</p>
          <p v-else-if="profile" class="text-base font-bold mt-0.5 font-mono">{{ profile.token ?? displayCode }}</p>
          <p v-else class="text-base font-bold mt-0.5 text-white/50">Loading…</p>
        </div>
        <button @click="$emit('update:modelValue', false)"
          class="p-1.5 rounded-lg hover:bg-white/15 transition-colors">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Masked notice -->
      <div v-if="profile && !profile.unmasked"
        class="mx-4 mt-3 flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5 text-xs text-amber-800 flex-shrink-0">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <span>Applicant identity is concealed during this evaluation stage to ensure impartial assessment.</span>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="flex-1 flex flex-col p-5 gap-4 overflow-y-auto">
        <div v-for="n in 4" :key="n" class="space-y-2">
          <div class="h-4 bg-gray-100 rounded w-1/3 animate-pulse"></div>
          <div class="h-16 bg-gray-50 rounded-lg animate-pulse"></div>
        </div>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="flex-1 flex items-center justify-center p-8 text-center">
        <div>
          <p class="text-sm font-semibold text-red-700 mb-1">Failed to load profile</p>
          <p class="text-xs text-gray-400">{{ error }}</p>
          <button @click="load" class="mt-3 text-xs text-[#1a5276] hover:underline">Retry</button>
        </div>
      </div>

      <!-- Content -->
      <div v-else-if="profile" class="flex-1 overflow-y-auto p-5 space-y-5">

        <!-- Submitted Documents (shown first so members can view immediately) -->
        <section>
          <h3 class="section-heading">Submitted Documents</h3>
          <div class="credential-card">
            <div class="space-y-2">
              <template v-for="(label, key) in DOC_LABELS" :key="key">
                <div class="flex items-center gap-2.5">
                  <!-- Status icon -->
                  <span v-if="profile.documents?.[key]"
                    class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                  </span>
                  <span v-else
                    class="flex-shrink-0 w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </span>

                  <!-- Label -->
                  <span class="text-xs flex-1 min-w-0"
                    :class="profile.documents?.[key] ? 'text-gray-800' : 'text-gray-400'">
                    {{ label }}
                  </span>

                  <!-- View button (only when document exists and link available) -->
                  <button
                    v-if="profile.documents?.[key] && profile.document_links?.[key]"
                    @click="viewDocument(key, profile.document_links[key])"
                    :disabled="viewingDoc === key"
                    class="flex-shrink-0 flex items-center gap-1 text-xs px-2.5 py-1 rounded-md font-medium transition-colors"
                    :class="viewingDoc === key
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-[#1a5276]/10 text-[#1a5276] hover:bg-[#1a5276]/20'">
                    <svg v-if="viewingDoc === key"
                      class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ viewingDoc === key ? 'Opening…' : 'View' }}
                  </button>

                  <!-- Not submitted badge -->
                  <span v-else-if="!profile.documents?.[key]"
                    class="flex-shrink-0 text-[10px] text-gray-400 italic">Not submitted</span>
                </div>
              </template>
            </div>
          </div>
        </section>

        <!-- Eligibility -->
        <section>
          <h3 class="section-heading">Civil Service Eligibility</h3>
          <div class="credential-card">
            <p v-if="profile.eligibility" class="text-sm text-gray-800">{{ profile.eligibility }}</p>
            <p v-else class="text-sm text-gray-400 italic">Not specified</p>
          </div>
        </section>

        <!-- Education -->
        <section>
          <h3 class="section-heading">Educational Attainment</h3>
          <div v-if="profile.education?.length" class="space-y-2">
            <div v-for="ed in profile.education" :key="ed.id" class="credential-card">
              <div class="flex items-start justify-between gap-2">
                <div>
                  <p class="text-sm font-semibold text-gray-900">{{ ed.degree_course ?? ed.level }}</p>
                  <p class="text-xs text-gray-600 mt-0.5">{{ ed.school_name }}</p>
                </div>
                <span v-if="ed.year_graduated" class="text-xs text-gray-400 flex-shrink-0">{{ ed.year_graduated }}</span>
              </div>
              <p v-if="ed.honors" class="text-xs text-indigo-600 mt-1">{{ ed.honors }}</p>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 italic">No education records submitted.</p>
        </section>

        <!-- Work Experience -->
        <section>
          <h3 class="section-heading">Work Experience</h3>
          <div v-if="profile.experience?.length" class="space-y-2">
            <div v-for="exp in profile.experience" :key="exp.id" class="credential-card">
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-gray-900 truncate">{{ exp.position_title }}</p>
                  <p class="text-xs text-gray-600 mt-0.5">{{ exp.department_agency }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-xs text-gray-500">{{ formatPeriod(exp.date_from, exp.date_to, exp.is_present) }}</p>
                  <p v-if="exp.monthly_salary" class="text-xs text-gray-400 mt-0.5">₱{{ Number(exp.monthly_salary).toLocaleString() }}/mo</p>
                </div>
              </div>
              <p v-if="exp.appointment_status" class="text-xs text-indigo-600 mt-1 capitalize">{{ exp.appointment_status }}</p>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 italic">No work experience records submitted.</p>
        </section>

        <!-- Trainings -->
        <section>
          <h3 class="section-heading">Relevant Training & Seminars</h3>
          <div v-if="profile.trainings?.length" class="space-y-2">
            <div v-for="tr in profile.trainings" :key="tr.id" class="credential-card">
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-900">{{ tr.title }}</p>
                  <p class="text-xs text-gray-500 mt-0.5">{{ tr.conducted_by }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-xs text-gray-500">{{ formatTrainingPeriod(tr.date_from, tr.date_to) }}</p>
                  <p v-if="tr.hours" class="text-xs text-gray-400">{{ tr.hours }}h</p>
                </div>
              </div>
              <span v-if="tr.type" class="inline-block mt-1 text-[10px] font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">
                {{ tr.type }}
              </span>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 italic">No training records submitted.</p>
        </section>

      </div>

    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue:    { type: Boolean, default: false },
  applicationId: { type: Number,  default: null },
  displayCode:   { type: String,  default: '' },
})

defineEmits(['update:modelValue'])

const loading    = ref(false)
const error      = ref(null)
const profile    = ref(null)
const viewingDoc = ref(null)

const DOC_LABELS = {
  pds:        'Personal Data Sheet (PDS) x Work Experience Sheet',
  tor:        'Transcript of Records',
  app_letter: 'Application Letter',
  coe:        'Certificate of Eligibility',
  ipcr:       'IPCR / Performance Rating',
}

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', year: 'numeric' })
}

function formatFullDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function formatTrainingPeriod(from, to) {
  if (!from) return '—'
  if (!to || from === to) return formatFullDate(from)
  return `${formatFullDate(from)} – ${formatFullDate(to)}`
}

function formatPeriod(from, to, isCurrent) {
  const f = from ? formatDate(from) : '—'
  const t = isCurrent ? 'Present' : (to ? formatDate(to) : '—')
  return `${f} – ${t}`
}

async function viewDocument(type, url) {
  viewingDoc.value = type
  try {
    const resp = await axios.get(url, {
      headers: authHeaders(),
      responseType: 'blob',
    })
    const mime   = resp.headers['content-type'] ?? 'application/octet-stream'
    const blob   = new Blob([resp.data], { type: mime })
    const objUrl = URL.createObjectURL(blob)
    window.open(objUrl, '_blank')
    setTimeout(() => URL.revokeObjectURL(objUrl), 60000)
  } catch (e) {
    alert('Could not open the document. It may not be available.')
  } finally {
    viewingDoc.value = null
  }
}

async function load() {
  if (!props.applicationId) return
  loading.value = true
  error.value   = null
  profile.value = null
  try {
    const { data } = await axios.get(
      `/api/hrmpsb/applications/${props.applicationId}/profile`,
      { headers: authHeaders() }
    )
    profile.value = data
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Unable to load applicant profile.'
  } finally {
    loading.value = false
  }
}

watch(
  () => [props.modelValue, props.applicationId],
  ([open]) => { if (open) load() },
  { immediate: true }
)
</script>

<style scoped>
@reference "../../../css/app.css";
.section-heading {
  @apply text-xs font-bold uppercase tracking-wider text-gray-400 mb-2;
}
.credential-card {
  @apply bg-gray-50 border border-gray-200 rounded-lg px-4 py-3;
}
</style>
