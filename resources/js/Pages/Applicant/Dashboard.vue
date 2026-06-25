<template>
  <ApplicantLayout>
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">

      <!-- Full-page skeleton overlay (shown while data loads) -->
      <div v-if="pageLoading" class="animate-pulse">
        <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 flex items-start gap-3">
          <div class="w-5 h-5 bg-gray-200 rounded flex-shrink-0 mt-0.5"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-64"></div>
            <div class="h-3 bg-gray-100 rounded w-96"></div>
          </div>
          <div class="h-8 w-28 bg-gray-200 rounded-lg flex-shrink-0"></div>
        </div>
        <div class="mb-6 space-y-2">
          <div class="h-7 bg-gray-200 rounded w-72"></div>
          <div class="h-4 bg-gray-100 rounded w-56"></div>
        </div>
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
          <div v-for="n in 4" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 flex items-start gap-4 shadow-sm">
            <div class="w-11 h-11 rounded-xl bg-gray-200 flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-3 bg-gray-200 rounded w-16"></div>
              <div class="h-7 bg-gray-200 rounded w-10"></div>
            </div>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="n in 6" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
            <div class="flex gap-2 mb-3"><div class="h-5 w-12 bg-gray-200 rounded-md"></div><div class="h-5 w-16 bg-gray-200 rounded-full"></div></div>
            <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded mb-4 w-1/2"></div>
            <div class="space-y-2">
              <div class="h-3 bg-gray-100 rounded w-full"></div>
              <div class="h-3 bg-gray-100 rounded w-5/6"></div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
              <div class="h-3 w-24 bg-gray-200 rounded"></div>
              <div class="h-8 w-24 bg-gray-200 rounded-lg"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Real content -->
      <div v-if="!pageLoading">

      <!-- Profile incomplete banner -->
      <div v-if="!isComplete"
        class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div class="flex-1">
          <p class="text-sm font-semibold text-amber-800">Complete your profile before applying</p>
          <p class="text-xs text-amber-700 mt-0.5">Fill out your personal details, address, experience, and upload required documents before submitting an application.</p>
        </div>
        <Link href="/applicant/complete-profile"
          class="flex-shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition-colors">
          Complete Now
        </Link>
      </div>

      <!-- Greeting -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ greeting }}, {{ firstName }}!</h1>
        <p class="text-sm text-gray-500 mt-1">Discover and apply for open government positions.</p>
      </div>

      <!-- Quick actions -->
      <div class="flex flex-wrap gap-2 mb-6">
        <Link href="/applicant/dashboard"
          class="inline-flex items-center gap-2 px-4 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          Browse Vacancies
        </Link>
        <Link href="/applicant/applications"
          class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          View My Applications
        </Link>
      </div>

      <!-- Stat cards -->
      <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <Link v-for="card in statCards" :key="card.label" :href="card.link"
          class="bg-white rounded-xl border border-gray-200 p-5 flex items-start gap-4 shadow-sm hover:border-[#2a338f]/30 hover:shadow-md transition-all cursor-pointer">
          <div :class="card.iconBg" class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" :class="card.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon"/>
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium">{{ card.label }}</p>
            <p v-if="!loadingApps" class="text-2xl font-bold text-gray-900 mt-0.5">{{ card.value }}</p>
            <div v-else class="h-7 w-10 bg-gray-200 rounded animate-pulse mt-0.5"></div>
          </div>
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2">

        <!-- Search + filters -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5">
          <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-48 relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input v-model="vacancyFilters.search" @input="onVacancySearch"
                type="text" placeholder="Search position title..."
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" />
            </div>
            <select v-model="vacancyFilters.salary_grade" @change="fetchDashboardVacancies"
              class="text-sm border border-gray-300 rounded-lg px-3 pr-8 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-[#2a338f] focus:outline-none">
              <option value="">All Salary Grades</option>
              <option v-for="sg in salaryGrades" :key="sg" :value="sg">SG-{{ sg }}</option>
            </select>
            <select v-model="vacancyFilters.sort" @change="fetchDashboardVacancies"
              class="text-sm border border-gray-300 rounded-lg px-3 pr-8 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-[#2a338f] focus:outline-none">
              <option value="deadline_asc">Deadline: Soonest First</option>
              <option value="deadline_desc">Deadline: Latest First</option>
              <option value="sg_desc">Salary Grade: Highest</option>
              <option value="newest">Recently Posted</option>
            </select>
            <button v-if="hasVacancyFilters" @click="clearVacancyFilters"
              class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Clear
            </button>
            <span class="ml-auto text-sm text-gray-400 self-center">
              {{ loadingVacancies ? 'Loading…' : `${vacancyPagination.total ?? 0} result${vacancyPagination.total !== 1 ? 's' : ''}` }}
            </span>
          </div>
        </div>

        <!-- Vacancy grid -->
        <div v-if="loadingVacancies" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div v-for="n in 6" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
            <div class="flex gap-2 mb-3"><div class="h-5 w-12 bg-gray-200 rounded-md"></div><div class="h-5 w-16 bg-gray-200 rounded-full"></div></div>
            <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded mb-4 w-1/2"></div>
            <div class="space-y-2">
              <div class="h-3 bg-gray-100 rounded w-full"></div>
              <div class="h-3 bg-gray-100 rounded w-5/6"></div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
              <div class="h-3 w-24 bg-gray-200 rounded"></div>
              <div class="h-8 w-24 bg-gray-200 rounded-lg"></div>
            </div>
          </div>
        </div>

        <div v-else-if="dashboardVacancies.length" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <VacancyCard v-for="v in dashboardVacancies" :key="v.id" :vacancy="v" :authenticated="true" :show-detail-first="true" :applied-ids="appliedVacancyIds" />
        </div>

        <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-center py-20 text-center">
          <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="text-sm font-semibold text-gray-900 mb-1">No vacancies found</p>
          <p class="text-xs text-gray-400">{{ hasVacancyFilters ? 'Try clearing your filters.' : 'No open positions at this time. Check back soon.' }}</p>
          <button v-if="hasVacancyFilters" @click="clearVacancyFilters"
            class="mt-4 px-4 py-2 text-sm font-medium text-white bg-[#2a338f] hover:bg-[#1e2570] rounded-lg transition-colors">
            Clear filters
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="!loadingVacancies && vacancyPagination.last_page > 1"
          class="mt-8 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing <span class="font-medium text-gray-700">{{ vacancyPagination.from }}</span>–<span class="font-medium text-gray-700">{{ vacancyPagination.to }}</span>
            of <span class="font-medium text-gray-700">{{ vacancyPagination.total }}</span>
          </p>
          <div class="flex items-center gap-1">
            <button :disabled="vacancyPagination.current_page === 1" @click="vacancyPage(vacancyPagination.current_page - 1)"
              class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
              </svg>
            </button>
            <template v-for="page in vacancyVisiblePages" :key="page">
              <span v-if="page === '...'" class="px-2 text-gray-400 text-sm">…</span>
              <button v-else @click="vacancyPage(page)"
                :class="['w-9 h-9 rounded-lg text-sm font-medium transition-colors', page === vacancyPagination.current_page ? 'bg-[#2a338f] text-white' : 'text-gray-700 hover:bg-gray-100']">
                {{ page }}
              </button>
            </template>
            <button :disabled="vacancyPagination.current_page === vacancyPagination.last_page" @click="vacancyPage(vacancyPagination.current_page + 1)"
              class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
              </svg>
            </button>
          </div>
        </div>

        </div>

        <!-- Profile status sidebar -->
        <div class="space-y-4">
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Profile Status</h2>
            <div class="space-y-3">
              <div v-for="step in profileSteps" :key="step.label" class="flex items-center gap-3">
                <div :class="step.done ? 'bg-green-100' : 'bg-gray-100'"
                  class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0">
                  <svg v-if="step.done" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                  <span v-else class="w-2 h-2 rounded-full bg-gray-300 block"></span>
                </div>
                <span class="text-sm" :class="step.done ? 'text-gray-700 font-medium' : 'text-gray-400'">{{ step.label }}</span>
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs text-gray-500">Completion</span>
                <span class="text-xs font-semibold text-gray-700">{{ completionPct }}%</span>
              </div>
              <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-2 rounded-full transition-all duration-500"
                  :class="completionPct === 100 ? 'bg-green-500' : 'bg-[#2a338f]'"
                  :style="{ width: completionPct + '%' }"></div>
              </div>
            </div>
            <Link :href="'/applicant/complete-profile'"
              class="mt-4 flex items-center justify-center gap-1.5 w-full py-2 text-sm font-semibold rounded-lg transition-colors"
              :class="isComplete ? 'bg-gray-100 hover:bg-gray-200 text-gray-700' : 'bg-[#2a338f] hover:bg-[#1e2570] text-white'">
              {{ isComplete ? 'View / Edit Profile' : 'Update Profile' }}
            </Link>
          </div>
        </div>

      </div>

    </div>

  </div>

  </ApplicantLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import { debounce } from 'lodash-es'
