<template>
<<<<<<< HEAD
  <HrmbsboardLayout title="BEI Rating" :vacancyId="props.vacancyId">
    <div class="space-y-6">

      <!-- Vacancy Banner -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="3"
        stageLabel="Behavioral Event Interview (BEI)"
        :loading="loading"
      />

      <!-- Lock / secretariat actions -->
      <div class="flex items-center justify-between">
        <div></div>
=======
  <HrmbsboardLayout title="BEI Rating">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">BEI Rating</h1>
          <p v-if="vacancy" class="text-sm text-gray-500 mt-1">{{ vacancy.position_title }}</p>
        </div>
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
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

<<<<<<< HEAD
      <!-- No competencies warning -->
      <div v-if="!loading && !competencyCount"
        class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
          <p class="text-sm font-semibold text-amber-800">No competencies assigned to this position</p>
          <p class="text-xs text-amber-700 mt-0.5">
            An administrator must assign competencies to this vacancy before BEI ratings can be submitted.
          </p>
        </div>
      </div>

      <!-- Applicant Tabs -->
      <div v-if="applications.length && competencyCount" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

=======
      <!-- Applicant Tabs -->
      <div v-if="applications.length" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
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

<<<<<<< HEAD
        <!-- Selected applicant panel -->
        <div v-if="selected" class="p-6">

          <!-- ── Secretariat: consolidated view ── -->
          <div v-if="isSecretariat" class="space-y-4">
=======
        <!-- Selected applicant rating form -->
        <div v-if="selected" class="p-6">
          <div v-if="isSecretariat" class="space-y-4">
            <!-- Secretariat: consolidated view -->
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
            <h3 class="font-semibold text-gray-800">All Evaluator Ratings</h3>
            <div v-if="selected.all_ratings?.length" class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
<<<<<<< HEAD
                    <th class="px-3 py-2 text-left text-gray-600 whitespace-nowrap">Evaluator</th>
                    <th v-for="(comp, key) in competencies" :key="key"
                      class="px-3 py-2 text-center text-gray-600 text-xs whitespace-nowrap">
                      <div>{{ comp.name }}</div>
                      <div class="text-[10px] font-normal text-gray-400 mt-0.5">
                        {{ comp.group }} · L{{ comp.level }}
                      </div>
                    </th>
                    <th class="px-3 py-2 text-center text-gray-600 whitespace-nowrap">Average</th>
                    <th class="px-3 py-2 text-center text-gray-600 whitespace-nowrap">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="rating in selected.all_ratings" :key="rating.evaluator"
                    class="hover:bg-gray-50">
                    <td class="px-3 py-2 font-medium text-gray-800 whitespace-nowrap">{{ rating.evaluator }}</td>
                    <td v-for="(comp, key) in competencies" :key="key" class="px-3 py-2 text-center">
                      <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold"
=======
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
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
                        :class="scoreClass(rating.competency_scores?.[key])">
                        {{ rating.competency_scores?.[key] ?? '—' }}
                      </span>
                    </td>
<<<<<<< HEAD
                    <td class="px-3 py-2 text-center font-semibold text-indigo-700">
                      {{ rating.total_rating ?? '—' }}
                    </td>
                    <td class="px-3 py-2 text-center">
                      <span v-if="rating.locked"
                        class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-100 text-green-700">
                        Locked
                      </span>
                      <span v-else
                        class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">
                        Draft
                      </span>
=======
                    <td class="px-3 py-2 text-center font-semibold text-indigo-700">{{ rating.total_rating ?? '—' }}</td>
                    <td class="px-3 py-2 text-center">
                      <span v-if="rating.locked" class="text-green-600 text-xs font-medium">✓</span>
                      <span v-else class="text-amber-500 text-xs">Draft</span>
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="text-gray-400 text-sm">No ratings submitted yet.</p>
          </div>

