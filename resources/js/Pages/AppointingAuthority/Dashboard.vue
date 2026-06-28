<template>
  <AppointingAuthorityLayout>
    <div class="mb-6">
      <h1 class="text-xl font-bold text-gray-900">Appointments</h1>
      <p class="text-sm text-gray-500 mt-1">Review endorsed applicants and render your decision</p>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="bg-white rounded-xl p-5 border border-gray-200 animate-pulse">
        <div class="h-5 w-64 bg-gray-100 rounded mb-3"></div>
        <div class="h-4 w-48 bg-gray-50 rounded"></div>
      </div>
    </div>

    <div v-else-if="vacancies.length === 0"
      class="bg-white rounded-xl border border-gray-200 p-10 text-center">
      <div class="w-14 h-14 mx-auto mb-3 rounded-xl bg-amber-50 flex items-center justify-center">
        <svg class="w-7 h-7 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <p class="text-sm font-medium text-gray-600">No pending appointments</p>
      <p class="text-xs text-gray-400 mt-1">Vacancies will appear once the HRMPSB forwards endorsed applicants.</p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="v in vacancies" :key="v.id"
        class="bg-white rounded-xl border border-gray-200 p-5 flex items-center justify-between gap-4 flex-wrap hover:shadow-sm transition">
        <div class="min-w-0">
          <h3 class="font-semibold text-gray-900 truncate">{{ v.position_title }}</h3>
          <p class="text-sm text-gray-400 mt-0.5">
            {{ v.plantilla_no }} &middot; SG {{ v.salary_grade }} &middot; {{ v.place_of_assignment }}
          </p>
          <p class="text-xs text-gray-400 mt-2">
            {{ v.endorsed_count }} endorsed &middot; {{ v.decided_count }} decided
          </p>
        </div>
        <Link :href="`/appointing-authority/${v.id}`"
          class="shrink-0 px-4 py-2 bg-[#0f2a44] text-white text-sm font-medium rounded-lg hover:bg-[#1a3a5c] transition inline-flex items-center gap-1.5">
          Review
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </Link>
      </div>
    </div>
  </AppointingAuthorityLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppointingAuthorityLayout from '@/Layouts/AppointingAuthorityLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const vacancies = ref([])

async function load() {
  loading.value = true
  try {
    const { data: stages } = await api.get('/hrmpsb/pipeline-stages', { params: { vacancy_ids: '__all__' } })
    const readyIds = Object.entries(stages).filter(([, s]) => s.deliberation_exists).map(([id]) => Number(id))
    if (readyIds.length === 0) { loading.value = false; return }
    const { data: vacs } = await api.get('/vacancies', { params: { ids: readyIds.join(','), per_page: 50 } })
    const raw = vacs.data ?? vacs ?? []
    const results = await Promise.all(raw.map(async (v) => {
      try {
        const { data } = await api.get(`/deliberation/${v.id}/appointing-authority`)
        return { ...v, endorsed_count: data.applications?.length ?? 0, decided_count: data.applications?.filter(a => a.aa_decision)?.length ?? 0 }
      } catch { return { ...v, endorsed_count: 0, decided_count: 0 } }
    }))
    vacancies.value = results
  } catch { vacancies.value = [] }
  finally { loading.value = false }
}

onMounted(load)
</script>
