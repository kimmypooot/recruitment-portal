<template>
  <PublicLayout>

    <!-- ── Hero ─────────────────────────────────────────────────────────── -->
    <section class="bg-gradient-to-br from-blue-800 via-blue-700 to-blue-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
        <div class="max-w-2xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-600/50 text-blue-100 text-xs font-medium mb-5 border border-blue-500/40">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
            {{ stats.published }} open position{{ stats.published !== 1 ? 's' : '' }} available
          </div>
          <h1 class="text-2xl sm:text-3xl lg:text-5xl font-bold leading-tight mb-4">
            CSC RO VIII<br>
            <span class="text-blue-300">Online Recruitment Portal</span>
          </h1>
          <p class="text-blue-100 text-base sm:text-lg leading-relaxed mb-8 max-w-xl">
            Browse open positions at the Civil Service Commission Regional Office
            and apply for a career in government service.
          </p>

          <!-- Inline search -->
          <div class="flex gap-2 max-w-lg">
            <div class="flex-1 relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input
                v-model="filters.search"
                @input="onSearchInput"
                type="text"
                placeholder="Search position title..."
                class="w-full pl-9 pr-4 py-3 rounded-lg text-sm text-gray-900 bg-white placeholder-gray-400 border-0 shadow focus:ring-2 focus:ring-blue-300 focus:outline-none" />
            </div>
            <button
              @click="fetchVacancies"
              class="px-5 py-3 bg-white text-blue-800 font-semibold text-sm rounded-lg shadow hover:bg-blue-50 transition-colors">
              Search
            </button>
          </div>
        </div>
      </div>

      <!-- Wave divider -->
      <div class="relative h-10 overflow-hidden">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <!-- ── Filter Bar ─────────────────────────────────────────────────── -->
    <section class="bg-gray-50 border-b border-gray-200 sticky top-16 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex flex-wrap items-center gap-3">

          <!-- Salary Grade filter -->
          <select
            v-model="filters.salary_grade"
            @change="fetchVacancies"
            class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
            <option value="">All Salary Grades</option>
            <option v-for="sg in salaryGrades" :key="sg" :value="sg">SG-{{ sg }}</option>
          </select>

          <!-- Place of Assignment filter -->
          <select
            v-model="filters.place"
            @change="fetchVacancies"
            class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
            <option value="">All Offices</option>
            <option v-for="place in uniquePlaces" :key="place" :value="place">{{ place }}</option>
          </select>

          <!-- Sort -->
          <select
            v-model="filters.sort"
            @change="fetchVacancies"
            class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
            <option value="deadline_asc">Deadline: Soonest First</option>
            <option value="deadline_desc">Deadline: Latest First</option>
            <option value="sg_desc">Salary Grade: Highest First</option>
            <option value="sg_asc">Salary Grade: Lowest First</option>
            <option value="newest">Recently Posted</option>
          </select>

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
          class="px-4 py-2 text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 rounded-lg transition-colors">
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
                  ? 'bg-blue-700 text-white shadow-sm'
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

    <!-- ── Info strip ────────────────────────────────────────────────── -->
    <section class="bg-blue-800 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
          <div>
            <p class="text-3xl font-bold text-white">{{ stats.published }}</p>
            <p class="text-blue-200 text-sm mt-1">Open Positions</p>
          </div>
          <div>
            <p class="text-3xl font-bold text-white">{{ stats.total_applications }}</p>
            <p class="text-blue-200 text-sm mt-1">Applications This Year</p>
          </div>
          <div>
            <p class="text-3xl font-bold text-white">{{ stats.appointed }}</p>
            <p class="text-blue-200 text-sm mt-1">Appointments Made</p>
          </div>
        </div>
      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { debounce } from 'lodash-es'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import VacancyCard from '@/Components/Vacancy/VacancyCard.vue'

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
const vacancies  = ref(props.initialVacancies.data ?? [])
const isLoading  = ref(false)
const pagination = ref({
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

onMounted(() => {
  // Only fetch if Inertia didn't already pass initial data
  if (!props.initialVacancies.data?.length) fetchVacancies()
})
</script>