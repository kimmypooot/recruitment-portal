<template>
  <ApplicantLayout>
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">

      <!-- Full-page skeleton overlay (shown while data loads) -->
      <div v-if="pageLoading" class="space-y-6">
        <SkeletonLoader variant="stat-card" :count="4" wrapper-class="grid grid-cols-2 xl:grid-cols-4 gap-4" />
        <SkeletonLoader variant="card" :count="6" wrapper-class="grid grid-cols-1 sm:grid-cols-2 gap-5" />
      </div>

      <!-- Real content -->
      <div v-if="!pageLoading">

      <!-- Profile incomplete banner -->
      <div v-if="!isComplete" role="alert"
        class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
        <Icon name="alert" size="5" color="text-amber-500" class="flex-shrink-0 mt-0.5" />
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
        <div class="flex flex-wrap items-center gap-2 mt-2">
          <p class="text-sm text-gray-500">Discover and apply for open positions at the Civil Service Commission Regional Office VIII.</p>
          <span v-if="authUser.email_verified_at"
            class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-full">
            <Icon name="check" size="3" color="text-green-700" />
            Email Verified
          </span>
          <span v-else
            class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-full">
            <Icon name="alert" size="3" color="text-amber-700" />
            Email Not Verified
          </span>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="flex flex-wrap gap-2 mb-6">
        <Link href="/applicant/dashboard"
          class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
          <Icon name="search" size="4" />
          Browse Vacancies
        </Link>
        <Link href="/applicant/applications"
          class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors">
          <Icon name="document" size="4" />
          View My Applications
        </Link>
      </div>

      <!-- Stat cards -->
      <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <Link v-for="card in statCards" :key="card.label" :href="card.link"
          class="group relative bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer overflow-hidden">
          <div class="h-1.5 w-full bg-gradient-to-r" :class="card.accent"></div>
          <div class="p-5 flex items-start gap-4">
            <div :class="card.iconBg" class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-200">
              <svg class="w-5 h-5" :class="card.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon"/>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-xs text-gray-500 font-medium truncate">{{ card.label }}</p>
              <p v-if="!loadingApps" class="text-2xl font-bold text-gray-900 mt-0.5 tabular-nums">{{ card.value }}</p>
              <div v-else class="h-7 w-10 bg-gray-200 rounded animate-pulse mt-0.5"></div>
            </div>
          </div>
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2">

        <!-- Search + filters -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5">
          <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-48 relative">
              <Icon name="search" size="4" color="text-gray-400" class="absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="vacancyFilters.search" @input="onVacancySearch"
                type="text" placeholder="Search position title..."
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
            </div>
            <select v-model="vacancyFilters.salary_grade" @change="fetchDashboardVacancies"
              class="text-sm border border-gray-300 rounded-lg px-3 pr-8 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-primary focus:outline-none">
              <option value="">All Salary Grades</option>
              <option v-for="sg in salaryGrades" :key="sg" :value="sg">SG-{{ sg }}</option>
            </select>
            <select v-model="vacancyFilters.sort" @change="fetchDashboardVacancies"
              class="text-sm border border-gray-300 rounded-lg px-3 pr-8 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-primary focus:outline-none">
              <option value="deadline_asc">Deadline: Soonest First</option>
              <option value="deadline_desc">Deadline: Latest First</option>
              <option value="sg_desc">Salary Grade: Highest</option>
              <option value="newest">Recently Posted</option>
            </select>
            <button v-if="hasVacancyFilters" @click="clearVacancyFilters"
              class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center gap-1.5">
              <Icon name="close" size="3.5" />
              Clear
            </button>
            <span class="ml-auto text-sm text-gray-400 self-center">
              {{ loadingVacancies ? 'Loading…' : `${vacancyPagination.total ?? 0} result${vacancyPagination.total !== 1 ? 's' : ''}` }}
            </span>
          </div>
        </div>

        <!-- Vacancy grid -->
        <div v-if="loadingVacancies" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <SkeletonLoader v-for="n in 6" :key="n" variant="card" />
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
            class="mt-4 px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-lg transition-colors">
            Clear filters
          </button>
        </div>

        <Pagination
          v-if="!loadingVacancies && vacancyPagination.last_page > 1"
          :current-page="vacancyPagination.current_page"
          :last-page="vacancyPagination.last_page"
          :total="vacancyPagination.total"
          :from="vacancyPagination.from"
          :to="vacancyPagination.to"
          @page-change="vacancyPage" />

        </div>

        <!-- Profile status sidebar -->
        <div class="space-y-4">
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Profile Status</h2>
            <div class="space-y-3">
              <div v-for="step in profileSteps" :key="step.label" class="flex items-center gap-3">
                <div :class="step.done ? 'bg-green-100' : 'bg-gray-100'"
                  class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0">
                  <Icon v-if="step.done" name="check" size="4" color="text-green-600" />
                  <span v-else class="w-2 h-2 rounded-full bg-gray-300 block"></span>
                </div>
                <span class="flex-1 text-sm" :class="step.done ? 'text-gray-700 font-medium' : 'text-gray-400'">{{ step.label }}</span>
                <Link v-if="!step.done" href="/applicant/complete-profile"
                  class="text-[10px] text-primary hover:underline flex-shrink-0 font-medium">Fix →</Link>
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs text-gray-500">Completion</span>
                <span class="text-xs font-semibold text-gray-700">{{ completionPct }}%</span>
              </div>
              <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-2 rounded-full transition-all duration-500"
                  :class="completionPct === 100 ? 'bg-green-500' : 'bg-primary'"
                  :style="{ width: completionPct + '%' }"></div>
              </div>
            </div>
            <Link :href="'/applicant/complete-profile'"
              class="mt-4 flex items-center justify-center gap-1.5 w-full py-2 text-sm font-semibold rounded-lg transition-colors"
              :class="isComplete ? 'bg-gray-100 hover:bg-gray-200 text-gray-700' : 'bg-primary hover:bg-primary-dark text-white'">
              {{ isComplete ? 'View / Edit Profile' : 'Update Profile' }}
            </Link>
          </div>

          <!-- Recent Applications -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-sm font-semibold text-gray-900">Recent Applications</h2>
              <Link href="/applicant/applications" class="text-xs text-primary hover:underline font-medium">View all →</Link>
            </div>
            <div v-if="!loadingApps && recentApplications.length" class="space-y-3">
              <div v-for="app in recentApplications" :key="app.id" class="flex items-start gap-2.5">
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-medium text-gray-800 truncate leading-snug">
                    {{ app.vacancy?.position_title ?? 'Unknown Position' }}
                  </p>
                  <p class="text-[10px] text-gray-400 mt-0.5">{{ formatDate(app.submitted_at ?? app.created_at) }}</p>
                </div>
                <StatusBadge :status="app.status" class="flex-shrink-0 mt-0.5" />
              </div>
            </div>
            <div v-else-if="loadingApps" class="space-y-3">
              <div v-for="n in 3" :key="n" class="flex items-center gap-2.5">
                <SkeletonLoader variant="text" :lines="2" :widths="['75%', '33%']" wrapper-class="flex-1" />
                <div class="h-4 w-16 bg-gray-100 rounded-full animate-pulse flex-shrink-0"></div>
              </div>
            </div>
            <p v-else class="text-xs text-gray-400 text-center py-3">No applications yet.</p>
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
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import ApplicantLayout from '@/Layouts/ApplicantLayout.vue'
import Icon from '@/Components/UI/Icon.vue'
import SkeletonLoader from '@/Components/UI/SkeletonLoader.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import { formatDate, formatDateLong, formatDateTime, formatDateRange, daysRemaining } from '@/utils/dates'

