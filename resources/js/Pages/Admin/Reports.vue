<template>
  <AdminLayout title="Demographic Reports">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900">Demographic Reports</h1>
          <p class="text-sm text-gray-500 mt-1">Applicant demographics breakdown across all or selected vacancies.</p>
        </div>
        <div v-if="!loading && totalProfiles > 0" class="flex items-center gap-2 px-3 py-1.5 bg-[#2a338f]/5 text-[#2a338f] rounded-lg text-sm font-medium">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          {{ totalProfiles }} applicant{{ totalProfiles !== 1 ? 's' : '' }} with profiles
        </div>
      </div>

      <!-- Error -->
      <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ error }}</div>

      <!-- Filter bar -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex flex-wrap items-end gap-4">
          <div class="min-w-56">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Position</label>
            <select v-model="selectedPosition" @change="onPositionChange"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
              <option value="">All Positions</option>
              <option v-for="title in uniquePositions" :key="title" :value="title">{{ title }}</option>
            </select>
          </div>

          <div class="min-w-56">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Date Published</label>
            <select v-model="selectedVacancyId" @change="loadAll" :disabled="!selectedPosition"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white disabled:opacity-50 disabled:cursor-not-allowed">
              <option value="">All Postings</option>
              <option v-for="v in positionVacancies" :key="v.id" :value="v.id">
                {{ formatPublicationRange(v.published_at, v.deadline_at) }}
              </option>
            </select>
          </div>

          <button @click="clearFilters"
            v-if="selectedPosition || selectedVacancyId"
            class="px-3 py-2 text-xs font-medium text-gray-500 hover:text-red-600 transition-colors whitespace-nowrap">
            Clear filters
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <div v-for="n in 5" :key="n" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
          <div class="h-4 bg-gray-100 rounded w-32 animate-pulse"></div>
          <div class="space-y-2">
            <div v-for="m in 4" :key="m" class="flex items-center gap-3">
              <div class="h-3 bg-gray-100 rounded w-20 animate-pulse"></div>
              <div class="flex-1 h-3 bg-gray-100 rounded animate-pulse"></div>
              <div class="h-3 bg-gray-100 rounded w-10 animate-pulse"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Demographic cards grid -->
      <div v-else-if="!loading && reports.length" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <div v-for="report in reports" :key="report.type"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col">

          <!-- Card header -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="flex items-center gap-3 min-w-0">
              <div :class="report.iconBg" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" :class="report.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="report.icon"/>
                </svg>
              </div>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">{{ report.label }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ report.description }}</p>
              </div>
            </div>
            <span class="text-xs font-medium text-gray-400 whitespace-nowrap shrink-0">{{ report.total }}</span>
          </div>

          <!-- Bar chart -->
          <div class="flex-1 space-y-2.5">
            <div v-for="(row, i) in report.rows" :key="i"
              class="flex items-center gap-3 group">
              <span class="text-xs text-gray-600 w-28 shrink-0 truncate" :title="row.label">{{ row.label }}</span>
              <div class="flex-1 h-5 bg-gray-50 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700 ease-out"
                  :style="{ width: Math.max(row.percentage, row.count > 0 ? 2 : 0) + '%' }"
                  :class="barColor(i)"></div>
              </div>
              <span class="text-xs font-semibold text-gray-700 w-12 text-right shrink-0">{{ row.count }}</span>
              <span class="text-xs text-gray-400 w-10 text-right shrink-0">{{ row.percentage }}%</span>
            </div>
          </div>

          <!-- Export button -->
          <button @click="exportCsv(report)"
            class="mt-4 w-full px-3 py-1.5 text-xs font-medium border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export CSV
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="!loading && !reports.length"
        class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-center py-24 text-center">
        <svg class="w-14 h-14 text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <p class="text-sm font-semibold text-gray-600 mb-1">No demographic data available</p>
        <p class="text-xs text-gray-400">Applicants need to complete their profiles for demographic data to appear.</p>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const vacancies           = ref([])
const selectedPosition    = ref('')
const selectedVacancyId   = ref('')
const loading             = ref(true)
const error               = ref(null)
const reports             = ref([])
const totalProfiles       = ref(0)

