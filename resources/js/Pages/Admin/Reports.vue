<template>
  <AdminLayout title="Reports">
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="text-xl font-bold text-gray-900">Reports & Exports</h1>
        <p class="text-sm text-gray-500 mt-1">Generate and download recruitment reports.</p>
      </div>

      <!-- Vacancy filter (used by applicable reports) -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Vacancy (optional)</label>
        <select v-model="selectedVacancyId" class="w-full sm:w-80 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
          <option value="">All vacancies</option>
          <option v-for="v in vacancies" :key="v.id" :value="v.id">{{ v.position_title }} ({{ v.status }})</option>
        </select>
      </div>

      <!-- Report cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <div v-for="report in reports" :key="report.type"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col gap-4">
          <div class="flex items-start gap-3">
            <div :class="report.iconBg" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5" :class="report.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" :d="report.icon"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ report.label }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ report.description }}</p>
            </div>
          </div>

          <!-- Preview table -->
          <div v-if="previews[report.type]" class="overflow-x-auto max-h-48 overflow-y-auto border border-gray-100 rounded-lg">
            <table class="min-w-full text-xs">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th v-for="col in previewCols(report.type)" :key="col"
                    class="px-3 py-1.5 text-left font-medium text-gray-500 whitespace-nowrap">{{ col }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(row, i) in previews[report.type]" :key="i" class="hover:bg-gray-50">
                  <td v-for="col in previewCols(report.type)" :key="col"
                    class="px-3 py-1.5 text-gray-700 whitespace-nowrap">{{ row[colKey(report.type, col)] ?? '—' }}</td>
                </tr>
                <tr v-if="!previews[report.type].length">
                  <td :colspan="previewCols(report.type).length" class="px-3 py-4 text-center text-gray-400">No data.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex gap-2 mt-auto">
            <button @click="loadPreview(report.type)"
              :disabled="loading[report.type]"
              class="flex-1 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 transition">
              {{ loading[report.type] ? 'Loading…' : 'Preview' }}
            </button>
            <button @click="exportJson(report.type)"
              :disabled="!previews[report.type]"
              class="flex-1 px-3 py-1.5 text-xs font-medium bg-[#2a338f] text-white rounded-lg hover:bg-[#1e2570] disabled:opacity-50 transition">
              Export JSON
            </button>
          </div>
        </div>
      </div>

      <!-- CS Form Generator -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">CS Form Generator</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Application (Appointed)</label>
            <select v-model="formApp" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
              <option value="">Select application…</option>
              <option v-for="a in appointedApps" :key="a.id" :value="a.id">
                {{ a.applicant_name }} — {{ a.position }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Form Type</label>
            <select v-model="formType" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
              <option value="">Select form…</option>
              <option value="33A">CS Form 33-A (Permanent Appointment)</option>
              <option value="33B">CS Form 33-B (Casual/Contractual)</option>
              <option value="form1">CS Form 1 — Personal Data Sheet (2025)</option>
            </select>
          </div>
          <div class="flex items-end">
            <button @click="generateForm" :disabled="!formApp || !formType || generating"
              class="w-full px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-50 transition">
              {{ generating ? 'Generating…' : 'Generate PDF' }}
            </button>
          </div>
        </div>
        <p v-if="formMessage" :class="formError ? 'text-red-600' : 'text-green-700'" class="mt-2 text-sm">{{ formMessage }}</p>

        <!-- Generated forms list -->
        <div v-if="generatedForms.length" class="mt-4 border border-gray-100 rounded-lg overflow-hidden">
          <table class="min-w-full text-xs">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-gray-500 font-medium">Form</th>
                <th class="px-4 py-2 text-left text-gray-500 font-medium">Generated</th>
                <th class="px-4 py-2 text-left text-gray-500 font-medium">Signed</th>
                <th class="px-4 py-2 text-left text-gray-500 font-medium">Submitted</th>
                <th class="px-4 py-2 text-right text-gray-500 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="f in generatedForms" :key="f.id" class="hover:bg-gray-50">
                <td class="px-4 py-2 font-medium text-gray-800">{{ f.form_label }}</td>
                <td class="px-4 py-2 text-gray-500">{{ formatDate(f.generated_at) }}</td>
                <td class="px-4 py-2">
                  <span v-if="f.signed_at" class="text-green-600 font-medium">✓ Signed</span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-4 py-2">
                  <span v-if="f.submitted_to_csc_at" class="text-green-600 font-medium">✓ Submitted</span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-4 py-2 text-right">
                  <div class="flex gap-2 justify-end">
                    <a :href="`/api/forms/${f.id}/download`" target="_blank"
                      class="px-2.5 py-1 text-xs font-medium text-[#2a338f] bg-[#2a338f]/10 hover:bg-[#2a338f]/20 rounded-md transition">Download</a>
                    <button v-if="!f.submitted_to_csc_at" @click="markSubmitted(f.id)"
                      class="px-2.5 py-1 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-md transition">Mark Submitted</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const vacancies      = ref([])
const appointedApps  = ref([])
const selectedVacancyId = ref('')
const previews       = ref({})
const loading        = ref({})
const error          = ref('')
const formApp        = ref('')
const formType       = ref('')
const generating     = ref(false)
const formMessage    = ref('')
const formError      = ref(false)
const generatedForms = ref([])

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

const reports = [
  {
    type: 'qualified-list',
    label: 'Qualified Applicants',
    description: 'All applicants who passed QS screening.',
    icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    iconBg: 'bg-green-100', iconColor: 'text-green-700',
  },
  {
    type: 'comparative-assessment',
    label: 'Comparative Assessment',
    description: 'QS, exam scores, and BEI ratings side by side.',
    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    iconBg: 'bg-blue-100', iconColor: 'text-blue-700',
  },
  {
    type: 'appointment-report',
    label: 'Appointment Report',
    description: 'All issued appointments with position and SG.',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    iconBg: 'bg-purple-100', iconColor: 'text-purple-700',
  },
  {
    type: 'pipeline-summary',
    label: 'Pipeline Summary',
    description: 'Applications and vacancies by status.',
    icon: 'M4 6h16M4 10h16M4 14h16M4 18h16',
    iconBg: 'bg-amber-100', iconColor: 'text-amber-700',
  },
  {
    type: 'compliance-deadlines',
    label: 'Compliance Deadlines',
    description: '30-day CSC submission deadlines and status.',
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    iconBg: 'bg-red-100', iconColor: 'text-red-700',
  },
]

const COL_MAPS = {
  'qualified-list':         { 'Applicant': 'applicant', 'Position': 'position', 'Eligibility': 'eligibility', 'Status': 'status' },
  'comparative-assessment': { 'Token': 'token', 'Name': 'name', 'QS': 'qs', 'TWE': 'twe', 'CBWE': 'cbwe', 'BEI Avg': 'bei_avg', 'Status': 'status' },
  'appointment-report':     { 'Appointee': 'appointee', 'Position': 'position', 'SG': 'salary_grade', 'Date': 'appointed_at' },
  'pipeline-summary':       { 'Status': 'status', 'Count': 'count' },
  'compliance-deadlines':   { 'Appointee': 'appointee', 'Position': 'position', 'Due': 'due_at', 'Days Left': 'days_remaining', 'Status': 'status' },
}

function previewCols(type) { return Object.keys(COL_MAPS[type] ?? {}) }
function colKey(type, col) { return COL_MAPS[type]?.[col] ?? col.toLowerCase() }

async function loadPreview(type) {
  loading.value[type] = true
  try {
    const params = {}
    if (selectedVacancyId.value) params.vacancy_id = selectedVacancyId.value
    const { data } = await axios.get(`/api/reports/${type}`, { headers: authHeaders(), params })
    previews.value[type] = data.rows ?? data.pipeline ?? []
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load report.'
  } finally {
    loading.value[type] = false
  }
}

function exportJson(type) {
  const data = previews.value[type]
  if (!data) return
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url  = URL.createObjectURL(blob)
  const a    = document.createElement('a')
  a.href = url
  a.download = `${type}-${new Date().toISOString().slice(0, 10)}.json`
  a.click()
  URL.revokeObjectURL(url)
}

async function loadVacancies() {
  const { data } = await axios.get('/api/vacancies', { headers: authHeaders() })
  vacancies.value = data.data ?? data
}

async function loadAppointedApps() {
  try {
    const { data } = await axios.get('/api/applications', { headers: authHeaders(), params: { status: 'appointed' } })
    const apps = data.data ?? data
    appointedApps.value = apps.map(a => ({
      id:             a.id,
      applicant_name: a.applicant ? `${a.applicant.first_name ?? ''} ${a.applicant.last_name ?? ''}`.trim() : '—',
      position:       a.vacancy?.position_title ?? '—',
    }))
  } catch { /* non-critical */ }
}

async function loadGeneratedForms() {
  if (!formApp.value) return
  try {
    const { data } = await axios.get(`/api/applications/${formApp.value}/forms`, { headers: authHeaders() })
    generatedForms.value = data.forms ?? []
  } catch { generatedForms.value = [] }
}

async function generateForm() {
  generating.value = true
  formMessage.value = ''
  formError.value = false
  try {
    const { data } = await axios.post(`/api/applications/${formApp.value}/forms`, { form_type: formType.value }, { headers: authHeaders() })
    formMessage.value = data.message
    await loadGeneratedForms()
  } catch (e) {
    formMessage.value = e.response?.data?.message ?? 'Failed to generate form.'
    formError.value = true
  } finally {
    generating.value = false
  }
}

async function markSubmitted(formId) {
  await axios.patch(`/api/forms/${formId}/mark-submitted`, {}, { headers: authHeaders() })
  await loadGeneratedForms()
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

watch(formApp, loadGeneratedForms)

onMounted(() => { loadVacancies(); loadAppointedApps() })
</script>
