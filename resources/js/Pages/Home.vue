<template>
  <DataPrivacyModal />
  <PublicLayout>

    <Head>
      <title>Home</title>
      <meta name="description" content="Browse open positions at the Civil Service Commission Regional Office VIII. Sign in or register to apply for a career in government service." />
      <meta property="og:title" content="Open Vacancies — CSC RO VIII - Recruitment Portal" />
      <meta property="og:description" content="Browse open government positions and submit your application online." />
    </Head>

    <!-- Redirect guard: show minimal loader while checking auth -->
    <div v-if="redirecting" class="min-h-[60vh] flex items-center justify-center">
      <div class="flex flex-col items-center gap-3">
        <div class="w-8 h-8 border-2 border-[#2a338f] border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-gray-400">Loading...</p>
      </div>
    </div>

    <template v-if="!redirecting">

    <!-- ── Hero ─────────────────────────────────────────────────────────── -->
    <section class="relative text-white overflow-hidden"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <!-- Brand gradient overlay -->
      <div class="absolute inset-0" style="background: linear-gradient(160deg, rgba(26,31,94,0.93) 0%, rgba(42,51,143,0.87) 55%, rgba(30,37,112,0.95) 100%);"></div>

      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">

        <!-- Logos -->
        <div class="flex items-center justify-center gap-4 sm:gap-6 mb-8">
          <img src="/images/csc-logo.png"        alt="CSC Logo"        class="h-16 sm:h-20 w-auto drop-shadow-md" />
          <img src="/images/bagong_pilipinas.png" alt="Bagong Pilipinas" class="h-16 sm:h-20 w-auto drop-shadow-md" />
          <img src="/images/lingkod_bayani.png"   alt="Lingkod Bayani"  class="h-16 sm:h-20 w-auto drop-shadow-md" />
        </div>

        <!-- Eyebrow badge -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold tracking-widest uppercase mb-6 border border-white/20 bg-white/10 text-white/85 backdrop-blur-sm">
          <span class="w-1.5 h-1.5 rounded-full bg-[#ec1c2d] animate-pulse"></span>
          {{ stats.published }} open position{{ stats.published !== 1 ? 's' : '' }} available
        </div>

        <!-- Headline -->
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight mb-4">
          CSC RO VIII
          <span class="block text-white/60 font-semibold text-[1.6rem] sm:text-[2rem] lg:text-[2.65rem] mt-1 tracking-normal">
            Recruitment Portal
          </span>
        </h1>

        <!-- Subheadline -->
        <p class="text-white/75 text-base sm:text-lg leading-relaxed mb-10 max-w-xl mx-auto">
          Explore career opportunities at the
          <span class="hidden sm:inline"><br></span>
          <span class="font-semibold text-white">Civil Service Commission Regional Office VIII</span>.
          <span class="hidden sm:inline"><br></span>
          Sign in or create a free account to submit your application.
        </p>

        <!-- Search bar -->
        <div class="flex gap-2 max-w-lg mx-auto">
          <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
              v-model="filters.search"
              @input="onSearchInput"
              type="text"
              aria-label="Search position title"
              placeholder="Search position title..."
              class="w-full pl-9 pr-4 py-3 rounded-xl text-sm text-gray-900 bg-white placeholder-gray-400 border-0 shadow-lg focus:ring-2 focus:ring-white/60 focus:outline-none" />
          </div>
          <button
            @click="fetchVacancies"
            class="px-5 py-3 min-w-[5rem] bg-[#ec1c2d] hover:bg-[#c9111f] active:bg-[#b00f1b] text-white font-semibold text-sm rounded-xl shadow-lg transition-colors">
            Search
          </button>
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
              class="appearance-none text-sm border border-gray-300 rounded-lg pl-3 pr-8 py-2 bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none">
              <option value="">All Salary Grades</option>
              <option v-for="sg in salaryGrades" :key="sg" :value="sg">SG-{{ sg }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </div>

          <!-- Place of Assignment filter -->
          <div class="relative">
            <select
              v-model="filters.place"
              @change="fetchVacancies"
              class="appearance-none text-sm border border-gray-300 rounded-lg pl-3 pr-8 py-2 bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none">
              <option value="">All Offices</option>
              <option v-for="place in uniquePlaces" :key="place" :value="place">{{ place }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </div>

          <!-- Sort -->
          <div class="relative">
            <select
              v-model="filters.sort"
              @change="fetchVacancies"
              class="appearance-none text-sm border border-gray-300 rounded-lg pl-3 pr-8 py-2 bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none">
              <option value="deadline_asc">Deadline: Soonest First</option>
              <option value="deadline_desc">Deadline: Latest First</option>
              <option value="sg_desc">Salary Grade: Highest First</option>
              <option value="sg_asc">Salary Grade: Lowest First</option>
              <option value="newest">Recently Posted</option>
            </select>
            <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </div>

          <!-- Active filter count + clear -->
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="flex items-center gap-1.5 text-sm text-red-600 hover:text-red-800 font-medium transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Clear filters
          </button>

          <!-- Result count -->
          <span class="ml-auto text-sm text-gray-500">
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
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

      <!-- Loading skeleton -->
      <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="n in 6" :key="n"
          class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
          <div class="flex gap-2 mb-3">
            <div class="h-5 w-12 bg-gray-200 rounded-md"></div>
            <div class="h-5 w-16 bg-gray-200 rounded-full"></div>
          </div>
          <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
          <div class="h-4 bg-gray-200 rounded mb-4 w-1/2"></div>
          <div class="space-y-2">
            <div class="h-3 bg-gray-100 rounded w-full"></div>
            <div class="h-3 bg-gray-100 rounded w-5/6"></div>
            <div class="h-3 bg-gray-100 rounded w-4/6"></div>
          </div>
          <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
            <div class="h-3 w-24 bg-gray-200 rounded"></div>
            <div class="h-8 w-24 bg-gray-200 rounded-lg"></div>
          </div>
        </div>
      </div>

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
          class="px-4 py-2 text-sm font-medium text-white bg-[#2a338f] hover:bg-[#1e2570] rounded-lg transition-colors">
          Clear filters
        </button>
      </div>

      <!-- ── Pagination ──────────────────────────────────────────────── -->
      <div
        v-if="!isLoading && pagination.last_page > 1"
        class="mt-10 flex items-center justify-between">

        <!-- Page info -->
        <p class="text-sm text-gray-500">
          Showing
          <span class="font-medium text-gray-700">{{ pagination.from }}</span>–<span class="font-medium text-gray-700">{{ pagination.to }}</span>
          of <span class="font-medium text-gray-700">{{ pagination.total }}</span>
        </p>

        <!-- Page buttons -->
        <div class="flex items-center gap-1">
          <!-- Prev -->
          <button
            :disabled="pagination.current_page === 1"
            @click="goToPage(pagination.current_page - 1)"
            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
          </button>

          <!-- Page numbers -->
          <template v-for="page in visiblePages" :key="page">
            <span v-if="page === '...'" class="px-2 text-gray-400 text-sm">…</span>
            <button
              v-else
              @click="goToPage(page)"
              :class="[
                'w-9 h-9 rounded-lg text-sm font-medium transition-colors',
                page === pagination.current_page
                  ? 'bg-[#2a338f] text-white shadow-sm'
                  : 'text-gray-700 hover:bg-gray-100'
              ]">
              {{ page }}
            </button>
          </template>

          <!-- Next -->
          <button
            :disabled="pagination.current_page === pagination.last_page"
            @click="goToPage(pagination.current_page + 1)"
            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
          </button>
        </div>
      </div>

    </section>

    <!-- ── Testimonials marquee ─────────────────────────────────────── -->
    <section v-if="testimonials.length >= 3 && vacancies.length > 0" class="py-14 bg-gray-50 border-t border-gray-100" style="overflow: hidden; max-width: 100vw;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8 text-center">
        <p class="text-xs font-semibold uppercase tracking-widest text-[#ec1c2d] mb-2">What Applicants Say</p>
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Real experiences, real people</h2>
        <p class="text-sm text-gray-500 mt-2">Feedback submitted by applicants after applying through this portal.</p>
      </div>

      <!-- Single scrolling row -->
      <div class="relative w-full" style="overflow: hidden;">
        <div ref="marqueeTrack"
          class="flex"
          style="width: max-content; will-change: transform;"
          @mouseenter="pauseMarquee"
          @mouseleave="resumeMarquee">
          <div v-for="(t, i) in [...testimonials, ...testimonials]" :key="'t-' + i"
            class="flex-shrink-0 w-72 sm:w-80 mx-3 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3">
            <!-- Stars -->
            <div class="flex items-center gap-0.5">
              <svg v-for="s in 5" :key="s" class="w-4 h-4"
                :class="s <= t.rating ? 'text-yellow-400' : 'text-gray-200'"
                fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </div>
            <!-- Comment -->
            <p class="text-sm text-gray-700 leading-relaxed line-clamp-3 flex-1">"{{ t.comment }}"</p>
            <!-- Author -->
            <div class="flex items-center gap-2.5 pt-2 border-t border-gray-100">
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                :style="{ backgroundColor: avatarColor(i) }">
                {{ t.display_name[0]?.toUpperCase() ?? '?' }}
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-800">{{ t.display_name }}</p>
                <p class="text-xs text-gray-400 truncate max-w-[180px]">Applied for {{ t.position_title }}</p>
              </div>
            </div>
          </div>
        </div>
        <!-- Fade edges -->
        <div class="pointer-events-none absolute inset-y-0 left-0 w-24 z-10" style="background: linear-gradient(to right, #f9fafb, transparent);"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 w-24 z-10" style="background: linear-gradient(to left, #f9fafb, transparent);"></div>
      </div>
    </section>

    <!-- ── Stats strip ──────────────────────────────────────────────── -->
    <section class="text-white" style="background-color: #2a338f;">
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
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import axios from 'axios'
import { Head, router } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import VacancyCard from '@/Components/Vacancy/VacancyCard.vue'
import DataPrivacyModal from '@/Components/DataPrivacyModal.vue'

const AVATAR_COLORS = ['#2a338f','#7c3aed','#0891b2','#059669','#d97706','#dc2626','#db2777','#0284c7','#65a30d']
function avatarColor(i) { return AVATAR_COLORS[i % AVATAR_COLORS.length] }

// ── Testimonials state (must be declared before watch) ────────────────────
const testimonials = ref([])

// ── Marquee (rAF-based) ───────────────────────────────────────────────────
const marqueeTrack = ref(null)
let marqueeOffset  = 0
let marqueeRaf     = null
let marqueePaused  = false

function tickMarquee() {
  const el = marqueeTrack.value
  if (el && !marqueePaused) {
    const halfWidth = el.scrollWidth / 2
    marqueeOffset += 0.5
    if (marqueeOffset >= halfWidth) marqueeOffset = 0
    el.style.transform = `translateX(-${marqueeOffset}px)`
  }
  marqueeRaf = requestAnimationFrame(tickMarquee)
}

function pauseMarquee()  { marqueePaused = true  }
function resumeMarquee() { marqueePaused = false }

// Start the loop once testimonials are loaded and rendered
watch(testimonials, async (val) => {
  if (val.length >= 3) {
    await nextTick()
    if (!marqueeRaf) marqueeRaf = requestAnimationFrame(tickMarquee)
  }
})

function debounce(fn, delay) {
  let timer
  return function (...args) {
    clearTimeout(timer)
    timer = setTimeout(() => fn.apply(this, args), delay)
  }
}

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

// Pagination page list with ellipsis
const visiblePages = computed(() => {
  const { current_page: cur, last_page: last } = pagination.value
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
  if (cur <= 4)  return [1, 2, 3, 4, 5, '...', last]
  if (cur >= last - 3) return [1, '...', last - 4, last - 3, last - 2, last - 1, last]
  return [1, '...', cur - 1, cur, cur + 1, '...', last]
})

// ── Data fetching ─────────────────────────────────────────────────────────
async function fetchVacancies() {
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
    console.error('Failed to fetch vacancies:', err)
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

onUnmounted(() => {
  if (marqueeRaf) cancelAnimationFrame(marqueeRaf)
})

onMounted(async () => {
  // Fetch public testimonials (fire-and-forget; section hides itself if empty)
  axios.get('/api/testimonials').then(({ data }) => { testimonials.value = data }).catch(() => {})

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

