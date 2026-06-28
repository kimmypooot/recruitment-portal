<template>
  <HrmbsboardLayout title="Final Deliberation" :vacancyId="props.vacancyId">
    <div class="space-y-6 pb-20 sm:pb-6">

      <!-- Vacancy Banner -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="8"
        stageLabel="Final Deliberation &amp; Selection"
        :loading="loading"
      />

      <!-- Actions row -->
      <div class="flex items-center justify-end gap-3 flex-wrap">
        <!-- Lock button -->
        <button
          v-if="canLock && !locked"
          @click="showLockConfirm = true"
          class="btn-primary"
        >Lock Deliberation</button>
        <!-- Unmask button -->
        <button
          v-if="canUnmask && !allUnmasked"
          @click="confirmUnmask = true"
          class="btn-danger"
        >Reveal Identities</button>
        <!-- Generate Comparative Assessment Result -->
        <button
          v-if="locked && canGenerateCAR && !carExists"
          @click="generateCAR"
          :disabled="carGenerating"
          class="btn-success"
        >
          <span v-if="carGenerating" class="flex items-center gap-1.5">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Generating…
          </span>
          <span v-else>Generate Comparative Assessment Result</span>
        </button>
        <!-- Download CAR -->
        <a
          v-if="locked && carExists"
          :href="carDownloadUrl"
          class="btn-success inline-flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Download Comparative Assessment Result
        </a>
        <!-- Forward to Appointing Authority -->
        <a
          v-if="locked && carExists"
          :href="`/hrmpsb/appointing-authority/${vacancyId}`"
          class="btn-primary"
        >Forward to Appointing Authority →</a>
      </div>

      <!-- Lock banner -->
      <div v-if="locked" class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl px-4 py-3">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        Deliberation results have been locked. No further changes are allowed.
      </div>

      <!-- Lock confirmation -->
      <div v-if="showLockConfirm" class="bg-amber-50 border border-amber-300 rounded-xl p-4 flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <p class="text-sm text-amber-800 font-medium">
          Locking will prevent any further changes to deliberation results and decisions. This action cannot be undone.
        </p>
        <div class="flex gap-2">
          <button @click="showLockConfirm = false" class="btn-secondary">Cancel</button>
          <button @click="lockDeliberation" :disabled="locking" class="btn-primary">
            {{ locking ? 'Locking…' : 'Confirm Lock' }}
          </button>
        </div>
      </div>

      <!-- Unmask confirmation -->
      <div v-if="confirmUnmask" class="bg-red-50 border border-red-300 rounded-xl p-4 flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
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
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-clip">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="font-semibold text-gray-800">Comparative Assessment</h2>
        </div>
        <div class="overflow-x-auto overflow-y-visible">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Applicant</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">QS</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">TWE</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">CBWE Avg</th>
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
                  {{ app.cbwe_average ?? '—' }}
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
                        class="absolute z-50 top-full left-1/2 -translate-x-1/2 mt-1.5 w-56 px-2.5 py-2 bg-gray-900 text-white text-[11px] leading-tight rounded-lg shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-normal">
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
                    :disabled="locked"
                    class="text-xs border border-gray-300 rounded px-2 pr-7 py-1"
                  >
                    <option value="">—</option>
                    <option value="endorsed">Endorsed</option>
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
                    :disabled="locked"
                    class="w-14 text-xs border border-gray-300 rounded px-2 py-1 text-center disabled:bg-gray-100 disabled:text-gray-400"
                    placeholder="—"
                  />
                  <span v-else>{{ app.deliberation?.rank ?? '—' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <textarea
                    v-if="canDecide"
                    v-model="decisions[app.id].remarks"
                    rows="2"
                    :disabled="locked"
                    class="text-xs border border-gray-300 rounded px-2 py-1 w-32 resize-none disabled:bg-gray-100 disabled:text-gray-400"
                    placeholder="Optional remarks…"
                  ></textarea>
                  <span v-else class="text-xs text-gray-500 truncate block max-w-[120px]">{{ app.deliberation?.remarks ?? '—' }}</span>
                </td>
                <td class="px-4 py-3">
                  <button
                    v-if="canDecide && decisions[app.id].action"
                    @click="saveDecision(app.id)"
                    :disabled="saving[app.id] || locked"
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
import { router } from '@inertiajs/vue3'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const props = defineProps({ vacancyId: Number })

const loading = ref(true)
const unmasking = ref(false)
const locking = ref(false)
const confirmUnmask = ref(false)
const showLockConfirm = ref(false)
const error = ref(null)
const vacancy = ref(null)
const applications = ref([])
const canUnmask = ref(false)
const canDecide = ref(false)
const canLock = ref(false)
const locked = ref(false)
const canGenerateCAR = ref(false)
const carGenerating = ref(false)
const carExists = ref(false)
const carDownloadUrl = ref('')

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
    const [delibRes, carRes] = await Promise.all([
      api.get(`/deliberation/${props.vacancyId}`),
      api.get(`/deliberation/${props.vacancyId}/comparative-assessment`).catch(() => null),
    ])
    const data = delibRes.data
    vacancy.value = data.vacancy
    applications.value = data.applications
    canUnmask.value = data.can_unmask
    canDecide.value = data.can_decide
    canLock.value = data.can_lock
    locked.value = data.locked
    canGenerateCAR.value = data.can_lock
    eoptDefinitions.value = data.eopt_definitions ?? {}

    // CAR status
    if (carRes?.data) {
      carExists.value = carRes.data.exists
      carDownloadUrl.value = carRes.data.download_url ?? ''
    }

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
    toast.success('Applicant identities revealed for deliberation.')
    await load()
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Failed to reveal identities.'
    error.value = msg
    toast.error(msg)
  } finally {
    unmasking.value = false
  }
}

async function lockDeliberation() {
  locking.value = true
  error.value = null
  try {
    await api.patch(`/deliberation/${props.vacancyId}/lock`)
    showLockConfirm.value = false
    toast.success('Deliberation results locked successfully.')
    await load()
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Failed to lock deliberation results.'
    error.value = msg
    toast.error(msg)
  } finally {
    locking.value = false
  }
}

async function generateCAR() {
  carGenerating.value = true
  error.value = null
  try {
    const { data } = await api.post(`/deliberation/${props.vacancyId}/comparative-assessment/generate`)
    carExists.value = true
    carDownloadUrl.value = data.download_url
    toast.success('Comparative Assessment Result generated successfully.')
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Failed to generate Comparative Assessment Result.'
    error.value = msg
    toast.error(msg)
  } finally {
    carGenerating.value = false
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
    toast.success('Decision saved.')
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
.btn-success {
  @apply inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition;
}
</style>