<<<<<<< HEAD
          <!-- ── Member: own rating form ── -->
          <div v-else>
            <h3 class="font-semibold text-gray-800 mb-5">Your BEI Rating</h3>

            <form v-if="!beiLocked" @submit.prevent="submitRating" class="space-y-6">

              <!-- Competencies grouped by category -->
              <div v-for="(group, groupName) in groupedCompetencies" :key="groupName" class="space-y-3">
                <div class="flex items-center gap-2">
                  <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded"
                    :class="groupColor(groupName)">
                    {{ groupName }}
                  </span>
                  <div class="flex-1 h-px bg-gray-100"></div>
                </div>

                <div v-for="(comp, key) in group" :key="key"
                  class="flex items-start gap-4 p-3 rounded-lg border border-gray-100 bg-gray-50/50">
                  <!-- Label -->
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ comp.name }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                      Required level:
                      <span class="font-semibold text-indigo-600">
                        {{ levelLabel(comp.level) }}
                      </span>
                    </p>
                    <p v-if="comp.description" class="text-[11px] text-gray-400 mt-0.5 leading-snug">
                      {{ comp.description }}
                    </p>
                  </div>
                  <!-- Score buttons -->
                  <div class="flex gap-1.5 shrink-0">
                    <button
                      v-for="n in 5"
                      :key="n"
                      type="button"
                      @click="ratingForm.competency_scores[key] = n"
                      class="w-9 h-9 rounded-full border text-sm font-bold transition-all"
                      :class="ratingForm.competency_scores[key] === n
                        ? scoreButtonActive(n)
                        : 'border-gray-300 text-gray-500 hover:border-indigo-400 hover:text-indigo-600'"
                    >{{ n }}</button>
                  </div>
                </div>
              </div>

              <!-- Remarks -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea v-model="ratingForm.remarks" rows="3" class="input w-full"
                  placeholder="Behavioral observations, notable responses…"></textarea>
              </div>

              <!-- Score summary -->
              <div v-if="scoredCount" class="flex items-center gap-3 p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                <span class="text-sm text-indigo-700 font-medium">Running average:</span>
                <span class="text-lg font-bold text-indigo-700">{{ runningAverage }}</span>
                <span class="text-xs text-indigo-400">({{ scoredCount }} / {{ competencyCount }} rated)</span>
              </div>

              <div class="flex justify-end gap-3">
                <button type="button" @click="saveDraft" class="btn-secondary">Save Draft</button>
                <button type="submit" :disabled="submitting || scoredCount < competencyCount" class="btn-primary">
                  {{ submitting ? 'Submitting…' : 'Submit Rating' }}
                </button>
              </div>
              <p v-if="scoredCount < competencyCount" class="text-xs text-amber-600 text-right -mt-3">
                Rate all {{ competencyCount }} competencies before submitting.
              </p>
            </form>

            <!-- Locked / submitted view -->
            <div v-else-if="selected.my_rating" class="space-y-4">
              <div v-for="(group, groupName) in groupedCompetencies" :key="groupName" class="space-y-2">
                <div class="flex items-center gap-2">
                  <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded"
                    :class="groupColor(groupName)">
                    {{ groupName }}
                  </span>
                  <div class="flex-1 h-px bg-gray-100"></div>
                </div>
                <div v-for="(comp, key) in group" :key="key"
                  class="flex items-center gap-4 px-3 py-2 rounded-lg border border-gray-100">
                  <span class="flex-1 text-sm text-gray-700">{{ comp.name }}</span>
                  <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold"
                    :class="scoreClass(selected.my_rating?.competency_scores?.[key])">
                    {{ selected.my_rating?.competency_scores?.[key] ?? '—' }}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-3 p-3 bg-indigo-50 rounded-lg border border-indigo-100 mt-2">
                <span class="text-sm text-indigo-700 font-medium">Your average:</span>
                <span class="text-lg font-bold text-indigo-700">{{ selected.my_rating?.total_rating ?? '—' }}</span>
              </div>
            </div>

=======
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
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
            <p v-else class="text-gray-400 text-sm">No rating submitted.</p>
          </div>
        </div>
      </div>

<<<<<<< HEAD
      <p v-else-if="!loading && competencyCount" class="text-gray-400 text-sm">
        No qualified applicants to rate.
      </p>
