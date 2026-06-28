<template>
  <AdminLayout title="Applicant Feedbacks">
    <div class="space-y-6">

      <!-- Page header -->
      <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-xl font-bold text-gray-900">Applicant Feedbacks</h1>
          <p class="text-sm text-gray-500 mt-0.5">Experience ratings submitted by applicants after applying for a position.</p>
        </div>
        <div v-if="!loading && stats.total" class="flex-shrink-0 flex items-center gap-2 px-3 py-1.5 bg-gray-100 rounded-lg">
          <span class="text-xs text-gray-500">{{ stats.total }} total response{{ stats.total !== 1 ? 's' : '' }}</span>
        </div>
      </div>

      <!-- Error -->
      <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ error }}</div>

      <!-- Stat section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Average rating card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex items-center gap-5">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0" style="background-color: #fefce8; border: 1px solid #fef08a;">
            <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Average Rating</p>
            <div class="flex items-baseline gap-2">
              <p class="text-4xl font-extrabold text-gray-900">{{ stats.average ?? '—' }}</p>
              <p class="text-sm text-gray-400 font-medium">/ 5</p>
            </div>
            <div class="flex items-center gap-0.5 mt-1.5">
              <svg v-for="s in 5" :key="s" class="w-3.5 h-3.5"
                :class="s <= Math.round(stats.average ?? 0) ? 'text-yellow-400' : 'text-gray-200'"
                fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Rating distribution -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-4">Rating Breakdown</p>
          <div class="space-y-2.5">
            <div v-for="star in [5,4,3,2,1]" :key="star" class="flex items-center gap-3">
              <div class="flex items-center gap-1 w-16 flex-shrink-0">
                <span class="text-xs font-semibold text-gray-600 w-3 text-right">{{ star }}</span>
                <svg class="w-3.5 h-3.5 text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </div>
              <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500"
                  :class="ratingBarColor(star)"
                  :style="{ width: ratingPct(star) + '%' }">
                </div>
              </div>
              <span class="text-xs text-gray-500 w-8 text-right flex-shrink-0">
                {{ stats.rating_counts?.[star] ?? 0 }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4 space-y-3">
        <div class="flex flex-wrap items-center gap-3">
          <!-- Search -->
          <div class="relative flex-1 min-w-[220px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input v-model="filters.search" type="text" placeholder="Search applicant name or position…"
              class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none"
              @input="debouncedLoad" />
          </div>

          <!-- Date range -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-1.5 sm:gap-2 w-full sm:w-auto">
            <div class="flex items-center gap-1.5 sm:gap-2">
              <span class="text-xs text-gray-400 whitespace-nowrap">From</span>
              <input v-model="filters.date_from" type="date" @change="loadFeedbacks"
                class="flex-1 sm:flex-none text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none text-gray-600 min-w-0 w-full sm:w-auto" />
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2">
              <span class="text-xs text-gray-400 whitespace-nowrap">to</span>
              <input v-model="filters.date_to" type="date" @change="loadFeedbacks"
                class="flex-1 sm:flex-none text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none text-gray-600 min-w-0 w-full sm:w-auto" />
            </div>
          </div>

          <button v-if="hasActiveFilter" @click="clearFilters"
            class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-red-600 transition-colors whitespace-nowrap">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Clear filters
          </button>
        </div>

        <!-- Star quick-filter pills -->
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-xs text-gray-400 font-medium">Filter by rating:</span>
          <button @click="setRatingFilter('')"
            class="px-3 py-1 text-xs font-semibold rounded-full border transition-colors"
            :class="filters.rating === ''
              ? 'bg-[#2a338f] text-white border-[#2a338f]'
              : 'border-gray-200 text-gray-600 hover:border-[#2a338f] hover:text-[#2a338f]'">
            All
          </button>
          <button v-for="star in [5,4,3,2,1]" :key="star"
            @click="setRatingFilter(String(star))"
            class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full border transition-colors"
            :class="filters.rating === String(star)
              ? 'text-white border-transparent ' + ratingActiveBg(star)
              : 'border-gray-200 text-gray-600 hover:border-yellow-400 hover:text-yellow-600'">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            {{ star }}
            <span v-if="stats.rating_counts?.[star] !== undefined" class="opacity-70">({{ stats.rating_counts[star] }})</span>
          </button>
        </div>
      </div>

      <!-- Feedback list -->
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

        <!-- Loading skeleton -->
        <div v-if="loading" class="divide-y divide-gray-100">
          <div v-for="n in 5" :key="n" class="p-5 animate-pulse">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-full bg-gray-100 flex-shrink-0"></div>
              <div class="flex-1 space-y-2">
                <div class="h-3.5 bg-gray-100 rounded w-40"></div>
                <div class="h-3 bg-gray-100 rounded w-56"></div>
                <div class="h-3 bg-gray-100 rounded w-full max-w-xs mt-1"></div>
              </div>
              <div class="flex gap-0.5">
                <div v-for="s in 5" :key="s" class="w-4 h-4 bg-gray-100 rounded"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Feedback cards -->
        <div v-else-if="feedbacks.length" class="divide-y divide-gray-100">
          <div v-for="(fb, idx) in feedbacks" :key="fb.id"
            class="group px-6 py-5 hover:bg-gray-50/60 transition-colors cursor-pointer"
            @click="toggleExpand(fb.id)">

            <div class="flex items-start gap-4">
              <!-- Avatar -->
              <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold text-white"
                :style="{ backgroundColor: avatarColor(fb) }">
                {{ avatarInitials(fb) }}
              </div>

              <!-- Main content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-gray-900">{{ applicantName(fb) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                      Applied for <span class="font-medium text-gray-700">{{ fb.vacancy?.position_title ?? '—' }}</span>
                    </p>
                  </div>
                  <div class="flex-shrink-0 text-right">
                    <!-- Stars + label -->
                    <div class="flex items-center justify-end gap-1.5">
                      <div class="flex items-center gap-0.5">
                        <svg v-for="s in 5" :key="s" class="w-3.5 h-3.5"
                          :class="s <= fb.rating ? 'text-yellow-400' : 'text-gray-200'"
                          fill="currentColor" viewBox="0 0 24 24">
                          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                      </div>
                      <span class="text-xs font-bold px-1.5 py-0.5 rounded-full" :class="sentimentBadge(fb.rating)">
                        {{ sentimentLabel(fb.rating) }}
                      </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1" :title="formatDateFull(fb.created_at)">
                      {{ timeAgo(fb.created_at) }}
                    </p>
                  </div>
                </div>

                <!-- Comment preview / expanded -->
                <div v-if="fb.comment" class="mt-2.5">
                  <p class="text-sm text-gray-600 leading-relaxed"
                    :class="expandedId !== fb.id ? 'line-clamp-2' : ''">
                    <svg class="inline w-3.5 h-3.5 text-gray-300 mr-1 -mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.293-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.293-3.996 5.849h3.983v10h-9.983z"/>
                    </svg>{{ fb.comment }}
                  </p>
                  <button v-if="fb.comment.length > 120"
                    @click.stop="toggleExpand(fb.id)"
                    class="mt-1 text-xs text-[#2a338f] hover:underline font-medium">
                    {{ expandedId === fb.id ? 'Show less' : 'Read more' }}
                  </button>
                </div>
                <p v-else class="mt-2 text-xs text-gray-300 italic">No comment provided</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else class="py-24 flex flex-col items-center gap-4 text-center">
          <div class="w-16 h-16 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-600">
              {{ hasActiveFilter ? 'No feedbacks match your filters' : 'No feedbacks yet' }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
              {{ hasActiveFilter ? 'Try adjusting your search or filters.' : 'Feedbacks will appear here once applicants submit their ratings.' }}
            </p>
            <button v-if="hasActiveFilter" @click="clearFilters"
              class="mt-3 text-xs font-medium text-[#2a338f] hover:underline">
              Clear all filters
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1 && !loading"
          class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-4 bg-gray-50/50">
          <p class="text-xs text-gray-500">
            Showing <span class="font-semibold text-gray-700">{{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, totalItems) }}</span> of <span class="font-semibold text-gray-700">{{ totalItems }}</span> feedbacks
          </p>
          <div class="flex items-center gap-1">
            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
              class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
              </svg>
            </button>
            <button v-for="p in visiblePages" :key="p" @click="typeof p === 'number' && goToPage(p)"
              :disabled="p === '…'"
              :class="['px-2.5 py-1 rounded-lg text-xs font-medium transition-colors',
                p === currentPage ? 'bg-[#2a338f] text-white' : p === '…' ? 'text-gray-300 cursor-default' : 'text-gray-600 hover:bg-gray-100']">
              {{ p }}
            </button>
            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
              class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { feedbackApi } from '@/services/api'

