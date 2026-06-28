<template>
  <HrmbsboardLayout title="AA Decisions">
    <div class="space-y-6 pb-20 sm:pb-6">

      <!-- Header -->
      <div>
        <h1 class="text-xl font-bold text-gray-900">Appointing Authority Decisions</h1>
        <p class="text-sm text-gray-500 mt-1">Review which applicants the Appointing Authority has selected.</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
          <div class="h-5 w-64 bg-gray-100 rounded mb-3"></div>
          <div class="h-4 w-48 bg-gray-50 rounded"></div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="vacancies.length === 0"
        class="bg-white rounded-xl border border-gray-200 p-10 text-center">
        <div class="w-14 h-14 mx-auto mb-3 rounded-xl bg-gray-50 flex items-center justify-center">
          <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-600">No decisions yet</p>
        <p class="text-xs text-gray-400 mt-1">Vacancies will appear once the Appointing Authority has rendered a decision.</p>
      </div>

      <!-- Vacancy cards -->
      <div v-else class="space-y-4">
        <div v-for="v in vacancies" :key="v.vacancy.id"
          class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-clip">
          <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">{{ v.vacancy.position_title }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">
              {{ v.vacancy.plantilla_no }} &middot; SG {{ v.vacancy.salary_grade }} &middot; {{ v.vacancy.place_of_assignment }}
            </p>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Applicant</th>
                  <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase">Decision</th>
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Decided By</th>
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="d in v.decisions" :key="d.application_id"
                  :class="d.action === 'appointed' ? 'bg-emerald-50/30' : ''">
                  <td class="px-4 py-2.5 font-medium text-gray-900 whitespace-nowrap">{{ d.applicant_name }}</td>
                  <td class="px-4 py-2.5 text-center">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full"
                      :class="d.action === 'appointed'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-gray-100 text-gray-500'">
                      <span class="w-1.5 h-1.5 rounded-full"
                        :class="d.action === 'appointed' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                      {{ d.action === 'appointed' ? 'Appointed' : 'Not Appointed' }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ d.decided_by }}</td>
                  <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ formatDate(d.decided_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <p v-if="error" class="text-sm text-red-500 bg-red-50 px-4 py-2 rounded-lg">{{ error }}</p>

    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import api from '@/services/api'

const loading = ref(true)
const error = ref(null)
const vacancies = ref([])

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

async function load() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get('/hrmpsb/appointing-authority/decisions')
    vacancies.value = data.vacancies ?? []
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load decisions.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
