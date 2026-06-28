<template>
  <HrmbsboardLayout title="BEI Rating" :vacancyId="props.vacancyId">
    <div class="space-y-4 pb-20 sm:pb-6">

      <!-- Vacancy Banner -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="5"
        stageLabel="Behavioral Event Interview (BEI)"
        :loading="loading"
      />

      <!-- Lock / secretariat actions -->
      <div class="flex items-center justify-between">
        <div></div>
        <button
          v-if="isSecretariat && !beiLocked"
          @click="confirmLock = true"
          class="btn-danger"
        >Lock All BEI Ratings</button>
      </div>

      <!-- Lock Confirmation -->
      <div v-if="confirmLock" class="bg-amber-50 border border-amber-300 rounded-xl p-4 flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
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

        <!-- Selected applicant panel -->
        <div v-if="selected" class="p-6">

          <!-- ── Submitted view ── -->
          <div v-if="!isSecretariat && selected.my_rating" class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div>
                <p class="text-sm font-semibold text-green-800">Rating submitted</p>
                <p class="text-xs text-green-600">Your average: <span class="font-bold">{{ selected.my_rating?.total_rating ?? '—' }}</span></p>
              </div>
            </div>
            <span v-if="selected.my_rating?.locked_at"
              class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-100 text-green-700">Locked</span>
          </div>

          <!-- ── Documents ── -->
          <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Submitted Documents</h4>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
              <button
                v-for="doc in documentTypes"
                :key="doc.key"
                @click="viewDocument(selected.document_links[doc.key])"
                :disabled="!selected.documents[doc.hasKey]"
                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition-colors"
                :class="selected.documents[doc.hasKey]
                  ? 'bg-indigo-100 text-indigo-700 border border-indigo-300 hover:bg-indigo-200 shadow-sm'
                  : 'bg-gray-100 text-gray-300 cursor-not-allowed'"
              >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                {{ doc.label }}
              </button>
            </div>
          </div>

          <!-- ── Secretariat: consolidated view ── -->
          <div v-if="isSecretariat" class="space-y-4">
            <h3 class="font-semibold text-gray-800">All Evaluator Ratings</h3>
            <div v-if="selected.all_ratings?.length" class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
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
                        :class="scoreClass(rating.competency_scores?.[key])">
                        {{ rating.competency_scores?.[key] ?? '—' }}
                      </span>
                    </td>
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
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="text-gray-400 text-sm">No ratings submitted yet.</p>
          </div>

          <!-- ── Member: own rating form ── -->
          <div v-else>
            <h3 class="font-semibold text-gray-800 mb-2">Your BEI Rating</h3>

            <div class="mb-5 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700 leading-relaxed">
              <p>You can submit and update your rating anytime before it is locked. Once the secretariat locks ratings after the interview, no further changes can be made.</p>
            </div>

            <form v-if="!beiLocked" @submit.prevent="openPreview" class="space-y-6">

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
                  class="flex items-start gap-4 p-3 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-indigo-50 hover:border-indigo-300 transition-colors">
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

            <!-- ── Preview Modal ── -->
            <div v-if="showPreview" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showPreview = false">
              <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg mx-4 max-h-[85vh] overflow-y-auto">
                <div class="p-6">
                  <h3 class="text-lg font-bold text-gray-900 mb-4">Preview Your BEI Rating</h3>

                  <div v-for="(group, groupName) in groupedCompetencies" :key="groupName" class="mb-4">
                    <div class="flex items-center gap-2 mb-2">
                      <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded"
                        :class="groupColor(groupName)">{{ groupName }}</span>
                      <div class="flex-1 h-px bg-gray-100"></div>
                    </div>
                    <div v-for="(comp, key) in group" :key="key"
                      class="flex items-center justify-between px-3 py-2 rounded-lg border border-gray-100 mb-1">
                      <span class="text-sm text-gray-700">{{ comp.name }}</span>
                      <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold"
                        :class="scoreClass(ratingForm.competency_scores[key])">
                        {{ ratingForm.competency_scores[key] ?? '—' }}
                      </span>
                    </div>
                  </div>

                  <div class="flex items-center gap-3 p-3 bg-indigo-50 rounded-lg border border-indigo-100 mb-4">
                    <span class="text-sm text-indigo-700 font-medium">Average:</span>
                    <span class="text-lg font-bold text-indigo-700">{{ runningAverage }}</span>
                    <span class="text-xs text-indigo-400">({{ scoredCount }} / {{ competencyCount }})</span>
                  </div>

                  <div v-if="ratingForm.remarks" class="mb-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Remarks</p>
                    <p class="text-sm text-gray-700 bg-gray-50 rounded-lg px-3 py-2">{{ ratingForm.remarks }}</p>
                  </div>

                  <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
                    <button type="button" @click="showPreview = false" class="btn-secondary">Cancel</button>
                    <button type="button" @click="confirmSubmit" :disabled="submitting" class="btn-primary">
                      {{ submitting ? 'Submitting…' : 'Confirm Submit' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>


          </div>
        </div>
      </div>

      <p v-else-if="!loading && competencyCount" class="text-gray-400 text-sm">
        No qualified applicants to rate.
      </p>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <!-- Navigation -->
      <div class="flex justify-between pt-2">
        <a :href="`/hrmpsb/bei-schedule/${vacancyId}`" class="btn-secondary">← BEI Schedule</a>
        <a :href="`/hrmpsb/eopt/${vacancyId}`" class="btn-primary">EOPT Assessment →</a>
      </div>
    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import { useEvaluationStore } from '@/stores/evaluation'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

const props = defineProps({ vacancyId: Number })
const toast = useToast()

const evalStore    = useEvaluationStore()
const loading      = ref(true)
const submitting   = ref(false)
const locking      = ref(false)
const confirmLock  = ref(false)
const showPreview  = ref(false)
const error        = ref(null)
const vacancy      = ref(null)
const applications = ref([])
const competencies = ref({})   // { key: { name, group, level, description } }
const isSecretariat = ref(false)
const selectedId   = ref(null)

const beiLocked = computed(() => {
  if (!selectedId.value) return false
  if (isSecretariat.value) {
    return applications.value.length > 0
      && applications.value.every(a =>
        a.all_ratings?.length > 0 && a.all_ratings.every(r => r.locked)
      )
  }
  return !!selected.value?.my_rating?.locked_at
})

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

const documentTypes = [
  { key: 'pds',        hasKey: 'has_pds',        label: 'PDS x WES',  full: 'Personal Data Sheet x Work Experience Sheet (PDS x WES)' },
  { key: 'tor',        hasKey: 'has_tor',        label: 'TOR',        full: 'Transcript of Records (TOR)' },
  { key: 'app_letter', hasKey: 'has_app_letter', label: 'App Letter', full: 'Application Letter' },
  { key: 'ipcr',       hasKey: 'has_ipcr',       label: 'IPCR',       full: 'Individual Performance Commitment Review (IPCR)' },
  { key: 'coe',        hasKey: 'has_coe',        label: 'COE',        full: 'Certificate of Eligibility (COE)' },
]

async function viewDocument(url) {
  if (!url) return
  try {
    const token = localStorage.getItem('auth_token')
    const resp = await axios.get(url, {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
      responseType: 'blob',
    })
    const mime   = resp.headers['content-type'] ?? 'application/octet-stream'
    const blob   = new Blob([resp.data], { type: mime })
    const objUrl = URL.createObjectURL(blob)
    window.open(objUrl, '_blank')
    setTimeout(() => URL.revokeObjectURL(objUrl), 60000)
  } catch {
    // silently fail
  }
}

function groupColor(group) {
  return {
    'Core':           'bg-blue-100 text-blue-700',
    'Organizational': 'bg-violet-100 text-violet-700',
    'Leadership':     'bg-amber-100 text-amber-700',
    'Technical':      'bg-teal-100 text-teal-700',
  }[group] ?? 'bg-gray-100 text-gray-600'
}

function scoreClass(score) {
  if (!score) return 'bg-gray-100 text-gray-500'
  if (score >= 4) return 'bg-green-100 text-green-800'
  if (score >= 3) return 'bg-blue-100 text-blue-800'
  return 'bg-amber-100 text-amber-800'
}

function scoreButtonActive(n) {
  if (n >= 4) return 'bg-green-500 text-white border-green-500'
  if (n >= 3) return 'bg-blue-500 text-white border-blue-500'
  return 'bg-amber-400 text-white border-amber-400'
}

// ── Data load ─────────────────────────────────────────────────────────────────
async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/bei-ratings/${props.vacancyId}`)
    vacancy.value      = data.vacancy
    applications.value = data.applications
    competencies.value = data.competencies ?? {}
    isSecretariat.value = data.is_secretariat
    if (applications.value.length && !selectedId.value) {
      selectedId.value = applications.value[0].id
    }
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load BEI data.'
  } finally {
    loading.value = false
  }
}

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
function saveDraft() {
  evalStore.saveBeiDraft(selectedId.value, { ...ratingForm.value })
}

function openPreview() {
  showPreview.value = true
}

async function confirmSubmit() {
  submitting.value = true
  showPreview.value = false
  error.value = null
  try {
    await api.post('/bei-ratings', {
      application_id: selectedId.value,
      ...ratingForm.value,
    })
    evalStore.clearBeiDraft(selectedId.value)
    await load()
    toast.success('BEI rating submitted successfully.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to submit rating.')
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
    toast.success('BEI ratings locked successfully.')
    await load()
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Failed to lock ratings.'
    error.value = msg
    toast.error(msg)
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