const uniquePositions = computed(() => {
  const titles = new Set(vacancies.value.map(v => v.position_title).filter(Boolean))
  return [...titles].sort()
})

const positionVacancies = computed(() => {
  if (!selectedPosition.value) return []
  return vacancies.value.filter(v => v.position_title === selectedPosition.value)
})

function onPositionChange() {
  selectedVacancyId.value = ''
  loadAll()
}

const BAR_COLORS = [
  'bg-[#2a338f]',
  'bg-blue-500',
  'bg-cyan-500',
  'bg-teal-500',
  'bg-emerald-500',
  'bg-green-500',
  'bg-yellow-500',
  'bg-amber-500',
  'bg-orange-500',
  'bg-red-500',
  'bg-pink-500',
  'bg-purple-500',
  'bg-violet-500',
  'bg-indigo-500',
  'bg-gray-400',
]

function barColor(i) {
  return BAR_COLORS[i % BAR_COLORS.length]
}

const DEMOGRAPHIC_REPORTS = [
  {
    type: 'demographics-age',
    label: 'Age Group',
    description: 'Distribution by age bracket',
    icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
    iconBg: 'bg-violet-100', iconColor: 'text-violet-700',
  },
  {
    type: 'demographics-gender',
    label: 'Sex / Gender',
    description: 'Distribution by gender identity',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    iconBg: 'bg-pink-100', iconColor: 'text-pink-700',
  },
  {
    type: 'demographics-civil-status',
    label: 'Civil Status',
    description: 'Distribution by civil status',
    icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
    iconBg: 'bg-red-100', iconColor: 'text-red-700',
  },
  {
    type: 'demographics-region',
    label: 'Region',
    description: 'Distribution by geographic region',
    icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
    iconBg: 'bg-emerald-100', iconColor: 'text-emerald-700',
  },
  {
    type: 'demographics-special',
    label: 'Special Categories',
    description: 'Indigenous, PWD, Solo Parent',
    icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    iconBg: 'bg-amber-100', iconColor: 'text-amber-700',
  },
]

function clearFilters() {
  selectedPosition.value = ''
  selectedVacancyId.value = ''
  loadAll()
}

function formatPublicationRange(published, deadline) {
  if (!published) return 'Unpublished'
  const from = new Date(published)
  const to = deadline ? new Date(deadline) : null
  const opts = { month: 'short', day: 'numeric', year: 'numeric' }
  if (!to || from.toDateString() === to.toDateString()) {
    return from.toLocaleDateString('en-US', opts)
  }
  const sameMonth = from.getMonth() === to.getMonth() && from.getFullYear() === to.getFullYear()
  if (sameMonth) {
    return `${from.toLocaleDateString('en-US', { month: 'short' })} ${from.getDate()}–${to.getDate()}, ${from.getFullYear()}`
  }
  return `${from.toLocaleDateString('en-US', opts)} – ${to.toLocaleDateString('en-US', opts)}`
}

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function loadReport(type) {
  try {
    const params = {}
    if (selectedVacancyId.value) params.vacancy_id = selectedVacancyId.value
    const { data } = await axios.get(`/api/reports/${type}`, { headers: authHeaders(), params })
    return data
  } catch (e) {
    throw new Error(e.response?.data?.message ?? `Failed to load ${type}.`)
  }
}

async function loadAll() {
  loading.value = true
  error.value = null
  reports.value = []

  try {
    const results = await Promise.all(
      DEMOGRAPHIC_REPORTS.map(async (def) => {
        const data = await loadReport(def.type)
        return { ...def, rows: data.rows ?? [], total: data.total ?? 0 }
      })
    )
    reports.value = results
    totalProfiles.value = Math.max(...results.map(r => r.total), 0)
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

function exportCsv(report) {
  if (!report.rows.length) return
  const headers = ['Category', 'Count', 'Percentage']
  const lines = report.rows.map(r => `"${r.label}",${r.count},${r.percentage}`)
  const csv = [headers.join(','), ...lines].join('\n')
  const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `${report.type}-${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(url)
}

async function loadVacancies() {
  const { data } = await axios.get('/api/vacancies', { headers: authHeaders() })
  vacancies.value = data.data ?? data
}

onMounted(async () => {
  await loadVacancies()
  loadAll()
})
</script>