// ── Page loading state ───────────────────────────────────────────────────────
const pageLoading = ref(true)

// ── Auth ─────────────────────────────────────────────────────────────────────
const authUser  = JSON.parse(localStorage.getItem('auth_user') ?? '{}')

const firstName = computed(() => authUser?.first_name ?? 'there')

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 18) return 'Good afternoon'
  return 'Good evening'
})

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
    interview: list.filter(a => a.status === 'for_interview' || a.status === 'interviewed').length,
  }
})

const statCards = computed(() => [
  {
    label: 'Total Applications', value: appCounts.value.total,
    link: '/applicant/applications',
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    accent: 'from-primary to-[#3b45b0]',
    iconBg: 'bg-indigo-50', iconColor: 'text-primary',
  },
  {
    label: 'Pending Review', value: appCounts.value.pending,
    link: '/applicant/applications?status=submitted',
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    accent: 'from-amber-400 to-yellow-500',
    iconBg: 'bg-amber-50', iconColor: 'text-amber-600',
  },
  {
    label: 'Exam Scheduled', value: appCounts.value.exam,
    link: '/applicant/applications?status=exam_scheduled',
    icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    accent: 'from-violet-500 to-purple-600',
    iconBg: 'bg-violet-50', iconColor: 'text-violet-600',
  },
  {
    label: 'Interview Scheduled', value: appCounts.value.interview,
    link: '/applicant/applications?status=for_interview',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    accent: 'from-sky-500 to-cyan-600',
    iconBg: 'bg-sky-50', iconColor: 'text-sky-600',
  },
])

const appliedVacancyIds = computed(() => applications.value.map(a => a.vacancy_id))

const recentApplications = computed(() =>
  [...applications.value]
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 3)
)

const profileSteps = computed(() => {
  const p = profile.value
  return [
    { label: 'Personal details',         done: !!(p?.gender && p?.civil_status && p?.birthday) },
    { label: 'Address information',      done: !!(p?.region && p?.province) },
    { label: 'Contact details',          done: !!(p?.mobile_number) },
    { label: 'Experience & education',   done: !!(p?.work_experiences?.length || p?.educational_attainments?.length) },
    { label: 'Trainings',                done: !!(p?.trainings?.length) },
    { label: 'Eligibility & other info', done: !!(p?.eligibility) },
    { label: 'Documents uploaded',       done: !!(p?.pds_path && p?.app_letter_path && p?.coe_path && p?.tor_path) },
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

async function fetchDashboardVacancies() {
  loadingVacancies.value = true
  try {
    const { data } = await axios.get('/api/vacancies', {
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
  const profileRes = await profileApi.show().catch(() => null)
  if (!profileRes?.data?.is_complete) {
    window.location.href = '/applicant/complete-profile'
    return
  }
  isComplete.value = profileRes.data.is_complete
  localStorage.setItem('profile_complete', profileRes.data.is_complete)
  window.dispatchEvent(new CustomEvent('profile-complete-changed'))
  profile.value    = profileRes.data.profile

  const appsRes = await applicationApi.myApplications().catch(() => null)
  if (appsRes) applications.value = appsRes.data

  loadingApps.value = false
  fetchDashboardVacancies()
  pageLoading.value = false
})
</script>
