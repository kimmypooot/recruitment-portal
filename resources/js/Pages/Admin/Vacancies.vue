<template>
  <AdminLayout title="Vacancies">

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
      <div class="flex gap-2 flex-wrap">
        <input
          v-model="filters.search"
          @input="onSearch"
          type="text"
          placeholder="Search position..."
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none w-52" />
        <select
          v-model="filters.status"
          @change="fetchVacancies"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="closed">Closed</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New Vacancy
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-8 space-y-3">
        <div v-for="n in 5" :key="n" class="h-12 bg-gray-100 rounded animate-pulse"></div>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3">Position</th>
            <th class="px-5 py-3">SG</th>
            <th class="px-5 py-3">Office</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3">Deadline</th>
            <th class="px-5 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="v in vacancies" :key="v.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3.5 font-medium text-gray-900">{{ v.position_title }}</td>
            <td class="px-5 py-3.5 text-gray-600">SG-{{ v.salary_grade }}</td>
            <td class="px-5 py-3.5 text-gray-600 max-w-[160px] truncate">{{ v.place_of_assignment }}</td>
            <td class="px-5 py-3.5"><StatusBadge :status="v.status" /></td>
            <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">{{ formatDate(v.deadline_at) }}</td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-2">
                <button
                  v-if="v.status === 'draft'"
                  @click="changeStatus(v, 'publish')"
                  class="px-2.5 py-1 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-md transition-colors">
                  Publish
                </button>
                <button
                  v-if="v.status === 'published'"
                  @click="changeStatus(v, 'archive')"
                  class="px-2.5 py-1 text-xs font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 rounded-md transition-colors">
                  Archive
                </button>
                <button
                  @click="confirmDelete(v)"
                  class="px-2.5 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!vacancies.length">
            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">No vacancies found.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span>Page {{ meta.current_page }} of {{ meta.last_page }}</span>
        <div class="flex gap-2">
          <button :disabled="meta.current_page === 1" @click="goPage(meta.current_page - 1)"
            class="px-3 py-1 rounded border border-gray-300 disabled:opacity-40 hover:bg-gray-50 transition-colors">Prev</button>
          <button :disabled="meta.current_page === meta.last_page" @click="goPage(meta.current_page + 1)"
            class="px-3 py-1 rounded border border-gray-300 disabled:opacity-40 hover:bg-gray-50 transition-colors">Next</button>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <h3 class="text-base font-semibold text-gray-900">New Vacancy</h3>
          <button @click="showModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <form @submit.prevent="submitVacancy" class="px-6 py-5 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Position Title <span class="text-red-500">*</span></label>
              <input v-model="form.position_title" required type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Item Number <span class="text-red-500">*</span></label>
              <input v-model="form.item_number" required type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Salary Grade <span class="text-red-500">*</span></label>
              <select v-model="form.salary_grade" required class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
                <option value="">Select SG</option>
                <option v-for="n in 33" :key="n" :value="n">SG-{{ n }}</option>
              </select>
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Place of Assignment <span class="text-red-500">*</span></label>
              <input v-model="form.place_of_assignment" required type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Deadline <span class="text-red-500">*</span></label>
              <input v-model="form.deadline_at" required type="date" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
            </div>
            <div v-for="field in requirementFields" :key="field.key" class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ field.label }} <span class="text-red-500">*</span></label>
              <textarea v-model="form[field.key]" required rows="2" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none resize-none"></textarea>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-60 transition-colors">
              {{ saving ? 'Saving…' : 'Create Vacancy' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete confirm modal -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="deleteTarget = null"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-2">Delete Vacancy?</h3>
        <p class="text-sm text-gray-500 mb-6">
          "<strong>{{ deleteTarget.position_title }}</strong>" will be permanently removed. This cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="deleteTarget = null" class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="doDelete" :disabled="saving" class="px-4 py-2 text-sm bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-60">
            {{ saving ? 'Deleting…' : 'Delete' }}
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
const vacancies    = ref([])
const meta         = ref({})
const showModal    = ref(false)
const deleteTarget = ref(null)

const filters = reactive({ search: '', status: '', page: 1 })
const form    = reactive({
  position_title: '', item_number: '', salary_grade: '',
  place_of_assignment: '', deadline_at: '',
  education_req: '', experience_req: '', training_req: '', eligibility_req: '',
})

const requirementFields = [
  { key: 'education_req',   label: 'Education Requirement' },
  { key: 'experience_req',  label: 'Experience Requirement' },
  { key: 'training_req',    label: 'Training Requirement' },
  { key: 'eligibility_req', label: 'Eligibility Requirement' },
]

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function fetchVacancies() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/vacancies', {
      params: { search: filters.search || undefined, status: filters.status || undefined, page: filters.page },
      headers: authHeaders(),
    })
    vacancies.value = data.data ?? data
    meta.value      = data.meta ?? {}
  } finally {
    loading.value = false
  }
}

const onSearch = debounce(() => { filters.page = 1; fetchVacancies() }, 350)

function goPage(p) { filters.page = p; fetchVacancies() }

function openCreate() {
  Object.keys(form).forEach(k => (form[k] = ''))
  showModal.value = true
}

async function submitVacancy() {
  saving.value = true
  try {
    await axios.post('/api/vacancies', form, { headers: authHeaders() })
    showModal.value = false
    fetchVacancies()
  } finally {
    saving.value = false
  }
}

async function changeStatus(vacancy, action) {
  await axios.patch(`/api/vacancies/${vacancy.id}/${action}`, {}, { headers: authHeaders() })
  fetchVacancies()
}

function confirmDelete(v) { deleteTarget.value = v }

async function doDelete() {
  saving.value = true
  try {
    await axios.delete(`/api/vacancies/${deleteTarget.value.id}`, { headers: authHeaders() })
    deleteTarget.value = null
    fetchVacancies()
  } finally {
    saving.value = false
  }
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(fetchVacancies)
</script>
