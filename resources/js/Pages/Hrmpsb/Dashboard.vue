<template>
  <HrmbsboardLayout title="HRMPSB Dashboard">

    <!-- Board role banner -->
    <div v-if="myRole" class="mb-6 bg-[#2a338f]/5 border border-[#2a338f]/20 rounded-xl px-5 py-4 flex items-center gap-4">
      <div class="w-10 h-10 rounded-full bg-[#2a338f] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
        {{ initials }}
      </div>
      <div>
        <div class="text-sm font-semibold text-gray-900">{{ roleLabel(myRole.hrmpsb_role) }}</div>
        <div class="text-xs text-gray-500 capitalize">{{ myRole.member_type }} member · Active</div>
      </div>
    </div>

    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Vacancies for Evaluation</h3>

    <div v-if="loading" class="space-y-3">
      <div v-for="n in 3" :key="n" class="h-20 bg-white rounded-xl border border-gray-200 animate-pulse"></div>
    </div>

    <div v-else-if="vacancies.length === 0" class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-sm text-gray-400">
      No vacancies are currently in the evaluation stage.
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="v in vacancies" :key="v.id"
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="font-semibold text-gray-900">{{ v.position_title }}</div>
          <div class="text-xs text-gray-500 mt-0.5">
            SG {{ v.salary_grade }} · {{ v.place_of_assignment }}
          </div>
          <div class="text-xs text-gray-400 mt-0.5">
            Deadline: {{ formatDate(v.deadline_at) }}
          </div>
        </div>
        <div class="flex gap-2 flex-wrap">
          <a :href="`/hrmpsb/qs-evaluation/${v.id}`"
            class="px-4 py-2 text-sm bg-[#1a5276] hover:bg-[#154360] text-white font-semibold rounded-lg transition-colors">
            QS Evaluation
          </a>
          <a :href="`/hrmpsb/exam-results/${v.id}`"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium rounded-lg transition-colors">
            Exam Results
          </a>
          <a :href="`/hrmpsb/bei-rating/${v.id}`"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium rounded-lg transition-colors">
            BEI Rating
          </a>
          <a v-if="isChairOrSecretary" :href="`/hrmpsb/deliberation/${v.id}`"
            class="px-4 py-2 text-sm border border-[#1a5276] text-[#1a5276] hover:bg-blue-50 font-semibold rounded-lg transition-colors">
            Deliberation
          </a>
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
const vacancies = ref([])

const initials = computed(() => {
  const name = myRole.value?.user?.name ?? ''
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
})

const isChairOrSecretary = computed(() =>
  myRole.value && ['chairperson', 'secretariat'].includes(myRole.value.hrmpsb_role)
)

function roleLabel(key) { return roleLabels.value[key] ?? key }

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function loadData() {
  try {
    const [roleRes, vacRes] = await Promise.all([
      axios.get('/api/hrmpsb/my-role', { headers: authHeaders() }),
      axios.get('/api/vacancies', { headers: authHeaders(), params: { status: 'closed,published', per_page: 50 } }),
    ])
    myRole.value    = roleRes.data.composition
    roleLabels.value = roleRes.data.roles ?? {}
    vacancies.value = vacRes.data.data ?? vacRes.data
  } catch (e) {
    console.error('Failed to load dashboard', e)
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>
