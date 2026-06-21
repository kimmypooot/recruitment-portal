<template>
  <HrmbsboardLayout title="My Assignments">

    <div v-if="loading" class="space-y-3">
      <div v-for="n in 3" :key="n" class="h-20 bg-white rounded-xl border border-gray-200 animate-pulse"></div>
    </div>

    <div v-else-if="assignments.length === 0" class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-sm text-gray-400">
      You have no active HRMPSB assignments.
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="item in assignments" :key="item.vacancy_id"
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="font-semibold text-gray-900">{{ item.vacancy?.position_title }}</div>
          <div class="text-xs text-gray-500 mt-0.5">
            Your role: <span class="font-medium text-[#1a5276]">{{ roleLabel(item.hrmpsb_role) }}</span>
            · <span class="capitalize">{{ item.member_type }}</span>
          </div>
          <div class="text-xs text-gray-400 mt-0.5">
            Deadline: {{ formatDate(item.vacancy?.deadline_at) }}
          </div>
        </div>
        <div class="flex gap-2">
          <a :href="`/hrmpsb/qs-evaluation/${item.vacancy_id}`"
            class="px-4 py-2 text-sm bg-[#1a5276] hover:bg-[#154360] text-white font-semibold rounded-lg transition-colors">
            QS Evaluation
          </a>
          <a v-if="item.hrmpsb_role === 'secretariat'" :href="`/hrmpsb/qs-results/${item.vacancy_id}`"
            class="px-4 py-2 text-sm border border-[#1a5276] text-[#1a5276] hover:bg-blue-50 font-semibold rounded-lg transition-colors">
            View Results
          </a>
        </div>
      </div>
    </div>

  </HrmbsboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'

const loading     = ref(true)
const assignments = ref([])
const roleLabels  = ref({})

function roleLabel(key) { return roleLabels.value[key] ?? key }

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function loadAssignments() {
  try {
    const { data } = await axios.get('/api/hrmpsb/my-assignments', { headers: authHeaders() })
    assignments.value = data.assignments ?? []
    if (data.roles) roleLabels.value = data.roles
  } catch (e) {
    console.error('Failed to load assignments', e)
  } finally {
    loading.value = false
  }
}

onMounted(loadAssignments)
</script>
