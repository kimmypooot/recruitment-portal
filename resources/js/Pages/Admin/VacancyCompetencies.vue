<template>
  <AdminLayout>

      <!-- Page header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Vacancy Competencies</h1>
        <p class="text-sm text-gray-500 mt-1">Assign competency requirements and proficiency levels to each vacant position.</p>
      </div>

      <div class="mb-4 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
        <strong>Note:</strong> Competencies can also be managed directly from the
        <Link href="/admin/vacancies" class="underline font-medium">Vacancies</Link> page.
        Changes made here will be reflected there and vice versa.
      </div>

      <!-- Vacancy selector -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Vacancy</label>
        <div class="flex flex-wrap gap-3 items-end">
          <div class="flex-1 min-w-64">
            <input v-model="vacancySearch" @input="onVacancySearch" type="text"
              placeholder="Search by position title…"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" />
          </div>
          <select v-model="selectedVacancyId" @change="loadVacancyCompetencies"
            class="flex-1 min-w-64 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-700 focus:ring-2 focus:ring-[#2a338f] focus:outline-none">
            <option value="">— Choose a vacancy —</option>
            <option v-for="v in filteredVacancies" :key="v.id" :value="v.id">
              {{ v.position_title }} (SG-{{ v.salary_grade }}) — {{ v.place_of_assignment }}
            </option>
          </select>
        </div>
        <p v-if="selectedVacancyId && currentVacancy"
          class="text-xs text-gray-500 mt-2">
          Status: <span class="font-medium capitalize">{{ currentVacancy.status }}</span>
          · Deadline: {{ formatDate(currentVacancy.deadline_at) }}
        </p>
      </div>

      <!-- Competency panel -->
      <div v-if="selectedVacancyId" class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- All competencies (left) -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
          <div class="p-5 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Available Competencies</h2>
            <p class="text-xs text-gray-400 mt-0.5">Click a competency to add it, then set the proficiency level.</p>
          </div>

          <div class="p-5 space-y-5">
            <div v-for="groupName in groupOrder" :key="groupName">
              <h3 class="text-xs font-semibold text-[#2a338f] uppercase tracking-wider mb-2">{{ groupName }}</h3>
              <div class="space-y-1">
                <div v-for="comp in competenciesByGroup[groupName]" :key="comp.competency_key"
                  @click="toggleCompetency(comp)"
                  :class="[
                    'flex items-center justify-between gap-3 p-2.5 rounded-lg cursor-pointer transition-colors',
                    isAssigned(comp.competency_key)
                      ? 'bg-[#2a338f]/5 border border-[#2a338f]/20'
                      : 'hover:bg-gray-50 border border-transparent'
                  ]">
                  <div class="flex items-center gap-2.5 min-w-0">
                    <div :class="isAssigned(comp.competency_key) ? 'bg-[#2a338f]' : 'bg-gray-200'"
                      class="w-4 h-4 rounded flex-shrink-0 flex items-center justify-center transition-colors">
                      <svg v-if="isAssigned(comp.competency_key)" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                    </div>
                    <span class="text-sm truncate" :class="isAssigned(comp.competency_key) ? 'font-medium text-gray-900' : 'text-gray-600'">
                      {{ comp.competency_name }}
                    </span>
                  </div>
                  <span v-if="isAssigned(comp.competency_key)"
                    :class="levelBadgeClass(getAssigned(comp.competency_key)?.level)"
                    class="flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-semibold">
                    {{ levelLabel(getAssigned(comp.competency_key)?.level) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Assigned competencies (right) -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col">
          <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h2 class="text-sm font-semibold text-gray-900">Assigned Requirements</h2>
              <p class="text-xs text-gray-400 mt-0.5">{{ draftAssignments.length }} competenc{{ draftAssignments.length === 1 ? 'y' : 'ies' }} required</p>
            </div>
            <button v-if="draftAssignments.length" @click="saveAssignments" :disabled="saving"
              class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-[#2a338f] hover:bg-[#1e2570] disabled:opacity-60 rounded-lg transition-colors">
              <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ saving ? 'Saving…' : 'Save Changes' }}
            </button>
          </div>

          <div v-if="!draftAssignments.length"
            class="flex-1 flex flex-col items-center justify-center py-16 text-center px-5">
            <svg class="w-10 h-10 text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm text-gray-400">No competencies assigned yet.</p>
            <p class="text-xs text-gray-300 mt-1">Click competencies on the left to add them.</p>
          </div>

          <div v-else class="flex-1 overflow-y-auto divide-y divide-gray-50">
            <div v-for="item in draftAssignments" :key="item.competency_key"
              class="p-4 flex items-center gap-3">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ item.competency_name }}</p>
                <p class="text-xs text-gray-400">{{ item.competency_group }}</p>
              </div>
              <select v-model="item.level"
                class="text-xs border border-gray-200 rounded-lg px-2 pr-7 py-1.5 bg-white text-gray-700 focus:ring-2 focus:ring-[#2a338f] focus:outline-none">
                <option :value="1">1 – Basic</option>
                <option :value="2">2 – Intermediate</option>
                <option :value="3">3 – Advanced</option>
                <option :value="4">4 – Superior</option>
              </select>
              <button @click="removeAssignment(item.competency_key)"
                class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Save feedback via toast -->
        </div>

      </div>

      <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-center py-24 text-center">
        <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <p class="text-sm font-semibold text-gray-900 mb-1">Select a vacancy to manage its competencies</p>
        <p class="text-xs text-gray-400">Use the dropdown above to choose a position.</p>
      </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const groupOrder = ['Core', 'Organizational', 'Leadership', 'Technical']