import { applicationApi, profileApi } from '@/services/api'
import VacancyCard from '@/Components/Vacancy/VacancyCard.vue'
import ApplicantLayout from '@/Layouts/ApplicantLayout.vue'

// ── Page loading state ───────────────────────────────────────────────────────
const pageLoading = ref(true)

// ── Auth ─────────────────────────────────────────────────────────────────────
const authUser  = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
const authToken = localStorage.getItem('auth_token') ?? ''

const firstName = computed(() => (authUser?.name ?? 'there').split(' ')[0])

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 18) return 'Good afternoon'
  return 'Good evening'
})

function authHeaders() {
  return authToken ? { Authorization: `Bearer ${authToken}` } : {}
}

// ── Applications ─────────────────────────────────────────────────────────────
const applications = ref([])
const profile      = ref(null)
const loadingApps  = ref(true)
const isComplete   = ref(false)

const appCounts = computed(() => {
  const list = applications.value
  return {
    total:   list.length,
    pending: list.filter(a => a.status === 'submitted').length,
    exam:    list.filter(a => a.status === 'exam_scheduled').length,
    passed:  list.filter(a => a.status === 'passed').length,
  }
})

const statCards = computed(() => [
  {
    label: 'Total Applications', value: appCounts.value.total,
    link: '/applicant/applications',
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    iconBg: 'bg-[#2a338f]/10', iconColor: 'text-[#2a338f]',
  },
  {
    label: 'Pending Review', value: appCounts.value.pending,
    link: '/applicant/applications?status=submitted',
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    iconBg: 'bg-yellow-50', iconColor: 'text-yellow-600',
  },
  {
    label: 'Exam Scheduled', value: appCounts.value.exam,
    link: '/applicant/applications?status=exam_scheduled',
    icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    iconBg: 'bg-purple-50', iconColor: 'text-purple-600',
  },
  {
    label: 'Passed', value: appCounts.value.passed,
    link: '/applicant/applications?status=passed',
    icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    iconBg: 'bg-green-50', iconColor: 'text-green-600',
  },
])