const loading     = ref(true)
const error       = ref(null)
const feedbacks   = ref([])
const stats       = ref({})
const currentPage = ref(1)
const perPage     = ref(20)
const totalItems  = ref(0)
const totalPages  = ref(1)
const expandedId  = ref(null)

const filters = reactive({
  search:    '',
  rating:    '',
  date_from: '',
  date_to:   '',
})

const hasActiveFilter = computed(() =>
  filters.search || filters.rating || filters.date_from || filters.date_to
)

const visiblePages = computed(() => {
  const total = totalPages.value
  const cur   = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  if (cur <= 4)   return [1, 2, 3, 4, 5, '…', total]
  if (cur >= total - 3) return [1, '…', total - 4, total - 3, total - 2, total - 1, total]
  return [1, '…', cur - 1, cur, cur + 1, '…', total]
})

async function loadFeedbacks() {
  loading.value = true
  try {
    const { data } = await feedbackApi.list({ page: currentPage.value, ...filters })
    feedbacks.value  = data.feedbacks.data
    totalItems.value = data.feedbacks.total
    totalPages.value = data.feedbacks.last_page
    perPage.value    = data.feedbacks.per_page
    stats.value      = data.stats
  } catch {
    feedbacks.value = []
    error.value = 'Failed to load feedbacks.'
  } finally {
    loading.value = false
  }
}

