<template>
  <AdminLayout title="Audit Logs">

    <!-- Toolbar -->
    <div class="flex flex-col gap-2 mb-4">

      <!-- Row 1: search + action filter + auto-refresh + count -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="filters.search" @input="onSearch" type="text"
            placeholder="Search action, model, or user…"
            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" />
        </div>
        <select v-model="filters.action_type" @change="resetAndFetch"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white sm:min-w-36">
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
        <button @click="autoRefresh = !autoRefresh"
          class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-xs font-medium transition-colors whitespace-nowrap self-start sm:self-auto"
          :class="autoRefresh ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
          <span class="w-1.5 h-1.5 rounded-full" :class="autoRefresh ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"></span>
          {{ autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh OFF' }}
        </button>
        <span v-if="!loading" class="text-xs text-gray-400 whitespace-nowrap hidden sm:block">
          {{ meta.total ?? 0 }} log{{ meta.total !== 1 ? 's' : '' }}
        </span>
      </div>

      <!-- Row 2: date range grouped -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">
          <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <input v-model="filters.date_from" @change="resetAndFetch" type="date"
            class="text-sm bg-transparent focus:outline-none text-gray-700 w-36" />
          <span class="text-gray-300 text-xs">—</span>
          <input v-model="filters.date_to" @change="resetAndFetch" type="date"
            class="text-sm bg-transparent focus:outline-none text-gray-700 w-36" />
          <button v-if="filters.date_from || filters.date_to" @click="filters.date_from = ''; filters.date_to = ''; resetAndFetch()"
            class="text-gray-300 hover:text-gray-500 transition-colors ml-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <button v-if="filters.search || filters.action_type || filters.date_from || filters.date_to"
          @click="clearFilters"
          class="text-xs text-[#2a338f] hover:underline self-start sm:self-auto">
          Clear all filters
        </button>
        <span v-if="!loading" class="text-xs text-gray-400 sm:hidden">
          {{ meta.total ?? 0 }} log{{ meta.total !== 1 ? 's' : '' }}
        </span>
      </div>
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

      <div v-else class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3">Action</th>
            <th class="px-5 py-3">Model</th>
            <th class="px-5 py-3">Performed By</th>
            <th class="px-5 py-3 text-right">Date &amp; Time</th>
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
            <td class="px-5 py-3.5">
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
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
              <span :title="timeAgo(log.created_at)" class="cursor-default">
                <span class="block text-xs text-gray-600 font-medium">{{ formatShortDate(log.created_at) }}</span>
                <span class="block text-[10px] text-gray-400">{{ formatTime(log.created_at) }}</span>
              </span>
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
      </div>

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
          <button v-for="p in visibleAuditPages" :key="p" @click="typeof p === 'number' && goPage(p)"
            :disabled="p === '…'"
            :class="['px-2.5 py-1 rounded-lg text-xs font-medium transition-colors',
              p === meta.current_page ? 'bg-[#2a338f] text-white' : p === '…' ? 'text-gray-300 cursor-default' : 'text-gray-600 hover:bg-gray-100']">
            {{ p }}
          </button>
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
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from 'vue'
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

const visibleAuditPages = computed(() => {
  const total = meta.value.last_page ?? 1
  const cur   = meta.value.current_page ?? 1
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  if (cur <= 4)   return [1, 2, 3, 4, 5, '…', total]
  if (cur >= total - 3) return [1, '…', total - 4, total - 3, total - 2, total - 1, total]
  return [1, '…', cur - 1, cur, cur + 1, '…', total]
})

function roleBadgeClass(role) {
  return {
    admin:      'bg-purple-100 text-purple-700',
    hrmpsb:     'bg-amber-100 text-amber-700',
    applicant:  'bg-gray-100 text-gray-600',
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

function formatShortDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function formatTime(str) {
  if (!str) return ''
  return new Date(str).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit' })
}

function timeAgo(str) {
  if (!str) return '—'
  const diff = (Date.now() - new Date(str).getTime()) / 1000
  if (diff < 60)   return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`
  return formatDateTime(str)
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
