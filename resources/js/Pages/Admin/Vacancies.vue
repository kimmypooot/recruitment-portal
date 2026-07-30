<template>
  <AdminLayout title="Vacancies">

    <!-- Status tabs -->
    <div class="flex flex-wrap gap-1.5 mb-4">
      <button v-for="tab in statusTabs" :key="tab.value"
        @click="filters.status = tab.value; resetAndFetch()"
        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors"
        :class="filters.status === tab.value
          ? 'bg-primary text-white shadow-sm'
          : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
        {{ tab.label }}
        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold"
          :class="filters.status === tab.value ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'">
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
      <div class="flex gap-2 flex-wrap">
        <input v-model="filters.search" @input="onSearch" type="text"
          placeholder="Search position..."
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none w-52" />
        <select v-model="filters.salary_grade" @change="resetAndFetch"
          class="px-3 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
          <option value="">All Salary Grades</option>
          <option v-for="n in 33" :key="n" :value="n">SG-{{ n }}</option>
        </select>
        <select v-model="filters.sort" @change="resetAndFetch"
          class="px-3 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
          <option value="">Newest Created</option>
          <option value="newest">Newest Published</option>
          <option value="deadline_desc">Deadline (Latest First)</option>
          <option value="sg_desc">Salary Grade (High → Low)</option>
          <option value="sg_asc">Salary Grade (Low → High)</option>
        </select>
      </div>
      <button @click="openCreate"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
        <Icon name="plus" size="4" />
        New Vacancy
      </button>
    </div>

    <!-- Bulk action bar -->
    <div v-if="selectedIds.length" class="flex items-center gap-3 flex-wrap px-5 py-3 bg-primary/5 border border-primary/20 rounded-lg mb-4">
      <span class="text-sm font-medium text-gray-700">{{ selectedIds.length }} selected</span>
      <div class="flex-1"></div>
      <select v-model="bulkStatus"
        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-primary focus:outline-none">
        <option value="">Change status to…</option>
        <option value="draft">Draft</option>
        <option value="published">Published</option>
        <option value="closed">Closed</option>
        <option value="filled">Filled</option>
        <option value="archived">Archived</option>
      </select>
      <button @click="bulkApply" :disabled="!bulkStatus || bulkLoading"
        class="px-4 py-1.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark disabled:opacity-50 rounded-lg transition-colors">
        <span v-if="bulkLoading" class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
          Applying…
        </span>
        <span v-else>Apply</span>
      </button>
      <button @click="selectedIds = []"
        class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
        Clear
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <SkeletonLoader v-if="loading" variant="table-row" :count="5" wrapper-class="p-8 space-y-3" />

      <div v-else class="overflow-x-auto">
      <table class="w-full min-w-[700px] text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3 w-10">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll"
                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
            </th>
            <th class="px-5 py-3">Position</th>
            <th class="px-5 py-3">SG</th>
            <th class="px-5 py-3">Office</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3">Published</th>
            <th class="px-5 py-3">Deadline</th>
            <th class="px-5 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="v in vacancies" :key="v.id" class="hover:bg-gray-50 transition-colors"
            :class="selectedIds.includes(v.id) ? 'bg-primary/5' : ''">
            <td class="px-5 py-3.5">
              <input type="checkbox" :checked="selectedIds.includes(v.id)" @change="toggleSelect(v.id)"
                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
            </td>
            <td class="px-5 py-3.5 font-medium text-gray-900">{{ v.position_title }}</td>
            <td class="px-5 py-3.5 text-gray-600">SG-{{ v.salary_grade }}</td>
            <td class="px-5 py-3.5 text-gray-600 max-w-[160px] truncate">{{ v.place_of_assignment }}</td>
            <td class="px-5 py-3.5"><StatusBadge :status="v.status" /></td>
            <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">{{ formatDate(v.published_at) }}</td>
            <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">{{ formatDate(v.deadline_at) }}</td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-2">
                <button @click="openEdit(v)"
                  class="px-2.5 py-1 text-xs font-medium text-primary bg-primary/10 hover:bg-primary/20 rounded-md transition-colors">
                  Edit
                </button>
                <button @click="previewVacancy(v)" :disabled="previewLoading === v.id"
                  class="px-2.5 py-1 text-xs font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-md transition-colors inline-flex items-center gap-1 disabled:opacity-60">
                  <svg v-if="previewLoading === v.id" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  <Icon v-else name="externalLink" size="3" />
                  Preview
                </button>
                <div class="relative">
                  <button :data-dropdown-btn="v.id" @click="toggleStatusDropdown(v.id, $event)"
                    :disabled="statusLoading === v.id"
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium border border-gray-200 rounded-md bg-white hover:bg-gray-50 transition-colors disabled:opacity-60">
                    <svg v-if="statusLoading === v.id" class="w-3 h-3 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    {{ statusLabel(v.status) }}
                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-200" :class="openDropdownId === v.id ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>
                </div>
                <Teleport to="body">
                  <Transition
                    enter-active-class="transition ease-out duration-100"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95">
                    <div v-if="openDropdownId === v.id"
                      class="fixed z-[999] w-36 bg-white rounded-xl border border-gray-200 shadow-lg py-1"
                      :style="{ top: dropdownPos.top + 'px', right: dropdownPos.right + 'px' }">
                      <button v-for="opt in statusOptions" :key="opt.value"
                        @click="changeRowStatus(v, opt.value)"
                        class="w-full text-left px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors flex items-center justify-between"
                        :class="v.status === opt.value ? 'text-primary font-semibold' : ''">
                        {{ opt.label }}
                        <svg v-if="v.status === opt.value" class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                      </button>
                    </div>
                  </Transition>
                </Teleport>
                <button @click="deleteTarget = v"
                  class="px-2.5 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!vacancies.length">
            <td colspan="8" class="px-5 py-16 text-center">
              <div class="flex flex-col items-center gap-2">
                <Icon name="briefcase" size="10" class="text-gray-200" />
                <p class="text-sm font-medium text-gray-400">No vacancies found</p>
                <button v-if="hasActiveFilters" @click="clearFilters"
                  class="text-xs text-primary hover:underline">Clear filters</button>
                <button v-else @click="openCreate"
                  class="text-xs text-primary hover:underline">Create your first vacancy</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span class="text-xs">
          Showing <strong class="text-gray-700">{{ meta.from }}</strong>–<strong class="text-gray-700">{{ meta.to }}</strong>
          of <strong class="text-gray-700">{{ meta.total }}</strong>
        </span>
        <div class="flex items-center gap-1">
          <button :disabled="meta.current_page === 1" @click="goPage(meta.current_page - 1)"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <Icon name="chevronLeft" size="4" />
          </button>
          <button v-for="p in visibleVacancyPages" :key="p" @click="typeof p === 'number' && goPage(p)"
            :disabled="p === '…'"
            :class="['px-2.5 py-1 rounded-lg text-xs font-medium transition-colors',
              p === meta.current_page ? 'bg-primary text-white' : p === '…' ? 'text-gray-300 cursor-default' : 'text-gray-600 hover:bg-gray-100']">
            {{ p }}
          </button>
          <button :disabled="meta.current_page === meta.last_page" @click="goPage(meta.current_page + 1)"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <Icon name="chevronRight" size="4" />
          </button>
        </div>
      </div>
    </div>

    <VacancyFormModal
      :open="showModal"
      :vacancy="editingVacancy"
      @close="showModal = false"
      @saved="onSaved"
    />

    <DeleteVacancyModal
      :vacancy="deleteTarget"
      @close="deleteTarget = null"
      @deleted="onDeleted"
    />

    <VacancyDetailModal
      v-if="showPreview && previewVacancyData"
      :vacancy="previewVacancyData"
      :applied-ids="[]"
      preview
      @close="showPreview = false"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { debounce } from 'lodash-es'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import VacancyDetailModal from '@/Components/Vacancy/VacancyDetailModal.vue'
