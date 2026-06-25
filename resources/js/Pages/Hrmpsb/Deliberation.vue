<template>
  <HrmbsboardLayout title="Final Deliberation" :vacancyId="props.vacancyId">
    <div class="space-y-6">

      <!-- Vacancy Banner -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="8"
        stageLabel="Final Deliberation &amp; Selection"
        :loading="loading"
      />

      <!-- Actions row -->
      <div class="flex items-center justify-end">
        <!-- Unmask button -->
        <button
          v-if="canUnmask && !allUnmasked"
          @click="confirmUnmask = true"
          class="btn-danger"
        >Reveal Identities</button>
      </div>

      <!-- Unmask confirmation -->
      <div v-if="confirmUnmask" class="bg-red-50 border border-red-300 rounded-xl p-4 flex items-center justify-between">
        <p class="text-sm text-red-800 font-medium">
          This will reveal all applicant identities to all deliberation participants. This action cannot be undone.
        </p>
        <div class="flex gap-2">
          <button @click="confirmUnmask = false" class="btn-secondary">Cancel</button>
          <button @click="unmaskAll" :disabled="unmasking" class="btn-danger">
            {{ unmasking ? 'Revealing…' : 'Confirm Reveal' }}
          </button>
        </div>
      </div>

      <!-- Unmasked banner -->
      <div v-if="allUnmasked" class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-sm text-blue-800 font-medium">
        Applicant identities have been revealed for final deliberation.
      </div>

      <!-- Consolidated Assessment Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="font-semibold text-gray-800">Comparative Assessment</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Applicant</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">QS</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">TWE</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">CBWE</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">BEI Avg</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">BI</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">EOPT</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Action</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Rank</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Remarks</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Decision</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-if="loading">
                <td colspan="11" class="px-4 py-8 text-center text-gray-400">Loading…</td>
              </tr>
              <tr v-else-if="applications.length === 0">
                <td colspan="11" class="px-4 py-8 text-center text-gray-400">No applications in deliberation.</td>
              </tr>
              <tr v-for="app in applications" :key="app.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <div v-if="app.unmasked">
                    <p class="font-medium text-gray-900">{{ app.name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ app.token }}</p>
                  </div>
                  <span v-else class="font-mono text-indigo-700 font-semibold">{{ app.token }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                    :class="app.qs_result === 'qualified' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'">
                    {{ app.qs_result ?? '—' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  {{ app.exam_scores?.TWE?.percentage != null ? app.exam_scores.TWE.percentage + '%' : '—' }}
                </td>
                <td class="px-4 py-3 text-center">
                  {{ app.exam_scores?.CBWE?.percentage != null ? app.exam_scores.CBWE.percentage + '%' : '—' }}
                </td>
                <td class="px-4 py-3 text-center font-semibold">
                  {{ app.bei_average ?? '—' }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span v-if="app.background_investigation?.submitted"
                    class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-800 cursor-help"
                    :title="'By: ' + (app.background_investigation.investigator ?? 'N/A')"
                  >
                    ✓ Submitted
                  </span>
                  <span v-else class="text-xs text-gray-400">—</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <div v-if="app.eopt" class="flex items-center justify-center gap-1">
                    <div v-for="cat in eoptCategories" :key="cat" class="relative group">
                      <span class="inline-block w-4 h-4 rounded-full text-[8px] font-bold leading-4 text-white cursor-default"
                        :class="eoptDotClass(app.eopt[cat])"
                        :title="eoptCategoryLabel(cat) + ': ' + eoptRatingLabel(app.eopt[cat])">
                      </span>
                      <div
                        class="absolute z-20 bottom-full left-1/2 -translate-x-1/2 mb-1 w-52 px-2 py-1.5 bg-gray-900 text-white text-[10px] leading-tight rounded shadow-lg opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-normal">
                        <p class="font-semibold mb-0.5">{{ eoptCategoryLabel(cat) }} — {{ eoptRatingLabel(app.eopt[cat]) }}</p>
                        <p>{{ eoptDefinition(cat, app.eopt[cat]) }}</p>
                      </div>
                    </div>
                  </div>
                  <span v-else class="text-xs text-gray-400">—</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <select
                    v-if="canDecide"
                    v-model="decisions[app.id].action"
                    class="text-xs border border-gray-300 rounded px-2 pr-7 py-1"
                  >
                    <option value="">—</option>
                    <option value="endorsed">Endorsed</option>
                    <option value="appointed">Appointed</option>
                    <option value="not_recommended">Not Recommended</option>
                  </select>
                  <span v-else class="text-xs text-gray-500">{{ app.deliberation?.action ?? '—' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <input
                    v-if="canDecide"
                    v-model.number="decisions[app.id].rank"
                    type="number"
                    min="1"
                    class="w-14 text-xs border border-gray-300 rounded px-2 py-1 text-center"
                    placeholder="—"
                  />
                  <span v-else>{{ app.deliberation?.rank ?? '—' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <textarea
                    v-if="canDecide"
                    v-model="decisions[app.id].remarks"
                    rows="2"
                    class="text-xs border border-gray-300 rounded px-2 py-1 w-32 resize-none"
                    placeholder="Optional remarks…"
                  ></textarea>
                  <span v-else class="text-xs text-gray-500 truncate block max-w-[120px]">{{ app.deliberation?.remarks ?? '—' }}</span>
                </td>
                <td class="px-4 py-3">
                  <button
                    v-if="canDecide && decisions[app.id].action"
                    @click="saveDecision(app.id)"
                    :disabled="saving[app.id]"
                    class="text-xs px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50"
                  >{{ saving[app.id] ? '…' : 'Save' }}</button>
                  <span v-else-if="app.deliberation?.action" class="text-xs text-green-600 font-medium">✓ Saved</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <!-- Back nav -->
      <div class="flex justify-start pt-2">
        <a :href="`/hrmpsb/background-check/${vacancyId}`" class="btn-secondary">← Background Investigation</a>
      </div>
    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import api from '@/services/api'

const props = defineProps({ vacancyId: Number })

const loading = ref(true)
const unmasking = ref(false)
const confirmUnmask = ref(false)
const error = ref(null)
const vacancy = ref(null)
const applications = ref([])
const canUnmask = ref(false)
const canDecide = ref(false)

const decisions = reactive({})
const saving = reactive({})

const allUnmasked = computed(() => applications.value.every(a => a.unmasked))

const eoptCategories = ['emotional_stability', 'extraversion', 'openness_to_experience', 'agreeableness', 'conscientiousness']
const eoptDefinitions = ref({})

function eoptCategoryLabel(cat) {
  const map = {
    emotional_stability: 'Emotional Stability',
    extraversion: 'Extraversion',
    openness_to_experience: 'Openness to Experience',
    agreeableness: 'Agreeableness',
    conscientiousness: 'Conscientiousness',
  }
  return map[cat] ?? cat
}

function eoptRatingLabel(rating) {
  const map = {
    very_high: 'Very High',
    high: 'High',
    average: 'Average',
    low: 'Low',
    very_low: 'Very Low',
  }
  return map[rating] ?? rating
}

function eoptDotClass(rating) {
  return {
    very_high: 'bg-emerald-600',
    high:      'bg-green-500',
    average:   'bg-amber-400',
    low:       'bg-orange-500',
    very_low:  'bg-red-500',
  }[rating] ?? 'bg-gray-300'
}

function eoptDefinition(category, rating) {
  return eoptDefinitions.value[category]?.[rating] ?? ''
}

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/deliberation/${props.vacancyId}`)
    vacancy.value = data.vacancy
    applications.value = data.applications
    canUnmask.value = data.can_unmask
    canDecide.value = data.can_decide
    eoptDefinitions.value = data.eopt_definitions ?? {}

    // Init decision rows
    data.applications.forEach(app => {
      decisions[app.id] = {
        action: app.deliberation?.action ?? '',
        rank: app.deliberation?.rank ?? null,
        remarks: app.deliberation?.remarks ?? '',
      }
    })
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load deliberation data.'
  } finally {
    loading.value = false
  }
}

async function unmaskAll() {
  unmasking.value = true
  error.value = null
  try {
    await api.patch(`/deliberation/${props.vacancyId}/unmask`)
    confirmUnmask.value = false
    await load()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to reveal identities.'
  } finally {
    unmasking.value = false
  }
}

async function saveDecision(applicationId) {
  saving[applicationId] = true
  error.value = null
  try {
    await api.post(`/deliberation/${props.vacancyId}/decide`, {
      application_id: applicationId,
      action: decisions[applicationId].action,
      rank: decisions[applicationId].rank || null,
      remarks: decisions[applicationId].remarks || null,
    })
    await load()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to save decision.'
  } finally {
    saving[applicationId] = false
  }
}

onMounted(load)
</script>

<style scoped>
@reference "../../../css/app.css";
.btn-primary {
  @apply inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition;
}
.btn-secondary {
  @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 transition;
}
.btn-danger {
  @apply inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 transition;
}
</style>
