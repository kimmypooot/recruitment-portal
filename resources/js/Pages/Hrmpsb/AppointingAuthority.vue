<template>
  <AppointingAuthorityLayout>
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-2 mb-1">
        <span class="w-1 h-6 bg-amber-400 rounded-full"></span>
        <h1 class="text-xl font-bold text-gray-900">{{ vacancy?.position_title ?? 'Loading…' }}</h1>
      </div>
      <p v-if="vacancy" class="text-sm text-gray-400 ml-3">
        {{ vacancy.plantilla_no }} &middot; SG {{ vacancy.salary_grade }} &middot; {{ vacancy.place_of_assignment }}
      </p>
      <p v-if="vacancy?.published_at" class="text-xs text-gray-400 ml-3 mt-1">
        Publication: {{ formatDate(vacancy.published_at) }} – {{ vacancy.deadline_at ? formatDate(vacancy.deadline_at) : 'Open' }}
      </p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="bg-white rounded-xl border border-gray-200 p-8 text-center text-sm text-gray-400">Loading…</div>

    <!-- No endorsed -->
    <div v-else-if="!hasEndorsed" class="bg-white rounded-xl border border-gray-200 p-8 text-center">
      <p class="text-sm font-medium text-gray-500">No endorsed applicants</p>
      <p class="text-xs text-gray-400 mt-1">This vacancy has no endorsed applicants from deliberation.</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-clip">
      <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
        <span class="text-sm font-semibold text-gray-800">Endorsed Applicants</span>
        <div class="flex items-center gap-3 text-xs text-gray-400">
          <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Appointed</span>
          <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span> Not Appointed</span>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gray-50">
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-8"></th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Applicant</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">QS</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">TWE</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">CBWE Avg</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">BEI</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">BI</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">EOPT</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Rank</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Deliberation</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase" style="min-width: 150px;">Your Decision</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-for="app in applications" :key="app.id">
              <!-- Main row (click to toggle) -->
              <tr @click="toggleOpen(app.id)"
                class="cursor-pointer transition-colors"
                :class="[
                  expanded[app.id] ? 'bg-blue-50/30' : '',
                  app.aa_decision?.action === 'appointed' ? 'bg-emerald-50/40' : app.aa_decision?.action === 'not_appointed' ? 'bg-gray-50/40' : 'hover:bg-gray-50/60'
                ]">
                <td class="px-4 py-3 text-center">
                  <svg class="w-3.5 h-3.5 text-gray-400 transition-transform mx-auto" :class="expanded[app.id] ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
                </td>
                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ app.name }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                    :class="app.qs_result === 'qualified' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'">
                    {{ app.qs_result ?? '—' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center font-medium" :class="(app.exam_scores?.TWE?.percentage ?? 0) >= 70 ? 'text-gray-900' : 'text-gray-400'">
                  {{ app.exam_scores?.TWE?.percentage != null ? app.exam_scores.TWE.percentage + '%' : '—' }}
                </td>
              <td class="px-4 py-3 text-center font-semibold text-gray-800">
                {{ app.cbwe_average ?? '—' }}
                </td>
                <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ app.bei_average ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                  <span v-if="app.background_investigation?.submitted" class="text-green-600 font-bold text-sm">✓</span>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <div v-if="app.eopt" class="inline-flex items-center gap-0.5">
                    <span v-for="(val, key) in app.eopt" :key="key" class="w-2 h-2 rounded-full" :class="eoptDotClass(val)"></span>
                  </div>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-center font-bold text-gray-800">{{ app.deliberation?.rank ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-indigo-50 text-indigo-700">
                    {{ app.deliberation?.action ?? '—' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center" @click.stop>
                  <div class="flex flex-col items-center gap-1.5">
                    <select
                      v-model="decisions[app.id].action"
                      class="text-xs border border-gray-200 rounded-lg px-3 py-2 w-full bg-white focus:ring-2 focus:ring-[#0f2a44]/20 focus:border-[#0f2a44] outline-none"
                      @change="decisions[app.id].dirty = true">
                      <option value="">— Select —</option>
                      <option value="appointed">Appointed</option>
                      <option value="not_appointed">Not Appointed</option>
                    </select>
                    <button v-if="decisions[app.id].dirty"
                      @click="saveDecision(app.id)" :disabled="saving[app.id]"
                      class="text-xs px-3 py-1.5 bg-[#0f2a44] text-white rounded-lg hover:bg-[#1a3a5c] disabled:opacity-50 transition font-medium">
                      {{ saving[app.id] ? '…' : 'Confirm' }}
                    </button>
                    <span v-else-if="app.aa_decision" class="inline-flex items-center gap-1 text-xs font-medium"
                      :class="app.aa_decision.action === 'appointed' ? 'text-emerald-700' : 'text-gray-500'">
                      <span class="w-2 h-2 rounded-full" :class="app.aa_decision.action === 'appointed' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                      {{ app.aa_decision.action === 'appointed' ? 'Appointed' : 'Not Appointed' }}
                      <span class="text-gray-300 font-normal">· {{ app.aa_decision.decided_by }}</span>
                    </span>
                    <span v-else class="text-xs text-gray-300 italic">Pending</span>
                  </div>
                </td>
              </tr>
              <!-- Expanded detail row -->
              <tr v-if="expanded[app.id]">
                <td colspan="11" class="px-6 py-4 bg-gray-50/70 border-b border-gray-100">
                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left: EOPT -->
                    <div v-if="app.eopt">
                      <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">EOPT Results</h4>
                      <div class="space-y-3">
                        <div v-for="(val, key) in app.eopt" :key="key" class="flex items-start gap-2.5">
                          <span class="w-3 h-3 rounded-full shrink-0 mt-0.5" :class="eoptDotClass(val)"></span>
                          <div>
                            <p class="text-sm font-medium text-gray-800">{{ eoptLabel(key) }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ eoptRatingLabel(val) }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">{{ eoptMeaning(key, val) }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- Right: BI -->
                    <div v-if="app.background_investigation?.submitted">
                      <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Background Investigation</h4>
                      <div class="space-y-4">
                        <div v-if="app.background_investigation.on_competencies">
                          <p class="text-xs font-semibold text-gray-600 mb-1">I. Competencies</p>
                          <p class="text-xs text-gray-500 leading-relaxed whitespace-pre-wrap">{{ app.background_investigation.on_competencies }}</p>
                        </div>
                        <div v-if="app.background_investigation.on_performance">
                          <p class="text-xs font-semibold text-gray-600 mb-1">II. Performance</p>
                          <p class="text-xs text-gray-500 leading-relaxed whitespace-pre-wrap">{{ app.background_investigation.on_performance }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <p v-if="error" class="mt-3 text-sm text-red-500 bg-red-50 px-4 py-2 rounded-lg">{{ error }}</p>

    <div class="mt-6">
      <a href="/appointing-authority/dashboard" class="text-sm text-gray-400 hover:text-[#0f2a44] transition inline-flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18" /></svg>
        Back to Appointments
      </a>
    </div>
  </AppointingAuthorityLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import AppointingAuthorityLayout from '@/Layouts/AppointingAuthorityLayout.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const props = defineProps({ vacancyId: Number, eoptDefinitions: Object })

const loading = ref(true)
const error = ref(null)
const vacancy = ref(null)
const applications = ref([])
const decisions = reactive({})
const saving = reactive({})
const expanded = reactive({})

const hasEndorsed = computed(() => applications.value.length > 0)

function toggleOpen(id) {
  expanded[id] = !expanded[id]
}

const eoptLabels = {
  emotional_stability: 'Emotional Stability',
  extraversion: 'Extraversion',
  openness_to_experience: 'Openness',
  agreeableness: 'Agreeableness',
  conscientiousness: 'Conscientiousness',
}

function eoptLabel(key) { return eoptLabels[key] ?? key }

function eoptRatingLabel(rating) {
  return {
    very_high: 'Very High',
    high: 'High',
    average: 'Average',
    low: 'Low',
    very_low: 'Very Low',
  }[rating] ?? rating
}

function eoptMeaning(category, rating) {
  return props.eoptDefinitions?.[category]?.[rating]
    ?? rating.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())
}

function eoptDotClass(rating) {
  return {
    very_high: 'bg-emerald-500',
    high: 'bg-green-500',
    average: 'bg-amber-400',
    low: 'bg-orange-500',
    very_low: 'bg-red-500',
  }[rating] ?? 'bg-gray-300'
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

async function load() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get(`/deliberation/${props.vacancyId}/appointing-authority`)
    vacancy.value = data.vacancy
    applications.value = data.applications
    data.applications.forEach(app => {
      decisions[app.id] = { action: app.aa_decision?.action ?? '', dirty: false }
      expanded[app.id] = false
    })
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load data.'
  } finally {
    loading.value = false
  }
}

async function saveDecision(applicationId) {
  saving[applicationId] = true
  error.value = null
  try {
    await api.patch(`/deliberation/${props.vacancyId}/appointing-authority/decide`, {
      application_id: applicationId,
      action: decisions[applicationId].action,
    })
    decisions[applicationId].dirty = false
    toast.success('Decision recorded successfully.')
    await load()
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Failed to save decision.'
    error.value = msg
    toast.error(msg)
  } finally {
    saving[applicationId] = false
  }
}

onMounted(load)
</script>
