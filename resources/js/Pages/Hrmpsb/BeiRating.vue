<template>
  <HrmbsboardLayout title="BEI Rating">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">BEI Rating</h1>
          <p v-if="vacancy" class="text-sm text-gray-500 mt-1">{{ vacancy.position_title }}</p>
        </div>
        <button
          v-if="isSecretariat && !beiLocked"
          @click="confirmLock = true"
          class="btn-danger"
        >Lock All BEI Ratings</button>
      </div>

      <!-- Lock Confirmation -->
      <div v-if="confirmLock" class="bg-amber-50 border border-amber-300 rounded-xl p-4 flex items-center justify-between">
        <p class="text-sm text-amber-800 font-medium">
          Locking BEI ratings is irreversible. All evaluators will lose edit access.
        </p>
        <div class="flex gap-2">
          <button @click="confirmLock = false" class="btn-secondary">Cancel</button>
          <button @click="lockRatings" :disabled="locking" class="btn-danger">
            {{ locking ? 'Locking…' : 'Confirm Lock' }}
          </button>
        </div>
      </div>

      <!-- Locked Banner -->
      <div v-if="beiLocked" class="bg-green-50 border border-green-200 rounded-xl p-3 text-sm text-green-800 font-medium">
        BEI ratings are locked.
      </div>

      <!-- Applicant Tabs -->
      <div v-if="applications.length" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Tab list -->
        <div class="flex overflow-x-auto border-b border-gray-200 bg-gray-50 px-4 gap-2 pt-3">
          <button
            v-for="app in applications"
            :key="app.id"
            @click="selectedId = app.id"
            class="px-4 py-2 text-sm font-medium rounded-t-lg whitespace-nowrap transition"
            :class="selectedId === app.id
              ? 'bg-white border border-b-white border-gray-200 text-indigo-700 -mb-px'
              : 'text-gray-500 hover:text-gray-700'"
          >
            <span class="font-mono">{{ app.token }}</span>
            <span v-if="app.unmasked" class="ml-1 text-gray-500">({{ app.name }})</span>
          </button>
        </div>

        <!-- Selected applicant rating form -->
        <div v-if="selected" class="p-6">
          <div v-if="isSecretariat" class="space-y-4">
            <!-- Secretariat: consolidated view -->
            <h3 class="font-semibold text-gray-800">All Evaluator Ratings</h3>
            <div v-if="selected.all_ratings?.length" class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 py-2 text-left text-gray-600">Evaluator</th>
                    <th v-for="(label, key) in competencies" :key="key" class="px-3 py-2 text-center text-gray-600 text-xs">
                      {{ label }}
                    </th>
                    <th class="px-3 py-2 text-center text-gray-600">Average</th>
                    <th class="px-3 py-2 text-center text-gray-600">Locked</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="rating in selected.all_ratings" :key="rating.evaluator" class="hover:bg-gray-50">
                    <td class="px-3 py-2 font-medium text-gray-800">{{ rating.evaluator }}</td>
                    <td
                      v-for="(label, key) in competencies"
                      :key="key"
                      class="px-3 py-2 text-center"
                    >
                      <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold"
                        :class="scoreClass(rating.competency_scores?.[key])">
                        {{ rating.competency_scores?.[key] ?? '—' }}
                      </span>
                    </td>
                    <td class="px-3 py-2 text-center font-semibold text-indigo-700">{{ rating.total_rating ?? '—' }}</td>
                    <td class="px-3 py-2 text-center">
                      <span v-if="rating.locked" class="text-green-600 text-xs font-medium">✓</span>
                      <span v-else class="text-amber-500 text-xs">Draft</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="text-gray-400 text-sm">No ratings submitted yet.</p>
          </div>

          <div v-else>
            <!-- Member: own rating form -->
            <h3 class="font-semibold text-gray-800 mb-4">Your BEI Rating</h3>
            <form v-if="!beiLocked" @submit.prevent="submitRating" class="space-y-4">
              <div v-for="(label, key) in competencies" :key="key" class="flex items-center gap-4">
                <label class="w-56 text-sm text-gray-700">{{ label }}</label>
                <div class="flex gap-2">
                  <button
                    v-for="n in 5"
                    :key="n"
                    type="button"
                    @click="ratingForm.competency_scores[key] = n"
                    class="w-9 h-9 rounded-full border text-sm font-bold transition"
                    :class="ratingForm.competency_scores[key] === n
                      ? 'bg-indigo-600 text-white border-indigo-600'
                      : 'border-gray-300 text-gray-600 hover:border-indigo-400'"
                  >{{ n }}</button>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (optional)</label>
                <textarea v-model="ratingForm.remarks" rows="3" class="input w-full"></textarea>
              </div>
              <div class="flex justify-end gap-3">
                <button type="button" @click="saveDraft" class="btn-secondary">Save Draft</button>
                <button type="submit" :disabled="submitting" class="btn-primary">
                  {{ submitting ? 'Submitting…' : 'Submit Rating' }}
                </button>
              </div>
            </form>
            <div v-else-if="selected.my_rating" class="space-y-3">
              <div v-for="(label, key) in competencies" :key="key" class="flex items-center gap-4">
                <span class="w-56 text-sm text-gray-700">{{ label }}</span>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold"
                  :class="scoreClass(selected.my_rating?.competency_scores?.[key])">
                  {{ selected.my_rating?.competency_scores?.[key] ?? '—' }}
                </span>
              </div>
            </div>
            <p v-else class="text-gray-400 text-sm">No rating submitted.</p>
          </div>
        </div>
      </div>

      <p v-else-if="!loading" class="text-gray-400 text-sm">No qualified applicants to rate.</p>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <!-- Navigation -->
      <div class="flex justify-between pt-2">
        <a :href="`/hrmpsb/exam-results/${vacancyId}`" class="btn-secondary">← Exam Results</a>
        <a :href="`/hrmpsb/deliberation/${vacancyId}`" class="btn-primary">Deliberation →</a>
      </div>
    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import { useEvaluationStore } from '@/stores/evaluation'