import VacancyFormModal from './Vacancies/VacancyFormModal.vue'
import DeleteVacancyModal from './Vacancies/DeleteVacancyModal.vue'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import Icon from '@/Components/UI/Icon.vue'
import SkeletonLoader from '@/Components/UI/SkeletonLoader.vue'
import { formatDate } from '@/utils/dates'
import { statusLabel } from '@/config/statusConfig'

const toast = useToast()
const { confirm } = useConfirm()

// ── State ────────────────────────────────────────────────────────────────────────
const loading            = ref(true)
const statusLoading       = ref(null)
const vacancies           = ref([])
const meta                = ref({})
const showModal           = ref(false)
const editingVacancy      = ref(null)
const deleteTarget        = ref(null)
const selectedIds         = ref([])
const bulkStatus          = ref('')
const bulkLoading         = ref(false)
const statusCounts        = ref({})
const openDropdownId      = ref(null)
const dropdownPos         = ref({ top: 0, right: 0 })

const statusOptions = computed(() =>
  STATUS_TAB_DEFS.filter(t => t.value !== '')
)

const STATUS_TAB_DEFS = [
  { value: '',          label: 'All' },
  { value: 'draft',     label: 'Draft' },
  { value: 'published', label: 'Published' },
  { value: 'closed',    label: 'Closed' },
  { value: 'filled',    label: 'Filled' },
  { value: 'archived',  label: 'Archived' },
]

