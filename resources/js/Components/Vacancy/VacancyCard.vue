<template>
  <article class="bg-white rounded-xl border border-gray-200 hover:border-[#2a338f]/40 hover:shadow-md transition-all duration-200 flex flex-col">

    <!-- Card header -->
    <div class="p-5 flex-1">
      <!-- SG badge + status -->
      <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-[#2a338f] text-white">
            SG-{{ vacancy.salary_grade }}
          </span>
          <StatusBadge :status="vacancy.status" />
        </div>
        <!-- Deadline countdown -->
        <span v-if="daysRemaining !== null && daysRemaining <= 7 && daysRemaining >= 0"
          :class="daysRemaining <= 3 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold">
          <span class="w-1.5 h-1.5 rounded-full animate-pulse"
            :class="daysRemaining <= 3 ? 'bg-red-500' : 'bg-amber-400'"></span>
          {{ daysRemaining === 0 ? 'Closing today' : `${daysRemaining}d left` }}
        </span>
      </div>

      <!-- Anticipated Vacancy badge -->
      <span v-if="vacancy.is_anticipated_vacancy"
        class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-100 text-amber-700 mb-1">
        Anticipated Vacancy
      </span>

      <!-- Position title -->
      <h3 class="text-base font-semibold text-gray-900 leading-snug mb-1 line-clamp-2">
        {{ vacancy.position_title }}
      </h3>

      <!-- Office / assignment -->
      <p class="text-sm text-gray-500 mb-1 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        {{ vacancy.place_of_assignment }}
      </p>

      <!-- Plantilla No. + Monthly Salary -->
      <p v-if="vacancy.plantilla_no || vacancy.monthly_salary"
        class="text-xs text-gray-400 mb-4 flex items-center gap-3">
        <span v-if="vacancy.plantilla_no" class="inline-flex items-center gap-1">
          <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
          Plantilla Item No. {{ vacancy.plantilla_no }}
        </span> |
        <span v-if="vacancy.monthly_salary" class="inline-flex items-center gap-1">
          <span class="w-3.5 h-3.5 flex items-center justify-center text-gray-300 text-[11px] font-bold">₱</span>
          {{ formatSalary(vacancy.monthly_salary) }}
        </span>
      </p>

      <!-- Key requirements -->
      <dl class="space-y-1.5">
        <div class="flex gap-2 text-xs">
          <dt class="w-24 flex-shrink-0 text-gray-400 font-medium">Education</dt>
          <dd class="text-gray-600 line-clamp-1">{{ vacancy.education_req }}</dd>
        </div>
        <div class="flex gap-2 text-xs">
          <dt class="w-24 flex-shrink-0 text-gray-400 font-medium">Experience</dt>
          <dd class="text-gray-600 line-clamp-1">{{ vacancy.experience_req }}</dd>
        </div>
        <div class="flex gap-2 text-xs">
          <dt class="w-24 flex-shrink-0 text-gray-400 font-medium">Eligibility</dt>
          <dd class="text-gray-600 line-clamp-1">{{ vacancy.eligibility_req }}</dd>
        </div>
        <div class="flex gap-2 text-xs">
          <dt class="w-24 flex-shrink-0 text-gray-400 font-medium">Training</dt>
          <dd class="text-gray-600 line-clamp-1">{{ vacancy.training_req || 'None required' }}</dd>
        </div>
      </dl>
    </div>

    <!-- Card footer -->
    <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between gap-3 bg-gray-50 rounded-b-xl">
      <div class="flex items-center gap-4">
        <div v-if="vacancy.published_at">
          <p class="text-xs text-gray-400">Published</p>
          <p class="text-xs font-semibold text-gray-700">{{ formatDate(vacancy.published_at) }}</p>
        </div>
        <div>
          <p class="text-xs text-gray-400">Deadline</p>
          <p class="text-xs font-semibold" :class="isUrgent ? 'text-red-600' : 'text-gray-700'">
            {{ formatDate(vacancy.deadline_at) }}
          </p>
        </div>
      </div>

      <!-- Logged in + showDetailFirst: fetch detail then show modal -->
      <button v-if="isLoggedIn && showDetailFirst"
        @click="openDetail"
        :disabled="loadingDetail"
        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#2a338f] hover:bg-[#1e2570] disabled:opacity-60 rounded-lg transition-colors shadow-sm">
        <svg v-if="loadingDetail" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
        <template v-else>
          View & Apply
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
          </svg>
        </template>
      </button>

      <!-- Logged in + direct: go directly to apply page -->
      <Link v-else-if="isLoggedIn"
        :href="`/vacancies/${vacancy.id}/apply`"
        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#2a338f] hover:bg-[#1e2570] rounded-lg transition-colors shadow-sm">
        View & Apply
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
        </svg>
      </Link>

      <!-- Not logged in: prompt to sign in -->
      <button v-else @click="promptLogin"
        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#2a338f] hover:bg-[#1e2570] rounded-lg transition-colors shadow-sm">
        View & Apply
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
        </svg>
      </button>
    </div>

    <!-- Vacancy detail modal -->
    <VacancyDetailModal
      v-if="showDetailModal && detailVacancy"
      :vacancy="detailVacancy"
      :applied-ids="appliedIds"
      @close="showDetailModal = false"
    />

    <!-- Auth gate modal -->
    <Teleport to="body">
      <div v-if="showAuthModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showAuthModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
          <div class="w-14 h-14 rounded-full bg-[#2a338f]/10 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <h3 class="text-base font-bold text-gray-900 mb-1">Sign in to apply</h3>
          <p class="text-sm text-gray-500 mb-1 font-medium line-clamp-2">{{ vacancy.position_title }}</p>
          <p class="text-xs text-gray-400 mb-6">You need an account to apply for this position. It only takes a minute to register.</p>
          <div class="flex flex-col gap-2">
            <Link :href="`/login?next=/vacancies/${vacancy.id}/apply`"
              class="w-full py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors">
              Sign in
            </Link>
            <Link :href="`/register?next=/vacancies/${vacancy.id}/apply`"
              class="w-full py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold rounded-lg transition-colors">
              Create an Account
            </Link>
          </div>
          <button @click="showAuthModal = false" class="mt-4 text-xs text-gray-400 hover:text-gray-600 transition-colors">
            Cancel
          </button>
        </div>
      </div>
    </Teleport>

  </article>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import VacancyDetailModal from '@/Components/Vacancy/VacancyDetailModal.vue'

const props = defineProps({
  vacancy:         { type: Object,  required: true },
  authenticated:   { type: Boolean, default: false },
  showDetailFirst: { type: Boolean, default: false },
  appliedIds:      { type: Array,   default: () => [] },
})

const showAuthModal    = ref(false)
const showDetailModal  = ref(false)
const detailVacancy    = ref(null)
const loadingDetail    = ref(false)

const isLoggedIn = props.authenticated || !!localStorage.getItem('auth_token')

function promptLogin() {
  showAuthModal.value = true
}

async function openDetail() {
  if (loadingDetail.value) return
  loadingDetail.value = true
  try {
    const token = localStorage.getItem('auth_token')
    const res = await fetch(`/api/vacancies/${props.vacancy.id}`, {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    })
    const json = await res.json()
    detailVacancy.value = json.data ?? json
    showDetailModal.value = true
  } catch {
    // Fallback: use the card's partial data
    detailVacancy.value = props.vacancy
    showDetailModal.value = true
  } finally {
    loadingDetail.value = false
  }
}

const daysRemaining = computed(() => {
  if (!props.vacancy.deadline_at) return null
  const ms = new Date(props.vacancy.deadline_at) - new Date()
  return ms < 0 ? -1 : Math.ceil(ms / (1000 * 60 * 60 * 24))
})

const isUrgent = computed(() => daysRemaining.value !== null && daysRemaining.value >= 0 && daysRemaining.value <= 7)

function formatSalary(val) {
  return Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dateStr) {
  if (!dateStr) return 'No deadline set'
  return new Date(dateStr).toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric'
  })
}
</script>
