<template>
  <AdminLayout title="Applications">

    <!-- Filters -->
    <div class="flex flex-wrap gap-2 mb-6">
      <input
        v-model="filters.search"
        @input="onSearch"
        type="text"
        placeholder="Search applicant or position..."
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none w-60" />
      <select
        v-model="filters.status"
        @change="fetchApplications"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
        <option value="">All Status</option>
        <option value="submitted">Submitted</option>
        <option value="under_review">Under Review</option>
        <option value="screened">Screened</option>
        <option value="qualified">Qualified</option>
        <option value="disqualified">Disqualified</option>
        <option value="shortlisted">Shortlisted</option>
        <option value="for_interview">For Interview</option>
        <option value="recommended">Recommended</option>
        <option value="appointed">Appointed</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-8 space-y-3">
        <div v-for="n in 6" :key="n" class="h-12 bg-gray-100 rounded animate-pulse"></div>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3">Applicant</th>
            <th class="px-5 py-3">Position</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3">Submitted</th>
            <th class="px-5 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="app in applications" :key="app.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3.5 font-medium text-gray-900">{{ app.applicant?.user?.name ?? '—' }}</td>
            <td class="px-5 py-3.5 text-gray-600 max-w-[200px] truncate">{{ app.vacancy?.position_title ?? '—' }}</td>
            <td class="px-5 py-3.5"><StatusBadge :status="app.status" /></td>
            <td class="px-5 py-3.5 text-gray-400 whitespace-nowrap">{{ formatDate(app.submitted_at) }}</td>
            <td class="px-5 py-3.5 text-right">
              <button @click="openUpdate(app)" class="text-xs font-medium text-[#2a338f] hover:underline">
                Update Status
              </button>
            </td>
          </tr>
          <tr v-if="!applications.length">
            <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">No applications found.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span>Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}</span>
        <div class="flex gap-2">
          <button :disabled="meta.current_page === 1" @click="goPage(meta.current_page - 1)"
            class="px-3 py-1 rounded border border-gray-300 disabled:opacity-40 hover:bg-gray-50">Prev</button>
          <button :disabled="meta.current_page === meta.last_page" @click="goPage(meta.current_page + 1)"
            class="px-3 py-1 rounded border border-gray-300 disabled:opacity-40 hover:bg-gray-50">Next</button>
        </div>
      </div>
    </div>

    <!-- Update Status Modal -->
    <div v-if="updateTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="updateTarget = null"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-1">Update Application Status</h3>
        <p class="text-sm text-gray-500 mb-5">
          {{ updateTarget.applicant?.user?.name ?? 'Applicant' }} —
          <em>{{ updateTarget.vacancy?.position_title ?? 'Position' }}</em>
        </p>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Status</label>
            <select v-model="statusForm.status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
              <option value="under_review">Under Review</option>
              <option value="screened">Screened</option>
              <option value="qualified">Qualified</option>
              <option value="disqualified">Disqualified</option>
              <option value="shortlisted">Shortlisted</option>
              <option value="for_interview">For Interview</option>
              <option value="recommended">Recommended</option>
              <option value="appointed">Appointed</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Remarks <span class="text-gray-400 font-normal">(optional)</span></label>
            <textarea v-model="statusForm.remarks" rows="3" placeholder="Add notes or feedback for the applicant..."
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none resize-none"></textarea>
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button @click="updateTarget = null" class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="doUpdateStatus" :disabled="saving" class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-60">
            {{ saving ? 'Saving…' : 'Save Status' }}
          </button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { debounce } from 'lodash-es'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

const loading      = ref(true)
const saving       = ref(false)
const applications = ref([])
const meta         = ref({})
const updateTarget = ref(null)

const filters    = reactive({ search: '', status: '', page: 1 })
const statusForm = reactive({ status: '', remarks: '' })

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function fetchApplications() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/applications', {
      params: { status: filters.status || undefined, page: filters.page },
      headers: authHeaders(),
    })
    applications.value = data.data ?? data
    meta.value         = data.meta ?? {}
  } finally {
    loading.value = false
  }
}

const onSearch = debounce(() => { filters.page = 1; fetchApplications() }, 350)
function goPage(p) { filters.page = p; fetchApplications() }

function openUpdate(app) {
  updateTarget.value  = app
  statusForm.status   = app.status
  statusForm.remarks  = app.remarks ?? ''
}

async function doUpdateStatus() {
  saving.value = true
  try {
    await axios.patch(`/api/applications/${updateTarget.value.id}/status`, statusForm, { headers: authHeaders() })
    updateTarget.value = null
    fetchApplications()
  } finally {
    saving.value = false
  }
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(fetchApplications)
</script>
