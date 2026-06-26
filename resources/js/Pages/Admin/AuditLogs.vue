<template>
  <AdminLayout title="Audit Logs">

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">

      <!-- Search -->
      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="filters.search" @input="onSearch" type="text"
          placeholder="Search action, model, or user…"
          class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" />
      </div>

      <!-- Action type filter -->
      <select v-model="filters.action_type" @change="resetAndFetch"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white min-w-36">
        <option value="">All Actions</option>
        <optgroup label="Vacancy">
          <option value="vacancy_created">Created</option>
          <option value="vacancy_published">Published</option>
          <option value="vacancy_archived">Archived</option>
          <option value="vacancy_deleted">Deleted</option>
          <option value="vacancy_updated">Updated</option>
        </optgroup>
        <optgroup label="Application">
          <option value="application_status_changed">Status Changed</option>
        </optgroup>
        <optgroup label="User">
          <option value="user_created">User Created</option>
          <option value="user_updated">User Updated</option>
        </optgroup>
      </select>

      <!-- Date range filter -->
      <input v-model="filters.date_from" @change="resetAndFetch" type="date"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white" />
      <span class="text-xs text-gray-400">–</span>
      <input v-model="filters.date_to" @change="resetAndFetch" type="date"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white" />

      <button @click="autoRefresh = !autoRefresh"
        class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-medium transition-colors whitespace-nowrap"
        :class="autoRefresh ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
        <span class="w-1.5 h-1.5 rounded-full" :class="autoRefresh ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"></span>
        {{ autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh OFF' }}
      </button>
      <span v-if="!loading" class="text-xs text-gray-400 whitespace-nowrap">
        {{ meta.total ?? 0 }} log{{ meta.total !== 1 ? 's' : '' }}
      </span>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

      <!-- Loading skeleton -->
      <div v-if="loading" class="p-6 space-y-3">
        <div v-for="n in 8" :key="n" class="flex items-center gap-4 h-10">
          <div class="h-5 w-24 bg-gray-100 rounded-full animate-pulse"></div>
          <div class="h-3.5 bg-gray-100 rounded flex-1 animate-pulse"></div>
          <div class="h-3 w-28 bg-gray-100 rounded animate-pulse hidden md:block"></div>
          <div class="h-3 w-20 bg-gray-100 rounded animate-pulse hidden lg:block"></div>
        </div>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3">Action</th>
            <th class="px-5 py-3 hidden md:table-cell">Model</th>
            <th class="px-5 py-3">Performed By</th>
            <th class="px-5 py-3 hidden lg:table-cell text-right">Date &amp; Time</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50/80 transition-colors">
            <td class="px-5 py-3.5">
              <span :class="actionBadgeClass(log.action)"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ actionLabel(log.action) }}
              </span>
            </td>
            <td class="px-5 py-3.5 hidden md:table-cell">
              <span class="text-gray-500 text-xs">
                {{ shortModel(log.auditable_type) }}
                <span class="text-gray-300 mx-1">#</span>
                <span class="text-gray-700 font-mono">{{ log.auditable_id }}</span>
              </span>
            </td>
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-bold flex-shrink-0">
                  {{ initials(log.user_name) }}
                </div>
                <span class="text-gray-700 text-xs truncate max-w-[130px]">
                  {{ log.user_name ?? 'System' }}
                </span>
                <span v-if="log.user_role"
                  class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider"
                  :class="roleBadgeClass(log.user_role)">
                  {{ roleLabel(log.user_role) }}
                </span>
              </div>
            </td>
            <td class="px-5 py-3.5 hidden lg:table-cell text-right text-xs text-gray-400 whitespace-nowrap">
              {{ formatDateTime(log.created_at) }}
            </td>
          </tr>

          <tr v-if="!logs.length">
            <td colspan="4" class="px-5 py-16 text-center">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm font-medium text-gray-400">No audit logs found</p>
                <button v-if="filters.search || filters.action_type" @click="clearFilters"
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

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, watch, onMounted, onBeforeUnmount } from 'vue'
import { debounce } from 'lodash-es'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { roleLabel } from '@/utils/roleLabel'

// ── State ────────────────────────────────────────────────────────────────────────
const loading      = ref(true)
const logs         = ref([])
const meta         = ref({})
const filters      = reactive({ search: '', action_type: '', date_from: '', date_to: '', page: 1 })
const autoRefresh  = ref(false)
let refreshInterval = null

watch(autoRefresh, (val) => {
  if (val) {
    refreshInterval = setInterval(fetchLogs, 30000)
  } else {
    clearInterval(refreshInterval)
  }
})

onBeforeUnmount(() => {
  clearInterval(refreshInterval)
})

// ── Auth ──────────────────────────────────────────────────────────────────────────
function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

// ── Data ──────────────────────────────────────────────────────────────────────────
async function fetchLogs() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/audit-logs', {
      params: {
        search:      filters.search      || undefined,
        action_type: filters.action_type || undefined,
        date_from:   filters.date_from   || undefined,
        date_to:     filters.date_to     || undefined,
        page:        filters.page,
      },
      headers: authHeaders(),
    })
    logs.value = data.data ?? []
    meta.value = data.meta ?? {}
  } finally {
    loading.value = false
  }
}

const onSearch = debounce(() => resetAndFetch(), 350)
function resetAndFetch() { filters.page = 1; fetchLogs() }
function goPage(p) { filters.page = p; fetchLogs() }
function clearFilters() { filters.search = ''; filters.action_type = ''; filters.date_from = ''; filters.date_to = ''; resetAndFetch() }

function roleBadgeClass(role) {
  return {
    admin: 'bg-purple-100 text-purple-700',
    'hr-manager': 'bg-[#2a338f]/10 text-[#2a338f]',
    'hr-officer': 'bg-teal-100 text-teal-700',
    applicant: 'bg-gray-100 text-gray-600',
    'hrmpsb-member': 'bg-amber-100 text-amber-700',
    'hrmpsb-secretariat': 'bg-orange-100 text-orange-700',
    'appointing-authority': 'bg-rose-100 text-rose-700',
  }[role] ?? 'bg-gray-100 text-gray-600'
}

// ── Helpers ───────────────────────────────────────────────────────────────────────
function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase()
}

function shortModel(type) {
  if (!type) return '—'
  return type.split('\\').pop()
}

function formatDateTime(str) {
  if (!str) return '—'
  return new Date(str).toLocaleString('en-PH', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: 'numeric', minute: '2-digit',
  })
}

function actionLabel(action) {
  if (!action) return '—'
  if (action.includes('status_changed')) {
    return action.replace('application_status_changed:', '').replace('→', ' → ')
  }
  return action.replace(/_/g, ' ')
}

function actionBadgeClass(action) {
  const a = action ?? ''
  if (a.includes('created'))         return 'bg-green-100 text-green-700'
  if (a.includes('published'))       return 'bg-blue-100 text-blue-700'
  if (a.includes('deleted'))         return 'bg-red-100 text-red-700'
  if (a.includes('archived'))        return 'bg-orange-100 text-orange-700'
  if (a.includes('updated'))         return 'bg-purple-100 text-purple-700'
  if (a.includes('status_changed'))  return 'bg-yellow-100 text-yellow-800'
  return 'bg-gray-100 text-gray-600'
}

onMounted(fetchLogs)
</script>
