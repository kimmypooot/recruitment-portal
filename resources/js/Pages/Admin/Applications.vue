<template>
  <AdminLayout title="Applications">

    <!-- Stats row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="stat in statCards" :key="stat.label"
        class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4 flex items-center gap-4">
        <div :class="stat.iconBg" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5" :class="stat.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon"/>
          </svg>
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium">{{ stat.label }}</p>
          <p class="text-2xl font-bold text-gray-900 mt-0.5">
            <span v-if="loading" class="inline-block h-6 w-8 bg-gray-200 rounded animate-pulse"></span>
            <span v-else>{{ stat.value }}</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
      <div class="flex flex-1 gap-2 flex-wrap">

        <!-- Search -->
        <div class="relative flex-1 min-w-48">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="filters.search" @input="onSearch" type="text"
            placeholder="Search applicant or position…"
            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" />
        </div>

        <!-- Status filter -->
        <select v-model="filters.status" @change="resetAndFetch"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
          <option value="">All Statuses</option>
          <optgroup label="Initial">
            <option value="submitted">Submitted</option>
            <option value="under_review">Under Review</option>
          </optgroup>
          <optgroup label="Screening">
            <option value="screened">Screened</option>
            <option value="qualified">Qualified</option>
            <option value="disqualified">Disqualified</option>
          </optgroup>
          <optgroup label="Selection">
            <option value="shortlisted">Shortlisted</option>
            <option value="for_interview">For Interview</option>
            <option value="recommended">Recommended</option>
          </optgroup>
          <optgroup label="Final">
            <option value="appointed">Appointed</option>
            <option value="completed">Completed</option>
            <option value="withdrawn">Withdrawn</option>
          </optgroup>
        </select>

        <span v-if="!loading" class="text-xs text-gray-400 self-center">
          {{ meta.total ?? 0 }} result{{ meta.total !== 1 ? 's' : '' }}
        </span>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

      <!-- Loading skeleton -->
      <div v-if="loading" class="p-6 space-y-3">
        <div v-for="n in 6" :key="n" class="flex items-center gap-4 h-14">
          <div class="w-9 h-9 rounded-full bg-gray-100 animate-pulse flex-shrink-0"></div>
          <div class="flex-1 space-y-1.5">
            <div class="h-3.5 bg-gray-100 rounded w-1/4 animate-pulse"></div>
            <div class="h-3 bg-gray-100 rounded w-1/3 animate-pulse"></div>
          </div>
          <div class="h-3.5 bg-gray-100 rounded w-40 animate-pulse hidden md:block"></div>
          <div class="h-5 w-20 bg-gray-100 rounded-full animate-pulse"></div>
          <div class="h-3 w-16 bg-gray-100 rounded animate-pulse"></div>
          <div class="h-7 w-24 bg-gray-100 rounded animate-pulse"></div>
        </div>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3">Applicant</th>
            <th class="px-5 py-3 hidden md:table-cell">Position</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3 hidden lg:table-cell">Submitted</th>
            <th class="px-5 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="app in applications" :key="app.id"
            class="hover:bg-gray-50/80 transition-colors group">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#2a338f]/10 flex items-center justify-center text-[#2a338f] text-xs font-bold flex-shrink-0">
                  {{ initials(app.applicant?.user?.name) }}
                </div>
                <div class="min-w-0">
                  <p class="font-medium text-gray-900 truncate">{{ app.applicant?.user?.name ?? '—' }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ app.applicant?.user?.email ?? '' }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3.5 hidden md:table-cell">
              <p class="text-gray-800 truncate max-w-[220px]">{{ app.vacancy?.position_title ?? '—' }}</p>
              <p class="text-xs text-gray-400 mt-0.5">
                <span v-if="app.vacancy?.salary_grade" class="font-medium text-gray-500">SG-{{ app.vacancy.salary_grade }}</span>
                <span v-if="app.vacancy?.place_of_assignment" class="ml-1">· {{ app.vacancy.place_of_assignment }}</span>
              </p>
            </td>
            <td class="px-5 py-3.5">
              <StatusBadge :status="app.status" />
            </td>
            <td class="px-5 py-3.5 text-xs text-gray-400 hidden lg:table-cell whitespace-nowrap">
              {{ formatDate(app.submitted_at ?? app.created_at) }}
            </td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="openDetail(app)"
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  View
                </button>
                <button @click="openUpdate(app)"
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-[#2a338f] bg-[#2a338f]/8 hover:bg-[#2a338f]/15 rounded-md transition-colors">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  Status
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!applications.length">
            <td colspan="5" class="px-5 py-16 text-center">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm font-medium text-gray-400">No applications found</p>
                <button v-if="filters.search || filters.status" @click="clearFilters"
                  class="text-xs text-[#2a338f] hover:underline">Clear filters</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="!loading && meta.last_page > 1"
        class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span class="text-xs">
          Showing <strong class="text-gray-700">{{ meta.from }}</strong>–<strong class="text-gray-700">{{ meta.to }}</strong>
          of <strong class="text-gray-700">{{ meta.total }}</strong>
        </span>
        <div class="flex items-center gap-1">
          <button :disabled="meta.current_page === 1" @click="goPage(meta.current_page - 1)"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
          </button>
          <span class="px-3 py-1 text-xs font-medium text-gray-700">
            {{ meta.current_page }} / {{ meta.last_page }}
          </span>
          <button :disabled="meta.current_page === meta.last_page" @click="goPage(meta.current_page + 1)"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- ── Detail Drawer ──────────────────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="detailTarget" class="fixed inset-0 z-50 flex justify-end">
      <div class="absolute inset-0 bg-black/40" @click="detailTarget = null"></div>
      <div class="relative bg-white w-full max-w-md h-full flex flex-col shadow-2xl overflow-hidden">

        <!-- Drawer header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#2a338f]/10 flex items-center justify-center text-[#2a338f] text-sm font-bold flex-shrink-0">
              {{ initials(detailTarget.applicant?.user?.name) }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ detailTarget.applicant?.user?.name ?? '—' }}</p>
              <p class="text-xs text-gray-400">{{ detailTarget.applicant?.user?.email ?? '' }}</p>
            </div>
          </div>
          <button @click="detailTarget = null" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Drawer body -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

          <!-- Current status -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Current Status</p>
            <StatusBadge :status="detailTarget.status" />
          </div>

          <!-- Position info -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Applied Position</p>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-sm font-semibold text-gray-900">{{ detailTarget.vacancy?.position_title ?? '—' }}</p>
              <p class="text-xs text-gray-500 mt-0.5">
                <span v-if="detailTarget.vacancy?.salary_grade">SG-{{ detailTarget.vacancy.salary_grade }}</span>
                <span v-if="detailTarget.vacancy?.place_of_assignment"> · {{ detailTarget.vacancy.place_of_assignment }}</span>
              </p>
            </div>
          </div>

          <!-- Timeline -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Application Timeline</p>
            <div class="space-y-1">
              <div v-for="step in statusPipeline" :key="step.key"
                :class="[
                  'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                  detailTarget.status === step.key ? 'bg-[#2a338f]/5 border border-[#2a338f]/20' : ''
                ]">
                <div :class="[
                    'w-2 h-2 rounded-full flex-shrink-0',
                    detailTarget.status === step.key ? 'bg-[#2a338f]' :
                    isPast(step.key, detailTarget.status) ? 'bg-gray-300' : 'bg-gray-100'
                  ]">
                </div>
                <span :class="detailTarget.status === step.key ? 'font-medium text-[#2a338f]' : 'text-gray-400'">
                  {{ step.label }}
                </span>
                <span v-if="detailTarget.status === step.key" class="ml-auto text-xs text-[#2a338f] font-medium">Current</span>
              </div>
            </div>
          </div>

          <!-- Remarks -->
          <div v-if="detailTarget.remarks">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Remarks</p>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ detailTarget.remarks }}</p>
          </div>

          <!-- Dates -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Dates</p>
            <dl class="space-y-1.5">
              <div class="flex gap-3 text-xs">
                <dt class="w-28 text-gray-400 font-medium">Submitted</dt>
                <dd class="text-gray-700">{{ formatDate(detailTarget.submitted_at ?? detailTarget.created_at) }}</dd>
              </div>
              <div v-if="detailTarget.reviewed_at" class="flex gap-3 text-xs">
                <dt class="w-28 text-gray-400 font-medium">Last Updated</dt>
                <dd class="text-gray-700">{{ formatDate(detailTarget.reviewed_at) }}</dd>
              </div>
            </dl>
          </div>

        </div>

        <!-- Drawer footer -->
        <div class="px-6 py-4 border-t border-gray-100 flex-shrink-0">
          <button @click="openUpdate(detailTarget); detailTarget = null"
            class="w-full py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors">
            Update Status
          </button>
        </div>
      </div>
    </div>
    </Teleport>

    <!-- ── Update Status Modal ────────────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="updateTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="updateTarget = null"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col" style="max-height: 90vh;">

        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <h3 class="text-base font-semibold text-gray-900">Update Application Status</h3>
          <p class="text-xs text-gray-400 mt-1">
            {{ updateTarget.applicant?.user?.name ?? 'Applicant' }}
            <span class="mx-1 text-gray-300">·</span>
            {{ updateTarget.vacancy?.position_title ?? 'Position' }}
          </p>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">

          <!-- Current status indicator -->
          <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
            <span class="text-xs text-gray-500 font-medium">Current:</span>
            <StatusBadge :status="updateTarget.status" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Status <span class="text-red-500">*</span></label>
            <select v-model="statusForm.status"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
              <optgroup label="Initial">
                <option value="under_review">Under Review</option>
              </optgroup>
              <optgroup label="Screening">
                <option value="screened">Screened</option>
                <option value="qualified">Qualified</option>
                <option value="disqualified">Disqualified</option>
              </optgroup>
              <optgroup label="Selection">
                <option value="exam_scheduled">Exam Scheduled</option>
                <option value="shortlisted">Shortlisted</option>
                <option value="for_interview">For Interview</option>
                <option value="recommended">Recommended</option>
              </optgroup>
              <optgroup label="Final">
                <option value="appointed">Appointed</option>
                <option value="completed">Completed</option>
                <option value="withdrawn">Withdrawn</option>
              </optgroup>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Remarks
              <span class="text-gray-400 font-normal">(optional — visible to applicant)</span>
            </label>
            <textarea v-model="statusForm.remarks" rows="3"
              placeholder="Add notes or feedback for the applicant…"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none resize-none"></textarea>
          </div>

        </div>

        <!-- Footer -->
        <div class="flex justify-between gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl flex-shrink-0">
          <button @click="updateTarget = null"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <button @click="doUpdateStatus" :disabled="saving"
            class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-60 transition-colors">
            <span v-if="saving" class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              Saving…
            </span>
            <span v-else>Save Status</span>
          </button>
        </div>

      </div>
    </div>
    </Teleport>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { debounce } from 'lodash-es'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