import api from '@/services/api'

const props = defineProps({ vacancyId: Number })

const evalStore = useEvaluationStore()

const loading = ref(true)
const submitting = ref(false)
const locking = ref(false)
const confirmLock = ref(false)
const error = ref(null)
const vacancy = ref(null)
const applications = ref([])
const competencies = ref({})
const isSecretariat = ref(false)
const beiLocked = ref(false)
const selectedId = ref(null)

const selected = computed(() => applications.value.find(a => a.id === selectedId.value) ?? null)

const ratingForm = ref({
  competency_scores: {},
  remarks: '',
})

function scoreClass(score) {
  if (!score) return 'bg-gray-100 text-gray-500'
  if (score >= 4) return 'bg-green-100 text-green-800'
  if (score >= 3) return 'bg-blue-100 text-blue-800'
  return 'bg-amber-100 text-amber-800'
}

watch(selectedId, (id) => {
  if (!id) return
  // Load draft or existing rating
  const draft = evalStore.getBeiDraft(id)
  const existing = applications.value.find(a => a.id === id)?.my_rating
  ratingForm.value = {
    competency_scores: draft?.competency_scores ?? existing?.competency_scores ?? {},
    remarks: draft?.remarks ?? existing?.remarks ?? '',
  }
})

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/bei-ratings/${props.vacancyId}`)
    vacancy.value = data.vacancy
    applications.value = data.applications
    competencies.value = data.competencies
    isSecretariat.value = data.is_secretariat
    beiLocked.value = data.applications.some(a =>
      a.all_ratings?.some(r => r.locked) || a.my_rating?.locked_at
    )
    if (applications.value.length && !selectedId.value) {
      selectedId.value = applications.value[0].id
    }
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load BEI data.'
  } finally {
    loading.value = false
  }
}

function saveDraft() {
  evalStore.saveBeiDraft(selectedId.value, { ...ratingForm.value })
}

async function submitRating() {
  submitting.value = true
  error.value = null
  try {
    await api.post('/bei-ratings', {
      application_id: selectedId.value,
      ...ratingForm.value,
    })
    evalStore.clearBeiDraft(selectedId.value)
    await load()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to submit rating.'
  } finally {
    submitting.value = false
  }
}

async function lockRatings() {
  locking.value = true
  error.value = null
  try {
    await api.patch(`/bei-ratings/${props.vacancyId}/lock`)
    confirmLock.value = false
    beiLocked.value = true
    await load()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to lock ratings.'
  } finally {
    locking.value = false
  }
}

onMounted(load)
</script>

<style scoped>
@reference "../../../css/app.css";
.input {
  @apply border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent;
}
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
