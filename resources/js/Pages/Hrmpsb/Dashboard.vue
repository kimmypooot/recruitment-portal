<template>
  <HrmbsboardLayout title="Dashboard">
    <div class="max-w-5xl mx-auto">

      <!-- Board role banner -->
      <div v-if="myRole" class="mb-6 bg-[#1a5276]/5 border border-[#1a5276]/20 rounded-xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-[#1a5276] flex items-center justify-center text-white font-bold text-base flex-shrink-0">
          {{ initials }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs text-gray-400 font-medium">Your Board Role</p>
          <p class="text-sm font-semibold text-gray-900">{{ roleLabel(myRole.hrmpsb_role) }}</p>
          <p class="text-xs text-gray-500 capitalize mt-0.5">{{ myRole.member_type }} member</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
          <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse block"></span>
          Active
        </span>
      </div>

      <!-- No board role notice -->
      <div v-else-if="!loading"
        class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
          <p class="text-sm font-semibold text-amber-800">No active board role assigned</p>
          <p class="text-xs text-amber-700 mt-0.5">Contact the administrator to assign you to the HRMPSB composition.</p>
        </div>
      </div>

      <!-- Section header -->
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-sm font-semibold text-gray-900">Vacancies for Evaluation</h2>
          <p class="text-xs text-gray-400 mt-0.5">Published and closed positions requiring HRMPSB action</p>
        </div>
        <span v-if="!loading" class="text-xs text-gray-400 font-medium">
          {{ vacancies.length }} position{{ vacancies.length !== 1 ? 's' : '' }}
        </span>
      </div>

      <!-- Loading skeletons -->
      <div v-if="loading" class="space-y-3">
        <div v-for="n in 3" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
              <div class="h-3 bg-gray-100 rounded w-1/3"></div>
            </div>
            <div class="flex gap-2">
              <div v-for="i in 3" :key="i" class="h-8 w-24 bg-gray-100 rounded-lg"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="vacancies.length === 0"
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-gray-900 mb-1">No vacancies for evaluation</p>
        <p class="text-xs text-gray-400">There are no published or closed positions requiring evaluation at this time.</p>
      </div>

      <!-- Vacancy cards -->
      <div v-else class="space-y-3">
        <div v-for="v in vacancies" :key="v.id"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 transition-shadow hover:shadow-md">

          <div class="flex flex-col sm:flex-row sm:items-start gap-4">

            <!-- Vacancy info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-[#1a5276] text-white">
                  SG-{{ v.salary_grade }}
                </span>
                <span :class="v.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                  class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium capitalize">
                  {{ v.status }}
                </span>
              </div>
              <h3 class="text-sm font-semibold text-gray-900 leading-snug">{{ v.position_title }}</h3>
              <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ v.place_of_assignment }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">Deadline: {{ formatDate(v.deadline_at) }}</p>
            </div>

            <!-- Evaluation action buttons -->
            <div class="flex flex-wrap gap-2 flex-shrink-0">
              <a :href="`/hrmpsb/qs-evaluation/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-[#1a5276] hover:bg-[#154360] text-white rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                QS Evaluation
              </a>
              <a :href="`/hrmpsb/qs-results/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                QS Results
              </a>
              <a :href="`/hrmpsb/exam-results/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Exam Results
              </a>
              <a :href="`/hrmpsb/bei-rating/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                BEI Rating
              </a>
              <a v-if="isChairOrSecretary" :href="`/hrmpsb/deliberation/${v.id}`"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold border border-[#1a5276] text-[#1a5276] hover:bg-[#1a5276]/5 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Deliberation
              </a>
            </div>

          </div>
        </div>
      </div>

    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'

const loading   = ref(true)
const myRole    = ref(null)
const roleLabels = ref({})
const authUser  = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
const vacancies = ref([])

// Derive initials from composition user if loaded, else from localStorage
const initials = computed(() => {
  const name = myRole.value?.user?.name ?? authUser?.name ?? ''
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() || '?'
})

const isChairOrSecretary = computed(() =>
  myRole.value && ['chairperson', 'secretariat'].includes(myRole.value.hrmpsb_role)
)

function roleLabel(key) {
  return roleLabels.value[key] ?? key
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function loadData() {
  loading.value = true
  try {
    const [roleRes, vacRes] = await Promise.all([
      axios.get('/api/hrmpsb/my-role', { headers: authHeaders() }),
      axios.get('/api/vacancies', {
        headers: authHeaders(),
        params: { status: 'published,closed', per_page: 100 },
      }),
    ])

    myRole.value    = roleRes.data.composition
    roleLabels.value = roleRes.data.roles ?? {}
    vacancies.value  = vacRes.data.data ?? []
  } catch (e) {
    console.error('Failed to load HRMPSB dashboard', e)
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>