// ── State ────────────────────────────────────────────────────────────────────────
const loading      = ref(true)
const saving       = ref(false)
const applications = ref([])
const meta         = ref({})
const updateTarget = ref(null)
const detailTarget = ref(null)

const filters    = reactive({ search: '', status: '', page: 1 })
const statusForm = reactive({ status: '', remarks: '' })

// ── Status pipeline (for drawer timeline) ───────────────────────────────────────
const statusPipeline = [
  { key: 'submitted',     label: 'Submitted' },
  { key: 'under_review',  label: 'Under Review' },
  { key: 'screened',      label: 'Screened' },
  { key: 'qualified',     label: 'Qualified' },
  { key: 'exam_scheduled', label: 'Exam Scheduled' },
  { key: 'shortlisted',   label: 'Shortlisted' },
  { key: 'for_interview', label: 'For Interview' },
  { key: 'recommended',   label: 'Recommended' },
  { key: 'appointed',     label: 'Appointed' },
  { key: 'completed',     label: 'Completed' },
]

const pipelineOrder = statusPipeline.map(s => s.key)
function isPast(key, currentStatus) {
  return pipelineOrder.indexOf(key) < pipelineOrder.indexOf(currentStatus)
}

// ── Stats ────────────────────────────────────────────────────────────────────────
const statCards = computed(() => [
  {
    label: 'Total Applications', value: meta.value.total ?? 0,
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    iconBg: 'bg-[#2a338f]/10', iconColor: 'text-[#2a338f]',
  },
  {
    label: 'Pending Review', value: applications.value.filter(a => a.status === 'submitted').length,
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    iconBg: 'bg-yellow-50', iconColor: 'text-yellow-600',
  },
  {
    label: 'Under Review', value: applications.value.filter(a => a.status === 'under_review').length,
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    iconBg: 'bg-blue-50', iconColor: 'text-blue-600',
  },
  {
    label: 'Appointed', value: applications.value.filter(a => a.status === 'appointed').length,
    icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
    iconBg: 'bg-green-50', iconColor: 'text-green-600',
  },
])

// ── Auth ─────────────────────────────────────────────────────────────────────────
function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

// ── Data ──────────────────────────────────────────────────────────────────────────
async function fetchApplications() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/applications', {
      params: {
        search: filters.search || undefined,
        status: filters.status || undefined,
        page:   filters.page,
      },
      headers: authHeaders(),
    })
    applications.value = data.data ?? []
    meta.value         = data.meta ?? {}
  } finally {
    loading.value = false
  }
}

const onSearch = debounce(() => resetAndFetch(), 350)

function resetAndFetch() { filters.page = 1; fetchApplications() }
function goPage(p) { filters.page = p; fetchApplications() }

function clearFilters() {
  filters.search = ''
  filters.status = ''
  resetAndFetch()
}

function openDetail(app) { detailTarget.value = app }

function openUpdate(app) {
  updateTarget.value = app
  statusForm.status  = app.status
  statusForm.remarks = app.remarks ?? ''
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

// ── Helpers ───────────────────────────────────────────────────────────────────────
function initials(name) {
  return name?.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase() ?? '?'
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(fetchApplications)
</script>
