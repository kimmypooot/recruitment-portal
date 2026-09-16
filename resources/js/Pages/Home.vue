<template>
  <DataPrivacyModal />
  <PublicLayout>

    <Head>
      <title>Home</title>
      <meta name="description" content="Browse open positions at the Civil Service Commission Regional Office VIII. Sign in or register to apply for a career in government service." />
      <meta property="og:title" content="Open Vacancies — CSC RO VIII - Recruitment Portal" />
      <meta property="og:description" content="Browse open government positions and submit your application online." />
      <link rel="preload" href="/images/cscbg_facade.jpeg" as="image" fetchpriority="high" />
    </Head>

    <!-- Redirect guard: show minimal loader while checking auth -->
    <div v-if="redirecting" class="min-h-[60vh] flex items-center justify-center">
      <div class="flex flex-col items-center gap-3">
        <div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-gray-400">Loading...</p>
      </div>
    </div>

    <template v-if="!redirecting">

    <!-- ── Hero ─────────────────────────────────────────────────────────── -->
    <section class="relative text-white overflow-hidden"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <!-- Brand gradient overlay -->
      <div class="absolute inset-0" style="background: linear-gradient(160deg, rgba(26,31,94,0.93) 0%, rgba(42,51,143,0.87) 55%, rgba(30,37,112,0.95) 100%);"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
        <div class="grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,36rem)] lg:gap-14">

          <div class="text-center lg:text-left">
            <!-- Logo -->
            <div class="flex items-center justify-center mb-8 lg:justify-start">
              <img src="/images/hero-logos.png" alt="CSC Logo, Bagong Pilipinas, Lingkod Bayani" class="h-16 sm:h-20 w-auto drop-shadow-md" />
            </div>

            <!-- Headline -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold leading-tight tracking-tight mb-4">
              CSC RO VIII
              <span class="block text-white/60 font-semibold text-[1.9rem] sm:text-[2.4rem] lg:text-[3.1rem] mt-1 tracking-normal">
                Recruitment Portal
              </span>
            </h1>

            <!-- Subheadline -->
            <p class="text-white/75 text-lg sm:text-xl leading-relaxed mb-10 max-w-xl mx-auto lg:mx-0">
              Explore career opportunities at the
              <span class="hidden sm:inline"><br></span>
              <span class="font-semibold text-white">Civil Service Commission Regional Office VIII</span>.
              <span class="hidden sm:inline"><br></span>
              Sign in or create a free account to submit your application.
            </p>

            <!-- Search bar -->
            <div class="flex gap-2 max-w-lg mx-auto lg:mx-0">
              <div class="flex-1 relative">
                <Icon name="search" size="5" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" />
                <input
                  v-model="filters.search"
                  @input="onSearchInput"
                  type="text"
                  aria-label="Search position title"
                  placeholder="Search position title..."
                  class="w-full pl-11 pr-10 py-3.5 rounded-xl text-base text-gray-900 bg-white placeholder-gray-400 border-0 shadow-lg focus:ring-2 focus:ring-white/60 focus:outline-none" />
                <button v-if="filters.search" @click="clearSearch"
                  class="absolute right-3 top-1/2 -translate-y-1/2 p-0.5 rounded-full text-gray-400 hover:text-gray-700 transition-colors"
                  aria-label="Clear search">
                  <Icon name="close" size="5" />
                </button>
              </div>
              <button
                @click="fetchVacancies"
                class="px-6 py-3.5 min-w-[5.5rem] bg-accent hover:bg-accent-dark active:bg-[#b00f1b] text-white font-semibold text-base rounded-xl shadow-lg transition-colors">
                Search
              </button>
            </div>

            <a href="#vacancy-results"
              class="inline-flex items-center gap-1 mt-4 text-sm text-white/70 hover:text-white transition-colors">
              or browse all open positions
              <Icon name="chevronDown" size="3.5" />
            </a>
          </div>

          <!-- Featured photo: the facade, framed as a single print sitting above the backdrop -->
          <figure class="hidden lg:block aspect-[4/3] w-full overflow-hidden rounded-2xl bg-white/10 p-1.5 shadow-2xl ring-1 ring-white/25 backdrop-blur-sm">
            <img
              src="/images/csc-background.jpg"
              alt="The CSC RO VIII building"
              loading="lazy"
              decoding="async"
              class="size-full rounded-xl object-cover" />
          </figure>

        </div>
      </div>

      <!-- Wave divider -->
      <div class="relative h-10 overflow-hidden pointer-events-none">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB" aria-hidden="true">
          <path d="M0 40L60 36C120 32 240 20 360 18C480 16 600 24 720 28C840 32 960 30 1080 24C1200 20 1320 24 1380 28L1440 32V40H0Z"/>
        </svg>
      </div>
    </section>

    <!-- ── Filter Bar ─────────────────────────────────────────────────── -->
    <section class="bg-gray-50 border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex flex-wrap items-center gap-3">

          <!-- Salary Grade filter -->
          <div class="relative">
            <select
              v-model="filters.salary_grade"
              @change="fetchVacancies"
              class="appearance-none text-sm border border-gray-300 rounded-lg pl-3 pr-8 py-2 bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none">
              <option value="">All Salary Grades</option>
              <option v-for="sg in salaryGrades" :key="sg" :value="sg">SG-{{ sg }}</option>
            </select>
            <Icon name="chevronDown" size="3.5" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400" />
          </div>

          <!-- Place of Assignment filter -->
          <div class="relative">
            <select
              v-model="filters.place"
              @change="fetchVacancies"
              class="appearance-none text-sm border border-gray-300 rounded-lg pl-3 pr-8 py-2 bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none">
              <option value="">All Offices</option>
              <option v-for="place in uniquePlaces" :key="place" :value="place">{{ place }}</option>
            </select>
            <Icon name="chevronDown" size="3.5" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400" />
          </div>

          <!-- Sort -->
          <div class="relative">
            <select
              v-model="filters.sort"
              @change="fetchVacancies"
              class="appearance-none text-sm border border-gray-300 rounded-lg pl-3 pr-8 py-2 bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none">
              <option value="deadline_asc">Deadline: Soonest First</option>
              <option value="deadline_desc">Deadline: Latest First</option>
              <option value="sg_desc">Salary Grade: Highest First</option>
              <option value="sg_asc">Salary Grade: Lowest First</option>
              <option value="newest">Recently Posted</option>
            </select>
            <Icon name="chevronDown" size="3.5" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400" />
          </div>

          <!-- Active filter count + clear -->
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="flex items-center gap-1.5 text-sm text-red-600 hover:text-red-800 font-medium transition-colors">
            <Icon name="close" size="3.5" />
            Clear filters
          </button>

          <!-- Result count -->
          <span class="ml-auto text-sm text-gray-500" aria-live="polite">
            <template v-if="!isLoading">
              {{ pagination.total }} result{{ pagination.total !== 1 ? 's' : '' }}
            </template>
            <template v-else>
              Loading...
            </template>
          </span>
        </div>
      </div>
    </section>

    <!-- ── Vacancy Grid ───────────────────────────────────────────────── -->
    <section id="vacancy-results" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

      <!-- Error banner -->
          <div v-if="fetchError" role="alert" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Icon name="alert" size="5" class="text-red-500 flex-shrink-0" />
          <p class="text-sm text-red-700">{{ fetchError }}</p>
        </div>
        <button @click="fetchVacancies"
          class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-700 hover:text-red-900 bg-red-100 hover:bg-red-200 rounded-lg transition-colors">
          <Icon name="refresh" size="3.5" />
          Retry
        </button>
      </div>

      <SkeletonLoader v-if="isLoading" variant="card" :count="6" wrapper-class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" />

      <!-- Vacancy cards -->
      <div
        v-else-if="vacancies.length > 0"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <VacancyCard
          v-for="vacancy in vacancies"
          :key="vacancy.id"
          :vacancy="vacancy" />
      </div>

      <!-- Empty state -->
      <div v-else class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="text-base font-semibold text-gray-900 mb-1">No vacancies found</h3>
        <p class="text-sm text-gray-500 mb-5 max-w-xs">
          {{ hasActiveFilters
            ? 'No positions match your current filters. Try adjusting or clearing them.'
            : 'There are no open positions at this time. Please check back soon.' }}
        </p>
        <button
          v-if="hasActiveFilters"
          @click="clearFilters"
          class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-lg transition-colors">
          Clear filters
        </button>
      </div>

      <Pagination
        v-if="!isLoading && pagination.last_page > 1"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        :from="pagination.from"
        :to="pagination.to"
        @page-change="goToPage" />

    </section>

    <!-- ── Stats strip ──────────────────────────────────────────────── -->
    <section class="text-white bg-primary">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center divide-y sm:divide-y-0 sm:divide-x divide-white/20">
          <div class="pb-6 sm:pb-0">
            <p class="text-4xl font-bold text-white">{{ stats.published }}</p>
            <p class="text-white/75 text-sm mt-1 uppercase tracking-wide font-medium">Open Positions</p>
          </div>
          <div class="py-6 sm:py-0 sm:px-8">
            <p class="text-4xl font-bold text-white">{{ stats.total_applications }}</p>
            <p class="text-white/75 text-sm mt-1 uppercase tracking-wide font-medium">Applications This Year</p>
          </div>
          <div class="pt-6 sm:pt-0 sm:px-8">
            <p class="text-4xl font-bold text-white">{{ stats.appointed }}</p>
            <p class="text-white/75 text-sm mt-1 uppercase tracking-wide font-medium">Appointments Made</p>
          </div>
        </div>
      </div>
    </section>

    </template>

  </PublicLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { Head, router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import VacancyCard from '@/Components/Vacancy/VacancyCard.vue'
import DataPrivacyModal from '@/Components/DataPrivacyModal.vue'
import Icon from '@/Components/UI/Icon.vue'
import SkeletonLoader from '@/Components/UI/SkeletonLoader.vue'
import Pagination from '@/Components/UI/Pagination.vue'
import { formatDateLong, formatDate, daysRemaining } from '@/utils/dates'

// ── Props from Laravel (Inertia) ──────────────────────────────────────────
// These are passed by HomeController::index() via Inertia::render()
const props = defineProps({
  initialVacancies: { type: Object, default: () => ({ data: [], meta: {} }) },
  stats: {
    type: Object,
    default: () => ({ published: 0, total_applications: 0, appointed: 0 })
  },
})

// ── State ─────────────────────────────────────────────────────────────────
const redirecting  = ref(true)
const vacancies   = ref(props.initialVacancies.data ?? [])
const isLoading   = ref(!props.initialVacancies.data?.length)
const fetchError  = ref('')
const pagination  = ref({
  total:        props.initialVacancies.meta?.total        ?? 0,
  current_page: props.initialVacancies.meta?.current_page ?? 1,
  last_page:    props.initialVacancies.meta?.last_page    ?? 1,
  from:         props.initialVacancies.meta?.from         ?? 0,
  to:           props.initialVacancies.meta?.to           ?? 0,
})

const filters = reactive({
  search:       '',
  salary_grade: '',
  place:        '',
  sort:         'deadline_asc',
  page:         1,
})

// ── Derived ───────────────────────────────────────────────────────────────
const salaryGrades   = Array.from({ length: 24 }, (_, i) => i + 1)
const hasActiveFilters = computed(() =>
  filters.search || filters.salary_grade || filters.place || filters.sort !== 'deadline_asc'
)

// Extract unique office names from loaded vacancies for the filter dropdown
const uniquePlaces = computed(() => {
  const all = vacancies.value.map(v => v.place_of_assignment).filter(Boolean)
  return [...new Set(all)].sort()
})

// ── Data fetching ─────────────────────────────────────────────────────────
function clearSearch() {
  filters.search = ''
  filters.page = 1
  fetchVacancies()
}

async function fetchVacancies() {
  fetchError.value = ''
  isLoading.value = true
  try {
    const { data } = await axios.get('/api/vacancies', {
      params: {
        search:       filters.search       || undefined,
        salary_grade: filters.salary_grade || undefined,
        place:        filters.place        || undefined,
        sort:         filters.sort,
        page:         filters.page,
      }
    })
    vacancies.value  = data.data
    pagination.value = data.meta
  } catch (err) {
    fetchError.value = err.response?.data?.message || 'Failed to load vacancies. Please try again.'
  } finally {
    isLoading.value = false
  }
}

// Debounce the search so we don't fire on every keystroke
const onSearchInput = debounce(() => {
  filters.page = 1
  fetchVacancies()
}, 350)

function goToPage(page) {
  filters.page = page
  fetchVacancies()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function clearFilters() {
  filters.search       = ''
  filters.salary_grade = ''
  filters.place        = ''
  filters.sort         = 'deadline_asc'
  filters.page         = 1
  fetchVacancies()
}

onMounted(async () => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    try {
      await axios.get('/api/profile', { headers: { Authorization: `Bearer ${token}` } })
      const user = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
      const role = user?.role ?? ''
      let dest = null
      if (role === 'applicant') dest = '/applicant/dashboard'
      else if (role === 'hrmpsb') {
        // Check if user is appointing-authority
        try {
          const { data } = await axios.get('/api/hrmpsb/my-role', {
            headers: { Authorization: `Bearer ${token}` }
          })
          if (data.composition?.hrmpsb_role === 'appointing-authority') {
            dest = '/appointing-authority/dashboard'
          } else {
            dest = '/hrmpsb/dashboard'
          }
        } catch {
          dest = '/hrmpsb/dashboard'
        }
      } else if (role) dest = '/admin/dashboard'
      if (dest) { router.visit(dest); return }
    } catch {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }
  redirecting.value = false
  if (!props.initialVacancies.data?.length) fetchVacancies()
})
</script>