const statusTabs = computed(() =>
  STATUS_TAB_DEFS.map(t => ({
    ...t,
    count: t.value === '' ? (statusCounts.value.all ?? 0) : (statusCounts.value[t.value] ?? 0),
  }))
)

const selectAll = computed(() =>
  vacancies.value.length > 0 && selectedIds.value.length === vacancies.value.length
)

const hasActiveFilters = computed(() =>
  !!(filters.search || filters.status || filters.salary_grade || filters.sort)
)

const visibleVacancyPages = computed(() => {
  const total = meta.value.last_page ?? 1
  const cur   = meta.value.current_page ?? 1
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  if (cur <= 4)   return [1, 2, 3, 4, 5, '…', total]
  if (cur >= total - 3) return [1, '…', total - 4, total - 3, total - 2, total - 1, total]
  return [1, '…', cur - 1, cur, cur + 1, '…', total]
})

function toggleSelectAll() {
  if (selectAll.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = vacancies.value.map(v => v.id)
  }
}

function toggleSelect(id) {
  const i = selectedIds.value.indexOf(id)
  if (i === -1) {
    selectedIds.value.push(id)
  } else {
    selectedIds.value.splice(i, 1)
  }
}

async function bulkApply() {
  if (!bulkStatus.value || !selectedIds.value.length) return
  bulkLoading.value = true
  try {
    await axios.patch('/api/vacancies/bulk-status', {
      ids: selectedIds.value,
      status: bulkStatus.value,
    }, { headers: authHeaders() })
    toast.success(`Status updated to "${bulkStatus.value}" for ${selectedIds.value.length} vacancy(ies).`)
    selectedIds.value = []
    bulkStatus.value = ''
    fetchVacancies()
    fetchStatusCounts()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Bulk update failed.')
  } finally {
    bulkLoading.value = false
  }
}

const showPreview        = ref(false)
const previewVacancyData = ref(null)
const previewLoading     = ref(null)

async function previewVacancy(v) {
  previewLoading.value = v.id
  try {
    const { data } = await axios.get(`/api/vacancies/${v.id}`, { headers: authHeaders() })
    previewVacancyData.value = data.data ?? data
    showPreview.value = true
  } catch {
    previewVacancyData.value = v
    showPreview.value = true
  } finally {
    previewLoading.value = null
  }
}

const filters = reactive({ search: '', status: '', salary_grade: '', sort: '', page: 1 })

