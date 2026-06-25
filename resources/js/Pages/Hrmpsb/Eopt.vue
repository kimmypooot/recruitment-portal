<template>
  <HrmbsboardLayout title="EOPT Assessment" :vacancyId="props.vacancyId">
    <div class="space-y-6">

      <!-- Vacancy Banner -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="6"
        stageLabel="Ethics-Oriented Personality Test (EOPT)"
        :loading="loading"
      />

      <!-- EOPT Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 class="font-semibold text-gray-800">Ethics-Oriented Personality Test</h2>
          <span v-if="isSecretariat" class="text-xs text-gray-400">You are rating based on CSC CO results</span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Applicant</th>
                <th v-for="cat in categories" :key="cat"
                  class="px-3 py-3 text-center font-medium text-gray-600 text-xs whitespace-nowrap">
                  {{ categoryLabel(cat) }}
                </th>
                <th class="px-3 py-3 text-center font-medium text-gray-600">Remarks</th>
                <th v-if="isSecretariat" class="px-3 py-3 text-center font-medium text-gray-600">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-if="loading">
                <td :colspan="isSecretariat ? 8 : 7" class="px-4 py-8 text-center text-gray-400">Loading…</td>
              </tr>
              <tr v-else-if="applications.length === 0">
                <td :colspan="isSecretariat ? 8 : 7" class="px-4 py-8 text-center text-gray-400">No applicants for EOPT.</td>
              </tr>
              <tr v-for="app in applications" :key="app.id"
                :class="dirty(app.id) ? 'bg-amber-50/50' : 'hover:bg-gray-50'">
                <td class="px-4 py-3">
                  <span class="font-mono text-indigo-700 font-semibold">{{ app.token }}</span>
                  <span v-if="isSecretariat && app.name" class="ml-1 text-xs text-gray-400">({{ app.name }})</span>
                </td>

                <!-- Secretary: dropdown per category -->
                <template v-if="isSecretariat">
                  <td v-for="cat in categories" :key="cat" class="px-3 py-3 text-center">
                    <div class="relative group">
                      <select
                        v-model="ratings[app.id][cat]"
                        @change="markDirty(app.id)"
                        class="text-xs border border-gray-300 rounded px-1.5 py-1 w-full max-w-[110px]"
                        :class="ratingColorClass(ratings[app.id][cat])"
                      >
                        <option value="" disabled>—</option>
                        <option v-for="r in ratingLevels" :key="r" :value="r">
                          {{ ratingLabels[r] }}
                        </option>
                      </select>
                      <div v-if="ratings[app.id][cat]"
                        class="absolute z-20 bottom-full left-1/2 -translate-x-1/2 mb-1 w-56 px-2 py-1.5 bg-gray-900 text-white text-[10px] leading-tight rounded shadow-lg opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity">
                        <p class="font-semibold mb-0.5">{{ ratingLabels[ratings[app.id][cat]] }}</p>
                        <p>{{ definition(cat, ratings[app.id][cat]) }}</p>
                      </div>
                    </div>
                  </td>
                </template>

                <!-- Member: read-only color badges -->
                <template v-else>
                  <td v-for="cat in categories" :key="cat" class="px-3 py-3 text-center">
                    <div v-if="app.eopt" class="relative group inline-block">
                      <span class="inline-block w-6 h-6 rounded-full text-[10px] font-bold leading-6 text-white cursor-default"
                        :class="ratingDotClass(app.eopt[cat])">
                        {{ ratingAbbr(app.eopt[cat]) }}
                      </span>
                      <div
                        class="absolute z-20 bottom-full left-1/2 -translate-x-1/2 mb-1 w-56 px-2 py-1.5 bg-gray-900 text-white text-[10px] leading-tight rounded shadow-lg opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity">
                        <p class="font-semibold mb-0.5">{{ categoryLabel(cat) }} — {{ ratingLabels[app.eopt[cat]] }}</p>
                        <p>{{ definition(cat, app.eopt[cat]) }}</p>
                      </div>
                    </div>
                    <span v-else class="text-gray-300">—</span>
                  </td>
                </template>

                <!-- Remarks column -->
                <td class="px-3 py-3 text-center">
                  <textarea v-if="isSecretariat"
                    v-model="remarks[app.id]"
                    @input="markDirty(app.id)"
                    rows="2"
                    class="text-xs border border-gray-300 rounded px-2 py-1 w-28 resize-none"
                    placeholder="Optional…"
                  ></textarea>
                  <span v-else class="text-xs text-gray-400 truncate block max-w-[100px]">{{ app.eopt?.remarks ?? '—' }}</span>
                </td>

                <!-- Save button (secretariat only) -->
                <td v-if="isSecretariat" class="px-3 py-3 text-center">
                  <button
                    @click="saveRating(app.id)"
                    :disabled="saving[app.id] || !dirty(app.id)"
                    class="text-xs px-3 py-1 rounded font-medium transition disabled:opacity-40"
                    :class="dirty(app.id)
                      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                      : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                  >{{ saving[app.id] ? '…' : 'Save' }}</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <!-- Navigation -->
      <div class="flex justify-between pt-2">
        <a :href="`/hrmpsb/bei-rating/${vacancyId}`" class="btn-secondary">← BEI Rating</a>
        <a :href="`/hrmpsb/background-check/${vacancyId}`" class="btn-primary">Background Investigation →</a>
      </div>
    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import api from '@/services/api'

