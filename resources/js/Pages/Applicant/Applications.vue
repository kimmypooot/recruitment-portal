<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">

    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
      <div class="h-1 w-full bg-[#ec1c2d]"></div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <Link href="/" class="flex items-center gap-3">
          <img src="/images/csc-logo.png" alt="CSC Logo" class="h-9 w-9 object-contain flex-shrink-0"
            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
          <div class="w-9 h-9 rounded-lg bg-[#2a338f] items-center justify-center flex-shrink-0 hidden">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
          </div>
          <div class="leading-tight">
            <p class="text-sm font-bold text-gray-900">CSC Regional Office</p>
            <p class="text-xs text-gray-500">Recruitment Portal</p>
          </div>
        </Link>
        <nav class="flex items-center gap-6">
          <Link href="/" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Browse Vacancies</Link>
          <Link href="/applicant/dashboard" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Dashboard</Link>
          <button @click="logout" class="text-sm text-red-500 hover:text-red-700 font-medium">Sign out</button>
        </nav>
      </div>
    </header>

    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">

      <!-- Page header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">My Applications</h1>
          <p class="text-sm text-gray-500 mt-1">Track the status of all your submitted applications.</p>
        </div>
        <Link href="/"
          class="inline-flex items-center gap-2 px-4 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          Browse Vacancies
        </Link>
      </div>

      <!-- Status filter tabs -->
      <div class="flex gap-1 bg-white border border-gray-200 rounded-xl p-1 mb-6 shadow-sm w-fit">
        <button v-for="tab in statusTabs" :key="tab.value"
          @click="activeStatus = tab.value"
          class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
          :class="activeStatus === tab.value
            ? 'bg-[#2a338f] text-white shadow-sm'
            : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'">
          {{ tab.label }}
          <span v-if="tab.count !== null"
            class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold"
            :class="activeStatus === tab.value ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600'">
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="n in 4" :key="n" class="h-20 bg-gray-100 rounded-xl animate-pulse"></div>
      </div>

      <!-- Applications list -->
      <div v-else-if="filteredApplications.length" class="space-y-3">
        <div v-for="app in filteredApplications" :key="app.id"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start gap-5 hover:border-[#2a338f]/30 transition-colors">

          <!-- Status indicator -->
          <div :class="statusColor(app.status).bg"
            class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" :class="statusColor(app.status).icon"
              fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="statusColor(app.status).path"/>
            </svg>
          </div>

          <!-- Details -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-base font-semibold text-gray-900 truncate">
                  {{ app.vacancy?.position_title ?? 'Unknown Position' }}
                </p>
                <p class="text-sm text-gray-500 mt-0.5">
                  {{ app.vacancy?.place_of_assignment ?? '' }}
                  <span v-if="app.vacancy?.salary_grade" class="ml-2 text-xs font-medium px-1.5 py-0.5 bg-[#2a338f]/10 text-[#2a338f] rounded">
                    SG-{{ app.vacancy.salary_grade }}
                  </span>
                </p>
              </div>
              <StatusBadge :status="app.status" />
            </div>

            <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Applied {{ formatDate(app.created_at) }}
              </span>
              <span v-if="app.vacancy?.deadline_at" class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Deadline {{ formatDate(app.vacancy.deadline_at) }}
              </span>
            </div>
          </div>

        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="text-center py-20">
        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-gray-500">
          {{ activeStatus === 'all' ? 'No applications yet' : `No ${activeStatus.replace(/_/g, ' ')} applications` }}
        </p>
        <p class="text-xs text-gray-400 mt-1">Browse open vacancies and submit your first application.</p>
        <Link href="/"
          class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors">
          Browse Vacancies
        </Link>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { applicationApi } from '@/services/api'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

const applications  = ref([])
const loading       = ref(true)
const activeStatus  = ref('all')

onMounted(async () => {
  if (!localStorage.getItem('auth_token')) {
    router.visit('/login')
    return
  }
  try {
    const { data } = await applicationApi.myApplications()
    applications.value = data
  } catch {
    // 401 handled by interceptor
  } finally {
    loading.value = false
  }
})

const statusTabs = computed(() => [
  { value: 'all',            label: 'All',         count: applications.value.length },
  { value: 'submitted',      label: 'Pending',     count: applications.value.filter(a => a.status === 'submitted').length },
  { value: 'exam_scheduled', label: 'Exam',        count: applications.value.filter(a => a.status === 'exam_scheduled').length },
  { value: 'passed',         label: 'Passed',      count: applications.value.filter(a => a.status === 'passed').length },
  { value: 'failed',         label: 'Not Passed',  count: applications.value.filter(a => a.status === 'failed').length },
])

const filteredApplications = computed(() =>
  activeStatus.value === 'all'
    ? applications.value
    : applications.value.filter(a => a.status === activeStatus.value)
)

function statusColor(status) {
  const map = {
    submitted:      { bg: 'bg-yellow-50', icon: 'text-yellow-500', path: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    exam_scheduled: { bg: 'bg-purple-50', icon: 'text-purple-500', path: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    passed:         { bg: 'bg-green-50',  icon: 'text-green-500',  path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    failed:         { bg: 'bg-red-50',    icon: 'text-red-400',    path: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
    appointed:      { bg: 'bg-[#2a338f]/10', icon: 'text-[#2a338f]', path: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z' },
  }
  return map[status] ?? { bg: 'bg-gray-50', icon: 'text-gray-400', path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
}

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
}

function logout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  router.visit('/login')
}
</script>