const appliedVacancyIds = computed(() => applications.value.map(a => a.vacancy_id))

const profileSteps = computed(() => {
  const p = profile.value
  return [
    { label: 'Personal details',         done: !!(p?.gender && p?.civil_status && p?.birthday) },
    { label: 'Address information',      done: !!(p?.region && p?.province) },
    { label: 'Contact details',          done: !!(p?.mobile_number) },
    { label: 'Experience & education',   done: !!(p?.work_experiences?.length || p?.educational_attainments?.length) },
    { label: 'Trainings',                done: !!(p?.trainings?.length) },
    { label: 'Eligibility & other info', done: !!(p?.eligibility) },
    { label: 'Documents uploaded',       done: !!(p?.pds_path && p?.app_letter_path && p?.coe_path && p?.tor_path && p?.ipcr_path) },
  ]
})

const completionPct = computed(() => {
  const steps = profileSteps.value
  return Math.round((steps.filter(s => s.done).length / steps.length) * 100)
})

// ── Vacancy browser ───────────────────────────────────────────────────────────
const dashboardVacancies = ref([])
const loadingVacancies   = ref(false)
const vacancyPagination  = ref({ total: 0, current_page: 1, last_page: 1, from: 0, to: 0 })
const salaryGrades       = Array.from({ length: 24 }, (_, i) => i + 1)

const vacancyFilters = reactive({
  search: '', salary_grade: '', sort: 'deadline_asc', page: 1,
})

const hasVacancyFilters = computed(() =>
  vacancyFilters.search || vacancyFilters.salary_grade || vacancyFilters.sort !== 'deadline_asc'
)

const vacancyVisiblePages = computed(() => {
  const { current_page: cur, last_page: last } = vacancyPagination.value
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
  if (cur <= 4)  return [1, 2, 3, 4, 5, '...', last]
  if (cur >= last - 3) return [1, '...', last - 4, last - 3, last - 2, last - 1, last]
  return [1, '...', cur - 1, cur, cur + 1, '...', last]
})

async function fetchDashboardVacancies() {
  loadingVacancies.value = true
  try {
    const { data } = await axios.get('/api/vacancies', {
      headers: authHeaders(),
      params: {
        search:       vacancyFilters.search       || undefined,
        salary_grade: vacancyFilters.salary_grade || undefined,
        sort:         vacancyFilters.sort,
        page:         vacancyFilters.page,
      },
    })
    dashboardVacancies.value = data.data
    vacancyPagination.value  = data.meta
  } catch (e) {
    console.error('Failed to load vacancies', e)
  } finally {
    loadingVacancies.value = false
  }
}

const onVacancySearch = debounce(() => {
  vacancyFilters.page = 1
  fetchDashboardVacancies()
}, 350)

function vacancyPage(page) {
  vacancyFilters.page = page
  fetchDashboardVacancies()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function clearVacancyFilters() {
  vacancyFilters.search       = ''
  vacancyFilters.salary_grade = ''
  vacancyFilters.sort         = 'deadline_asc'
  vacancyFilters.page         = 1
  fetchDashboardVacancies()
}

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([
    (async () => {
      try {
        const [appsRes, profileRes] = await Promise.all([
          applicationApi.myApplications(),
          profileApi.show(),
        ])
        applications.value = appsRes.data
        profile.value      = profileRes.data.profile
        isComplete.value   = profileRes.data.is_complete
      } catch (e) {
        // 401 handled by api.js interceptor
      } finally {
        loadingApps.value = false
      }
    })(),
    fetchDashboardVacancies(),
  ])
  pageLoading.value = false
})
</script>