// ── Auth ─────────────────────────────────────────────────────────────────────────
function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

// ── Vacancies CRUD ────────────────────────────────────────────────────────────────
async function fetchVacancies() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/vacancies', {
      params: {
        search: filters.search || undefined,
        status: filters.status || undefined,
        salary_grade: filters.salary_grade || undefined,
        sort: filters.sort || undefined,
        page: filters.page,
      },
      headers: authHeaders(),
    })
    vacancies.value = data.data ?? data
    meta.value      = data.meta ?? {}
  } finally {
    loading.value = false
  }
}

async function fetchStatusCounts() {
  try {
    const { data } = await axios.get('/api/vacancies/status-counts', {
      params: {
        search: filters.search || undefined,
        salary_grade: filters.salary_grade || undefined,
      },
      headers: authHeaders(),
    })
    statusCounts.value = data
  } catch {
    statusCounts.value = {}
  }
}

const onSearch = debounce(() => { resetAndFetch(); fetchStatusCounts() }, 350)

function resetAndFetch() { filters.page = 1; fetchVacancies() }
function goPage(p)       { filters.page = p; fetchVacancies() }
function clearFilters()  {
  filters.search = ''; filters.status = ''; filters.salary_grade = ''; filters.sort = ''
  resetAndFetch()
  fetchStatusCounts()
}

function toggleStatusDropdown(id, e) {
  if (openDropdownId.value === id) {
    openDropdownId.value = null
    return
  }
  const rect = e.currentTarget.getBoundingClientRect()
  dropdownPos.value = {
    top: rect.bottom + 4,
    right: window.innerWidth - rect.right,
  }
  openDropdownId.value = id
}

function handleOutsideClick(e) {
  if (!openDropdownId.value) return
  const btn = e.target.closest('[data-dropdown-btn]')
  if (!btn || +btn.dataset.dropdownBtn !== openDropdownId.value) {
    openDropdownId.value = null
  }
}

function openCreate() {
  editingVacancy.value = null
  showModal.value = true
}

function openEdit(vacancy) {
  editingVacancy.value = vacancy
  showModal.value = true
}

function onSaved() {
  showModal.value = false
  fetchVacancies()
  fetchStatusCounts()
}

function onDeleted() {
  deleteTarget.value = null
  fetchVacancies()
  fetchStatusCounts()
}

async function changeRowStatus(vacancy, newStatus) {
  const oldStatus = vacancy.status
  if (newStatus === oldStatus) return

  const label = STATUS_TAB_DEFS.find(t => t.value === newStatus)?.label ?? newStatus
  const ok = await confirm(`Change "${vacancy.position_title}" status to ${label}?`)
  if (!ok) return
  openDropdownId.value = null

  statusLoading.value = vacancy.id
  vacancy.status = newStatus

  try {
    if (newStatus === 'published') {
      await axios.patch(`/api/vacancies/${vacancy.id}/publish`, {}, { headers: authHeaders() })
    } else if (newStatus === 'archived') {
      await axios.patch(`/api/vacancies/${vacancy.id}/archive`, {}, { headers: authHeaders() })
    } else {
      await axios.patch('/api/vacancies/bulk-status', { ids: [vacancy.id], status: newStatus }, { headers: authHeaders() })
    }
    toast.success(`Vacancy "${vacancy.position_title}" status changed to ${label}.`)
    fetchStatusCounts()
  } catch (e) {
    vacancy.status = oldStatus
    toast.error(e.response?.data?.message ?? 'Status change failed.')
  } finally {
    statusLoading.value = null
  }
}

// ── Escape handler ─────────────────────────────────────────────────────────────────
function handleKeydown(e) {
  if (e.key === 'Escape') {
    showModal.value = false
    deleteTarget.value = null
    openDropdownId.value = null
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  document.addEventListener('click', handleOutsideClick)
  fetchVacancies()
  fetchStatusCounts()
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('click', handleOutsideClick)
})
</script>
