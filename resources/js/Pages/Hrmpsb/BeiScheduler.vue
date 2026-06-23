<template>
  <HrmbsboardLayout title="BEI Scheduler" :vacancyId="props.vacancyId">
    <div class="space-y-6">

      <!-- Vacancy Banner -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="3"
        stageLabel="Behavioral Event Interview Scheduling"
        :loading="loading"
      >
        <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
          <svg class="w-4 h-4 text-[#1a5276] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Only applicants who <strong class="text-gray-700">passed all written examinations</strong> (≥ {{ passingThreshold }}%) appear here.
          Each applicant receives one BEI schedule.
        </div>
      </VacancyBanner>

      <!-- No exam passers notice -->
      <div v-if="!loading && applications.length === 0"
        class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
          <p class="text-sm font-semibold text-amber-800">No exam passers yet</p>
          <p class="text-xs text-amber-700 mt-0.5">
            Examination scores must be encoded and applicants must pass both TWE and CBWE
            before BEI scheduling is available.
          </p>
        </div>
      </div>

      <!-- Summary cards -->
      <div v-if="!loading && applications.length" class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-gray-900">{{ applications.length }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Exam Passers</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Eligible for BEI</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-green-700">{{ scheduledCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">BEI Scheduled</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Date and venue set</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-amber-600">{{ applications.length - scheduledCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Pending Schedule</p>
          <p class="text-[10px] text-gray-400 mt-0.5">No BEI date set yet</p>
        </div>
      </div>

      <!-- Batch Schedule Panel -->
      <div v-if="canSchedule && applications.length" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <button
          @click="batchOpen = !batchOpen"
          class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-violet-600 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <div class="text-left">
              <p class="text-sm font-semibold text-gray-900">Batch Schedule BEI</p>
              <p class="text-xs text-gray-400 mt-0.5">Schedule all exam passers for the same BEI date, time, and venue</p>
            </div>
          </div>
          <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="batchOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
          </svg>
        </button>

        <div v-show="batchOpen" class="border-t border-gray-100 px-6 pb-6 pt-5">
          <form @submit.prevent="submitBatch">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
              <div>
                <label class="field-label">Date &amp; Time</label>
                <input v-model="batchForm.scheduled_at" type="datetime-local" class="input" required />
              </div>
              <div>
                <label class="field-label">Venue / Location</label>
                <input v-model="batchForm.venue" type="text" class="input" placeholder="e.g. HRMPSB Conference Room" required />
              </div>
              <div>
                <label class="field-label">Notes (optional)</label>
                <input v-model="batchForm.notes" type="text" class="input" placeholder="e.g. Bring government-issued ID" />
              </div>
            </div>
            <div class="flex items-center justify-between">
              <p v-if="batchError" class="text-sm text-red-600">{{ batchError }}</p>
              <div v-else class="text-xs text-gray-400">
                Schedules all <strong>{{ applications.length }}</strong> exam passer(s) for BEI at the same date and venue.
                Existing BEI schedules will be overwritten.
              </div>
              <button type="submit" :disabled="batchSubmitting" class="btn-primary ml-4 flex-shrink-0">
                <svg v-if="batchSubmitting" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ batchSubmitting ? 'Scheduling…' : 'Schedule All for BEI' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Individual Schedule Form -->
      <div v-if="canSchedule && applications.length" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <button
          @click="formOpen = !formOpen"
          class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-[#1a5276] flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            <div class="text-left">
              <p class="text-sm font-semibold text-gray-900">
                {{ editingId ? 'Edit BEI Schedule' : 'Add / Update BEI Schedule' }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">Set or update the BEI schedule for an individual applicant</p>
            </div>
          </div>
          <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="formOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
          </svg>
        </button>

        <div v-show="formOpen" class="border-t border-gray-100 px-6 pb-6 pt-5">
          <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
              <div>
                <label class="field-label">Applicant (Blind Code)</label>
                <select v-model="form.application_id" class="input" :disabled="!!editingId" required>
                  <option value="">— Select applicant —</option>
                  <option v-for="app in applications" :key="app.id" :value="app.id">
                    {{ app.token }}{{ getSchedule(app.id) ? ' (scheduled)' : '' }}
                  </option>
                </select>
              </div>
              <div>
                <label class="field-label">Date &amp; Time</label>
                <input v-model="form.scheduled_at" type="datetime-local" class="input" required />
              </div>
              <div>
                <label class="field-label">Venue / Location</label>
                <input v-model="form.venue" type="text" class="input" placeholder="e.g. HRMPSB Conference Room" required />
              </div>
              <div>
                <label class="field-label">Notes (optional)</label>
                <input v-model="form.notes" type="text" class="input" placeholder="Panel members, instructions…" />
              </div>
            </div>
            <div class="flex items-center justify-end gap-2">
              <button v-if="editingId" type="button" @click="cancelEdit" class="btn-secondary">Cancel</button>
              <p v-if="formError" class="text-sm text-red-600 mr-3">{{ formError }}</p>
              <button type="submit" :disabled="formSubmitting" class="btn-primary">
                <svg v-if="formSubmitting" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ formSubmitting ? 'Saving…' : (editingId ? 'Update Schedule' : 'Save Schedule') }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Schedule Table -->
      <div v-if="!loading && applications.length" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Table header with selection action bar -->
        <div class="px-6 py-4 border-b border-gray-100">
          <!-- Selection bar -->
          <div v-if="selected.length > 0" class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <div class="w-5 h-5 rounded bg-[#1a5276] flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
              </div>
              <span class="text-sm font-semibold text-gray-900">
                {{ selected.length }} applicant{{ selected.length !== 1 ? 's' : '' }} selected
              </span>
              <span class="text-xs text-gray-400 hidden sm:inline">— hold Shift and click to select a range</span>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
              <button
                @click="sendNotification"
                :disabled="!!notifying"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-violet-600 hover:bg-violet-700 text-white disabled:opacity-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                {{ notifying ? 'Sending…' : 'Notify BEI Schedule' }}
              </button>
              <button
                @click="clearSelection"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Clear
              </button>
            </div>
          </div>

          <!-- Default header -->
          <div v-else class="flex items-center justify-between gap-3 flex-wrap">
            <div>
              <h2 class="text-sm font-bold text-gray-900">BEI Schedule — Exam Passers</h2>
              <p class="text-xs text-gray-400 mt-0.5">Check rows to select, then use Notify to send BEI schedule notices</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
              <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>Scheduled
              </span>
              <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-amber-300 inline-block"></span>Pending
              </span>
            </div>
          </div>
        </div>

        <!-- Notification result toast -->
        <div v-if="notifyResult"
          class="mx-6 mt-4 px-4 py-3 rounded-xl text-sm font-medium flex items-center justify-between gap-3"
          :class="notifyResult.skipped > 0 ? 'bg-amber-50 border border-amber-200 text-amber-800' : 'bg-green-50 border border-green-200 text-green-800'">
          <div class="flex items-center gap-2">
            <svg v-if="notifyResult.skipped === 0" class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg v-else class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span>
              <strong>{{ notifyResult.notified }}</strong> BEI notification{{ notifyResult.notified !== 1 ? 's' : '' }} queued.
              <span v-if="notifyResult.skipped > 0">
                {{ notifyResult.skipped }} skipped (no BEI schedule set).
              </span>
            </span>
          </div>
          <button @click="notifyResult = null" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/70">
                <!-- Select-all checkbox -->
                <th class="pl-6 pr-2 py-3 w-10">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    :indeterminate="someSelected"
                    @change="toggleAll"
                    class="rounded border-gray-300 text-[#1a5276] focus:ring-[#1a5276] cursor-pointer"
                  />
                </th>
                <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider w-12">#</th>
                <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Blind Code</th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">TWE %</th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">CBWE %</th>
                <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">BEI Schedule</th>
                <th v-if="canSchedule" class="pl-3 pr-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="(app, idx) in applications"
                :key="app.id"
                class="hover:bg-gray-50/50 transition-colors cursor-pointer"
                :class="[
                  isSelected(app.id) ? 'bg-[#1a5276]/5' : '',
                  getSchedule(app.id) ? 'bg-green-50/20' : '',
                ]"
                @click.prevent="handleRowClick($event, idx)"
              >
                <!-- Checkbox -->
                <td class="pl-6 pr-2 py-4" @click.stop>
                  <input
                    type="checkbox"
                    :checked="isSelected(app.id)"
                    @click="handleCheckbox($event, idx)"
                    class="rounded border-gray-300 text-[#1a5276] focus:ring-[#1a5276] cursor-pointer"
                  />
                </td>

                <td class="px-3 py-4 text-sm text-gray-400 font-medium">{{ idx + 1 }}</td>

                <td class="px-3 py-4">
                  <span class="text-sm font-bold font-mono text-[#1a5276]">{{ app.token }}</span>
                </td>

                <!-- TWE score -->
                <td class="px-3 py-4 text-center">
                  <span v-if="getExamScore(app, 'TWE') !== null"
                    class="text-sm font-bold"
                    :class="getExamScore(app, 'TWE') >= passingThreshold ? 'text-green-700' : 'text-red-600'">
                    {{ getExamScore(app, 'TWE') }}%
                  </span>
                  <span v-else class="text-gray-300 text-sm">—</span>
                </td>

                <!-- CBWE score -->
                <td class="px-3 py-4 text-center">
                  <span v-if="getExamScore(app, 'CBWE') !== null"
                    class="text-sm font-bold"
                    :class="getExamScore(app, 'CBWE') >= passingThreshold ? 'text-green-700' : 'text-red-600'">
                    {{ getExamScore(app, 'CBWE') }}%
                  </span>
                  <span v-else class="text-gray-300 text-sm">—</span>
                </td>

                <!-- BEI Schedule -->
                <td class="px-3 py-4">
                  <div v-if="getSchedule(app.id)" class="flex flex-col gap-0.5">
                    <span class="text-sm font-semibold text-gray-900">{{ formatDt(getSchedule(app.id).scheduled_at) }}</span>
                    <span class="text-xs text-gray-500">{{ getSchedule(app.id).venue }}</span>
                    <span v-if="getSchedule(app.id).notes" class="text-[10px] text-gray-400 italic">{{ getSchedule(app.id).notes }}</span>
                  </div>
                  <div v-else class="flex items-center gap-1.5 text-xs text-amber-500">
                    <span class="w-2 h-2 rounded-full bg-amber-300 inline-block flex-shrink-0"></span>
                    Not yet scheduled
                  </div>
                </td>

                <!-- Actions -->
                <td v-if="canSchedule" class="pl-3 pr-6 py-4" @click.stop>
                  <div class="flex items-center justify-end gap-3">
                    <button
                      @click="editSchedule(app)"
                      class="text-[11px] font-semibold text-[#1a5276] hover:underline transition-colors">
                      {{ getSchedule(app.id) ? 'Edit' : 'Schedule' }}
                    </button>
                    <button
                      v-if="getSchedule(app.id)"
                      @click="deleteSchedule(getSchedule(app.id))"
                      :disabled="deleting[getSchedule(app.id).id]"
                      class="text-[11px] font-semibold text-red-500 hover:underline disabled:opacity-50 transition-colors">
                      {{ deleting[getSchedule(app.id).id] ? '…' : 'Clear' }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-400">
          <span>{{ applications.length }} exam passer{{ applications.length !== 1 ? 's' : '' }} eligible for BEI</span>
          <span>Blind Codes preserve applicant anonymity throughout the evaluation process.</span>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex items-center justify-between pt-2">
        <a :href="`/hrmpsb/exam-results/${props.vacancyId}`" class="btn-secondary">
          <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
          </svg>
          Exam Results
        </a>
        <a :href="`/hrmpsb/bei-rating/${props.vacancyId}`" class="btn-primary">
          BEI Rating
          <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
          </svg>
        </a>
      </div>

    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted, nextTick } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import api from '@/services/api'

const props = defineProps({ vacancyId: Number })

const loading          = ref(true)
const vacancy          = ref(null)
const applications     = ref([])
const schedules        = ref([])
const canSchedule      = ref(false)
const passingThreshold = ref(70)

const batchOpen       = ref(false)
const batchSubmitting = ref(false)
const batchError      = ref(null)
const batchForm       = ref({ scheduled_at: '', venue: '', notes: '' })

const formOpen       = ref(true)
const formSubmitting = ref(false)
const formError      = ref(null)
const editingId      = ref(null)
const form           = ref({ application_id: '', scheduled_at: '', venue: '', notes: '' })

const deleting     = reactive({})

// ── Selection state ───────────────────────────────────────────────────────
const selected       = ref([])
const lastCheckedIdx = ref(null)
const notifying      = ref(false)
const notifyResult   = ref(null)

// ── Computed ──────────────────────────────────────────────────────────────

const scheduleMap = computed(() => {
  const map = {}
  for (const s of schedules.value) {
    map[s.application_id] = s
  }
  return map
})

const scheduledCount = computed(() =>
  applications.value.filter(a => scheduleMap.value[a.id]).length
)

const allSelected = computed(() =>
  applications.value.length > 0 && selected.value.length === applications.value.length
)
const someSelected = computed(() =>
  selected.value.length > 0 && selected.value.length < applications.value.length
)

// ── Selection helpers ─────────────────────────────────────────────────────

function isSelected(id) {
  return selected.value.includes(id)
}

function toggleAll() {
  if (allSelected.value) {
    selected.value = []
  } else {
    selected.value = applications.value.map(a => a.id)
  }
  lastCheckedIdx.value = null
}

function clearSelection() {
  selected.value = []
  lastCheckedIdx.value = null
}

function handleCheckbox(e, idx) {
  e.stopPropagation()
  const id = applications.value[idx].id

  if (e.shiftKey && lastCheckedIdx.value !== null) {
    const from = Math.min(lastCheckedIdx.value, idx)
    const to   = Math.max(lastCheckedIdx.value, idx)
    const adding = !isSelected(id)
    for (let i = from; i <= to; i++) {
      const rid = applications.value[i].id
      if (adding && !isSelected(rid)) {
        selected.value.push(rid)
      } else if (!adding) {
        selected.value = selected.value.filter(x => x !== rid)
      }
    }
  } else {
    if (isSelected(id)) {
      selected.value = selected.value.filter(x => x !== id)
    } else {
      selected.value.push(id)
    }
  }
  lastCheckedIdx.value = idx
}

function handleRowClick(e, idx) {
  handleCheckbox(e, idx)
}

// ── Schedule helpers ──────────────────────────────────────────────────────

function getSchedule(appId) {
  return scheduleMap.value[appId] ?? null
}

function getExamScore(app, type) {
  const r = app.exam_results?.find(r => r.exam_type === type)
  return r ? r.percentage : null
}

function formatDt(str) {
  if (!str) return '—'
  return new Date(str).toLocaleString('en-PH', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function toDatetimeLocal(str) {
  if (!str) return ''
  const d = new Date(str)
  const pad = n => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

// ── Edit helpers ──────────────────────────────────────────────────────────

function editSchedule(app) {
  const existing = getSchedule(app.id)
  editingId.value = existing?.id ?? null
  form.value = {
    application_id: app.id,
    scheduled_at: existing ? toDatetimeLocal(existing.scheduled_at) : '',
    venue: existing?.venue ?? '',
    notes: existing?.notes ?? '',
  }
  formOpen.value = true
  nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))
}

function cancelEdit() {
  editingId.value = null
  form.value = { application_id: '', scheduled_at: '', venue: '', notes: '' }
}

// ── Data ──────────────────────────────────────────────────────────────────

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/bei-schedules/${props.vacancyId}`)
    vacancy.value          = data.vacancy
    applications.value     = data.applications
    schedules.value        = data.schedules
    canSchedule.value      = data.can_schedule
    passingThreshold.value = data.passing_threshold ?? 70
    selected.value         = selected.value.filter(id => data.applications.some(a => a.id === id))
  } catch (e) {
    console.error('Failed to load BEI schedules', e)
  } finally {
    loading.value = false
  }
}

async function submitBatch() {
  batchSubmitting.value = true
  batchError.value = null
  try {
    const { data } = await api.post(`/bei-schedules/batch/${props.vacancyId}`, batchForm.value)
    batchForm.value = { scheduled_at: '', venue: '', notes: '' }
    batchOpen.value = false
    await load()
    alert(`Successfully scheduled ${data.scheduled} applicant(s) for BEI.`)
  } catch (e) {
    batchError.value = e.response?.data?.message ?? 'Failed to batch schedule.'
  } finally {
    batchSubmitting.value = false
  }
}

async function submitForm() {
  formSubmitting.value = true
  formError.value = null
  try {
    if (editingId.value) {
      await api.put(`/bei-schedules/${editingId.value}`, {
        scheduled_at: form.value.scheduled_at,
        venue: form.value.venue,
        notes: form.value.notes,
      })
    } else {
      await api.post('/bei-schedules', form.value)
    }
    cancelEdit()
    await load()
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Failed to save schedule.'
  } finally {
    formSubmitting.value = false
  }
}

async function deleteSchedule(schedule) {
  if (!confirm('Remove this BEI schedule for the applicant?')) return
  deleting[schedule.id] = true
  try {
    await api.delete(`/bei-schedules/${schedule.id}`)
    await load()
  } catch (e) {
    alert(e.response?.data?.message ?? 'Failed to delete schedule.')
  } finally {
    delete deleting[schedule.id]
  }
}

async function sendNotification() {
  if (!selected.value.length) return

  const hasSchedule = selected.value.filter(id => getSchedule(id))
  const missing = selected.value.length - hasSchedule.length

  let confirmMsg = `Send BEI schedule notification to ${hasSchedule.length} applicant(s)?`
  if (missing > 0) {
    confirmMsg += `\n\nNote: ${missing} selected applicant(s) have no BEI schedule and will be skipped.`
  }
  if (!confirm(confirmMsg)) return

  notifying.value = true
  notifyResult.value = null
  try {
    const { data } = await api.post(`/bei-schedules/notify/${props.vacancyId}`, {
      application_ids: selected.value,
    })
    notifyResult.value = data
    clearSelection()
  } catch (e) {
    alert(e.response?.data?.message ?? 'Failed to send notifications.')
  } finally {
    notifying.value = false
  }
}

onMounted(load)
</script>

<style scoped>
@reference "../../../css/app.css";
.field-label {
  @apply block text-xs font-semibold text-gray-600 mb-1.5;
}
.input {
  @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a5276] focus:border-transparent outline-none transition;
}
.btn-primary {
  @apply inline-flex items-center px-5 py-2.5 bg-[#1a5276] text-white text-sm font-semibold rounded-xl hover:bg-[#154360] disabled:opacity-50 transition shadow-sm;
}
.btn-secondary {
  @apply inline-flex items-center px-5 py-2.5 border border-gray-300 text-sm font-semibold rounded-xl text-gray-700 hover:bg-gray-50 transition;
}
</style>