// ── Auth ────────────────────────────────────────────────────────────────────────
const authToken = localStorage.getItem('auth_token') ?? ''
function authHeaders() {
  return authToken ? { Authorization: `Bearer ${authToken}` } : {}
}

// ── Vacancies ───────────────────────────────────────────────────────────────────
const allVacancies       = ref([])
const vacancySearch      = ref('')
const selectedVacancyId  = ref('')
const filteredVacancies  = computed(() => {
  if (!vacancySearch.value.trim()) return allVacancies.value
  const q = vacancySearch.value.toLowerCase()
  return allVacancies.value.filter(v =>
    v.position_title.toLowerCase().includes(q) || v.place_of_assignment?.toLowerCase().includes(q)
  )
})
const currentVacancy = computed(() =>
  allVacancies.value.find(v => v.id === Number(selectedVacancyId.value)) ?? null
)

function onVacancySearch() {
  selectedVacancyId.value = ''
}

// ── Competencies ────────────────────────────────────────────────────────────────
const allCompetencies   = ref([])
const draftAssignments  = ref([])   // [{ competency_key, competency_name, competency_group, level }]
const saving            = ref(false)

const competenciesByGroup = computed(() => {
  const map = {}
  for (const g of groupOrder) {
    map[g] = allCompetencies.value.filter(c => c.competency_group === g)
  }
  return map
})

function isAssigned(key) {
  return draftAssignments.value.some(d => d.competency_key === key)
}

function getAssigned(key) {
  return draftAssignments.value.find(d => d.competency_key === key)
}

function toggleCompetency(comp) {
  if (isAssigned(comp.competency_key)) {
    removeAssignment(comp.competency_key)
  } else {
    draftAssignments.value.push({
      competency_key:   comp.competency_key,
      competency_name:  comp.competency_name,
      competency_group: comp.competency_group,
      level:            1,
    })
  }
}

function removeAssignment(key) {
  draftAssignments.value = draftAssignments.value.filter(d => d.competency_key !== key)
}

async function loadVacancyCompetencies() {
  if (!selectedVacancyId.value) return
  draftAssignments.value = []
  try {
    const { data } = await axios.get(`/api/admin/competencies/vacancy/${selectedVacancyId.value}`, {
      headers: authHeaders(),
    })
    draftAssignments.value = (data.data ?? []).map(vc => ({
      competency_key:   vc.competency_key,
      competency_name:  vc.competency_name,
      competency_group: vc.competency_group,
      level:            vc.competency_level,
    }))
  } catch (e) {
    toast.error('Failed to load competencies for this vacancy.')
  }
}

async function saveAssignments() {
  saving.value = true
  try {
    await axios.post(`/api/admin/competencies/vacancy/${selectedVacancyId.value}/sync`, {
      competencies: draftAssignments.value.map(d => ({
        competency_key: d.competency_key,
        level: d.level,
      })),
    }, { headers: authHeaders() })
    toast.success('Competency requirements saved.')
  } catch (e) {
    toast.error(e?.response?.data?.message ?? 'Failed to save competencies.')
  } finally {
    saving.value = false
  }
}

// ── Helpers ─────────────────────────────────────────────────────────────────────
function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
}

function levelLabel(level) {
  return ['', 'Basic', 'Intermediate', 'Advanced', 'Superior'][level] ?? level
}

function levelBadgeClass(level) {
  return {
    1: 'bg-gray-100 text-gray-600',
    2: 'bg-blue-100 text-blue-700',
    3: 'bg-violet-100 text-violet-700',
    4: 'bg-amber-100 text-amber-700',
  }[level] ?? 'bg-gray-100 text-gray-600'
}

// ── Init ─────────────────────────────────────────────────────────────────────────
onMounted(async () => {
  const [vacRes, compRes] = await Promise.all([
    axios.get('/api/admin/vacancies', { headers: authHeaders() }).catch(() => null),
    axios.get('/api/competencies', { headers: authHeaders() }).catch(() => null),
  ])
  if (vacRes)  allVacancies.value    = vacRes.data.data  ?? []
  if (compRes) allCompetencies.value = compRes.data.data ?? []
})
</script>