=======
      <p v-else-if="!loading" class="text-gray-400 text-sm">No qualified applicants to rate.</p>
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <!-- Navigation -->
      <div class="flex justify-between pt-2">
        <a :href="`/hrmpsb/exam-results/${vacancyId}`" class="btn-secondary">← Exam Results</a>
        <a :href="`/hrmpsb/deliberation/${vacancyId}`" class="btn-primary">Deliberation →</a>
      </div>
<<<<<<< HEAD

=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
<<<<<<< HEAD
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
import { useEvaluationStore } from '@/stores/evaluation'
import api from '@/services/api'

const props = defineProps({ vacancyId: Number })

<<<<<<< HEAD
const evalStore    = useEvaluationStore()
const loading      = ref(true)
const submitting   = ref(false)
const locking      = ref(false)
const confirmLock  = ref(false)
const error        = ref(null)
const vacancy      = ref(null)
const applications = ref([])
const competencies = ref({})   // { key: { name, group, level, description } }
const isSecretariat = ref(false)
const beiLocked    = ref(false)
const selectedId   = ref(null)

const selected = computed(() => applications.value.find(a => a.id === selectedId.value) ?? null)

const ratingForm = ref({ competency_scores: {}, remarks: '' })

// ── Competency helpers ────────────────────────────────────────────────────────
const competencyCount = computed(() => Object.keys(competencies.value).length)

const groupedCompetencies = computed(() => {
  const groups = {}
  for (const [key, comp] of Object.entries(competencies.value)) {
    const g = comp.group ?? 'Core'
    if (!groups[g]) groups[g] = {}
    groups[g][key] = comp
  }
  return groups
})

const scoredCount = computed(() =>
  Object.values(ratingForm.value.competency_scores).filter(v => v >= 1).length
)

const runningAverage = computed(() => {
  const scores = Object.values(ratingForm.value.competency_scores).filter(v => v >= 1)
  if (!scores.length) return '—'
  return (scores.reduce((s, v) => s + v, 0) / scores.length).toFixed(2)
})

const LEVEL_LABELS = { 1: 'Basic', 2: 'Intermediate', 3: 'Advanced', 4: 'Superior' }
function levelLabel(n) { return LEVEL_LABELS[n] ?? `Level ${n}` }

function groupColor(group) {
  return {
    'Core':           'bg-blue-100 text-blue-700',
    'Organizational': 'bg-violet-100 text-violet-700',
    'Leadership':     'bg-amber-100 text-amber-700',
    'Technical':      'bg-teal-100 text-teal-700',
  }[group] ?? 'bg-gray-100 text-gray-600'
}

=======
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

>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
function scoreClass(score) {
  if (!score) return 'bg-gray-100 text-gray-500'
  if (score >= 4) return 'bg-green-100 text-green-800'
  if (score >= 3) return 'bg-blue-100 text-blue-800'
  return 'bg-amber-100 text-amber-800'
}

<<<<<<< HEAD
function scoreButtonActive(n) {
  if (n >= 4) return 'bg-green-500 text-white border-green-500'
  if (n >= 3) return 'bg-blue-500 text-white border-blue-500'
  return 'bg-amber-400 text-white border-amber-400'
}

// ── Data load ─────────────────────────────────────────────────────────────────
=======
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

>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/bei-ratings/${props.vacancyId}`)
<<<<<<< HEAD
    vacancy.value      = data.vacancy
    applications.value = data.applications
    competencies.value = data.competencies ?? {}
=======
    vacancy.value = data.vacancy
    applications.value = data.applications
    competencies.value = data.competencies
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
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

<<<<<<< HEAD
watch(selectedId, (id) => {
  if (!id) return
  const draft    = evalStore.getBeiDraft(id)
  const existing = applications.value.find(a => a.id === id)?.my_rating
  ratingForm.value = {
    competency_scores: draft?.competency_scores ?? existing?.competency_scores ?? {},
    remarks:           draft?.remarks           ?? existing?.remarks           ?? '',
  }
})

// ── Actions ───────────────────────────────────────────────────────────────────
=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
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
