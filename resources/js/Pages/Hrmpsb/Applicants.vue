<template>
  <HrmbsboardLayout title="Applicants &amp; Documents">
    <div v-if="loading" class="space-y-4">
      <div v-for="n in 5" :key="n" class="h-14 bg-white rounded-xl border border-gray-200 animate-pulse"></div>
    </div>

    <template v-else-if="error">
      <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-center">
        <p class="text-sm font-semibold text-red-700">{{ error }}</p>
      </div>
    </template>

    <template v-else>
      <a :href="`/hrmpsb/dashboard`"
        class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-[#1a5276] mb-4 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Dashboard
      </a>

      <!-- Vacancy header -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-[#1a5276] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
            SG-{{ vacancy.salary_grade }}
          </div>
          <div class="min-w-0">
            <h2 class="text-sm font-semibold text-gray-900">{{ vacancy.position_title }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ vacancy.plantilla_no }} &middot; {{ vacancy.place_of_assignment }}</p>
          </div>
          <span class="ml-auto text-xs text-gray-400 font-medium">{{ applicants.length }} applicant{{ applicants.length !== 1 ? 's' : '' }}</span>
        </div>
      </div>

      <!-- Applicants table -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
              <th class="px-5 py-3">#</th>
              <th class="px-5 py-3">Applicant ID</th>
              <th class="px-5 py-3">Status</th>
              <th class="px-5 py-3 text-center" v-for="doc in docTypes" :key="doc.key">{{ doc.label }}</th>
              <th class="px-5 py-3 text-right">Submitted</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(app, i) in applicants" :key="app.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3.5 text-gray-400 text-xs">{{ i + 1 }}</td>
              <td class="px-5 py-3.5 font-medium text-gray-900">{{ app.anonymized_name }}</td>
              <td class="px-5 py-3.5"><StatusBadge :status="app.status" /></td>
              <td class="px-5 py-3.5 text-center" v-for="doc in docTypes" :key="doc.key">
                <button v-if="app.document_links[doc.key]"
                  @click="openDoc(app.document_links[doc.key])"
                  class="inline-flex items-center justify-center w-7 h-7 rounded-full transition-colors"
                  :class="docBtnClass(doc.key)"
                  :title="'Open ' + doc.label">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                  </svg>
                </button>
                <span v-else class="inline-flex items-center justify-center w-7 h-7 rounded-full text-gray-300 bg-gray-50">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </span>
              </td>
              <td class="px-5 py-3.5 text-right text-xs text-gray-400 whitespace-nowrap">{{ formatDate(app.submitted_at) }}</td>
            </tr>
            <tr v-if="!applicants.length">
              <td :colspan="3 + docTypes.length + 1" class="px-5 py-12 text-center text-sm text-gray-400">
                No applicants for this position.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Doc legend -->
      <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-400">
        <span v-for="doc in docTypes" :key="doc.key" class="inline-flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full" :class="doc.color"></span>
          {{ doc.label }}
        </span>
      </div>
    </template>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

const props = defineProps({ vacancyId: Number })

const loading   = ref(true)
const error     = ref('')
const vacancy   = ref({})
const applicants = ref([])

const docTypes = [
  { key: 'pds',        label: 'PDS',        color: 'bg-blue-500' },
  { key: 'app_letter', label: 'App Letter', color: 'bg-purple-500' },
  { key: 'tor',        label: 'TOR',        color: 'bg-orange-500' },
  { key: 'ipcr',       label: 'IPCR',       color: 'bg-teal-500' },
  { key: 'coe',        label: 'COE',        color: 'bg-green-500' },
]

const btnClasses = {
  pds:        'text-blue-600 bg-blue-50 hover:bg-blue-100',
  app_letter: 'text-purple-600 bg-purple-50 hover:bg-purple-100',
  tor:        'text-orange-600 bg-orange-50 hover:bg-orange-100',
  ipcr:       'text-teal-600 bg-teal-50 hover:bg-teal-100',
  coe:        'text-green-600 bg-green-50 hover:bg-green-100',
}

function docBtnClass(key) {
  return btnClasses[key] ?? 'text-green-600 bg-green-50 hover:bg-green-100'
}

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

async function openDoc(url) {
  try {
    const { data, headers } = await axios.get(url, { headers: authHeaders(), responseType: 'blob' })
    const blob = new Blob([data], { type: headers['content-type'] })
    const blobUrl = URL.createObjectURL(blob)
    window.open(blobUrl, '_blank')
    setTimeout(() => URL.revokeObjectURL(blobUrl), 60000)
  } catch {
    // silently fail
  }
}

onMounted(async () => {
  try {
    const { data } = await axios.get(`/api/hrmpsb/vacancies/${props.vacancyId}/applicants`, { headers: authHeaders() })
    vacancy.value   = data.vacancy
    applicants.value = data.applicants
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load applicants.'
  } finally {
    loading.value = false
  }
})
</script>