let searchTimer = null
function debouncedLoad() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { currentPage.value = 1; loadFeedbacks() }, 350)
}

function setRatingFilter(val) {
  filters.rating = val
  currentPage.value = 1
  loadFeedbacks()
}

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  loadFeedbacks()
}

function clearFilters() {
  Object.assign(filters, { search: '', rating: '', date_from: '', date_to: '' })
  currentPage.value = 1
  loadFeedbacks()
}

function toggleExpand(id) {
  expandedId.value = expandedId.value === id ? null : id
}

// ── Display helpers ──────────────────────────────────────────────────────────

function applicantName(fb) {
  const u = fb.applicant?.user
  if (!u) return '—'
  return [u.last_name, u.first_name].filter(Boolean).join(', ') || '—'
}

function avatarInitials(fb) {
  const u = fb.applicant?.user
  if (!u) return '?'
  return ((u.first_name?.[0] ?? '') + (u.last_name?.[0] ?? '')).toUpperCase() || '?'
}

const AVATAR_COLORS = ['#2a338f','#7c3aed','#0891b2','#059669','#d97706','#dc2626','#db2777']
function avatarColor(fb) {
  const id = fb.applicant_id ?? fb.id ?? 0
  return AVATAR_COLORS[id % AVATAR_COLORS.length]
}

function sentimentLabel(rating) {
  return { 5: 'Excellent', 4: 'Very Good', 3: 'Good', 2: 'Fair', 1: 'Poor' }[rating] ?? ''
}

function sentimentBadge(rating) {
  return {
    5: 'bg-green-100 text-green-700',
    4: 'bg-teal-100 text-teal-700',
    3: 'bg-yellow-100 text-yellow-700',
    2: 'bg-orange-100 text-orange-700',
    1: 'bg-red-100 text-red-700',
  }[rating] ?? 'bg-gray-100 text-gray-600'
}

function ratingPct(star) {
  const total = stats.value.total ?? 0
  if (!total) return 0
  return Math.round(((stats.value.rating_counts?.[star] ?? 0) / total) * 100)
}

function ratingBarColor(star) {
  return {
    5: 'bg-green-400',
    4: 'bg-teal-400',
    3: 'bg-yellow-400',
    2: 'bg-orange-400',
    1: 'bg-red-400',
  }[star] ?? 'bg-gray-300'
}

function ratingActiveBg(star) {
  return {
    5: 'bg-green-500',
    4: 'bg-teal-500',
    3: 'bg-yellow-500',
    2: 'bg-orange-500',
    1: 'bg-red-500',
  }[star] ?? 'bg-gray-500'
}

function formatDateFull(ts) {
  if (!ts) return ''
  return new Date(ts).toLocaleString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function timeAgo(ts) {
  if (!ts) return '—'
  const diff = Date.now() - new Date(ts).getTime()
  const mins  = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days  = Math.floor(diff / 86400000)
  if (mins < 1)   return 'just now'
  if (mins < 60)  return `${mins}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7)   return `${days}d ago`
  return new Date(ts).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(loadFeedbacks)
</script>
