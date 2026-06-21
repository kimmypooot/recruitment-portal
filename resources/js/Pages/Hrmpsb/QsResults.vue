<template>
  <HrmbsboardLayout :title="`QS Results — ${vacancy?.position_title ?? '…'}`">

    <div v-if="loading" class="space-y-3">
      <div v-for="n in 4" :key="n" class="h-16 bg-white rounded-xl border border-gray-200 animate-pulse"></div>
    </div>

    <template v-else>
      <!-- Summary bar -->
      <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
          <div class="text-2xl font-bold text-gray-900">{{ applications.length }}</div>
          <div class="text-xs text-gray-500 mt-0.5">Total Applicants</div>
        </div>
        <div class="bg-white rounded-xl border border-green-200 shadow-sm p-4 text-center">
          <div class="text-2xl font-bold text-green-700">{{ qualifiedCount }}</div>
          <div class="text-xs text-gray-500 mt-0.5">Qualified</div>
        </div>
        <div class="bg-white rounded-xl border border-red-200 shadow-sm p-4 text-center">
          <div class="text-2xl font-bold text-red-500">{{ applications.length - qualifiedCount }}</div>
          <div class="text-xs text-gray-500 mt-0.5">Disqualified</div>
        </div>
      </div>

      <!-- Results table -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">Consolidated QS Results</h3>
          <span v-if="qsLocked" class="flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded-full">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Locked
          </span>
        </div>

        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
              <th class="px-5 py-3">Applicant</th>
              <th class="px-5 py-3 text-center">Evaluations</th>
              <th class="px-5 py-3 text-center">Qualified Votes</th>
              <th class="px-5 py-3 text-center">Result</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="app in applications" :key="app.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3.5">
                <div class="font-medium text-gray-900">{{ app.applicant?.first_name }} {{ app.applicant?.last_name }}</div>
              </td>
              <td class="px-5 py-3.5 text-center text-gray-600">{{ app.evaluation_summary?.total ?? 0 }}</td>
              <td class="px-5 py-3.5 text-center">
                <span class="text-green-700 font-semibold">{{ app.evaluation_summary?.qualified ?? 0 }}</span>
                <span class="text-gray-400"> / {{ app.evaluation_summary?.total ?? 0 }}</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span :class="app.status === 'qualified'
                  ? 'bg-green-50 text-green-700'
                  : app.status === 'disqualified'
                    ? 'bg-red-50 text-red-600'
                    : 'bg-gray-100 text-gray-600'"
                  class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize">
                  {{ app.status.replace('_', ' ') }}
                </span>
              </td>
            </tr>
            <tr v-if="!applications.length">
              <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-400">No applications found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'

const props = defineProps({ vacancyId: Number })

const loading      = ref(true)
const vacancy      = ref(null)
const applications = ref([])
const qsLocked     = ref(false)

const qualifiedCount = computed(() =>
  applications.value.filter(a => a.status === 'qualified').length
)

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function loadData() {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/qs-evaluations/${props.vacancyId}`, { headers: authHeaders() })
    vacancy.value      = data.vacancy
    applications.value = data.applications
    qsLocked.value     = data.qs_locked
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>