const props = defineProps({ vacancyId: Number })

const loading = ref(true)
const error = ref(null)
const vacancy = ref(null)
const applications = ref([])
const isSecretariat = ref(false)
const categories = ref([])
const ratingLevels = ['very_high', 'high', 'average', 'low', 'very_low']
const ratingLabels = ref({})
const definitionsMap = ref({})
const saving = reactive({})
const dirtyMap = reactive({})

const ratings = reactive({})
const remarks = reactive({})

function categoryLabel(cat) {
  const map = {
    emotional_stability: 'Emotional\nStability',
    extraversion: 'Extraversion',
    openness_to_experience: 'Openness\nto Experience',
    agreeableness: 'Agreeableness',
    conscientiousness: 'Conscientiousness',
  }
  return map[cat] ?? cat
}

function definition(cat, rating) {
  return definitionsMap.value[cat]?.[rating] ?? ''
}

function ratingAbbr(rating) {
  return ({ very_high: 'VH', high: 'H', average: 'A', low: 'L', very_low: 'VL' })[rating] ?? '—'
}

function ratingDotClass(rating) {
  return {
    very_high: 'bg-emerald-600',
    high:      'bg-green-500',
    average:   'bg-amber-400',
    low:       'bg-orange-500',
    very_low:  'bg-red-500',
  }[rating] ?? 'bg-gray-300'
}

function ratingColorClass(rating) {
  if (!rating) return ''
  return {
    very_high: 'bg-emerald-50 border-emerald-300 text-emerald-800',
    high:      'bg-green-50 border-green-300 text-green-800',
    average:   'bg-amber-50 border-amber-300 text-amber-800',
    low:       'bg-orange-50 border-orange-300 text-orange-800',
    very_low:  'bg-red-50 border-red-300 text-red-800',
  }[rating] ?? ''
}

function dirty(id) {
  return !!dirtyMap[id]
}

function markDirty(id) {
  dirtyMap[id] = true
}

function initApp(app) {
  if (!ratings[app.id]) {
    ratings[app.id] = {
      emotional_stability: app.eopt?.emotional_stability ?? '',
      extraversion: app.eopt?.extraversion ?? '',
      openness_to_experience: app.eopt?.openness_to_experience ?? '',
      agreeableness: app.eopt?.agreeableness ?? '',
      conscientiousness: app.eopt?.conscientiousness ?? '',
    }
  }
  if (remarks[app.id] === undefined) {
    remarks[app.id] = app.eopt?.remarks ?? ''
  }
}

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/eopt/${props.vacancyId}`)
    vacancy.value = data.vacancy
    applications.value = data.applications
    isSecretariat.value = data.is_secretariat
    categories.value = data.categories
    ratingLabels.value = data.rating_labels
    definitionsMap.value = data.definitions

    data.applications.forEach(app => {
      initApp(app)
      dirtyMap[app.id] = false
    })
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load EOPT data.'
  } finally {
    loading.value = false
  }
}

async function saveRating(appId) {
  saving[appId] = true
  error.value = null
  try {
    await api.post(`/eopt/${props.vacancyId}`, {
      application_id: appId,
      ...ratings[appId],
      remarks: remarks[appId] || null,
    })
    dirtyMap[appId] = false
    const app = applications.value.find(a => a.id === appId)
    if (app) {
      app.eopt = {
        emotional_stability: ratings[appId].emotional_stability,
        extraversion: ratings[appId].extraversion,
        openness_to_experience: ratings[appId].openness_to_experience,
        agreeableness: ratings[appId].agreeableness,
        conscientiousness: ratings[appId].conscientiousness,
        remarks: remarks[appId] || null,
      }
    }
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to save EOPT rating.'
  } finally {
    saving[appId] = false
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
</style>
