<template>
  <HrmbsboardLayout title="Exam Scheduler" :vacancyId="props.vacancyId">
    <div class="space-y-6">

      <!-- Vacancy Banner -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="stageNumber"
        :stageLabel="stageLabel"
        :loading="loading"
      >
        <div class="mt-4 flex items-center gap-1">
          <a :href="`/hrmpsb/exam-schedule/${props.vacancyId}?exam_type=TWE`"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors"
            :class="examType === 'TWE' ? 'bg-[#1a5276] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            TWE — Technical Written Exam
          </a>
          <a :href="`/hrmpsb/exam-schedule/${props.vacancyId}?exam_type=CBWE`"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors"
            :class="examType === 'CBWE' ? 'bg-[#1a5276] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            CBWE — Competency-Based Written Exam
          </a>
        </div>
        <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
          <svg class="w-4 h-4 text-[#1a5276] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Schedule the <strong class="text-gray-700">{{ examTypeLabel }}</strong> for all qualified applicants.
        </div>
      </VacancyBanner>

      <!-- Summary cards -->
      <div v-if="!loading && applications.length" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-gray-900">{{ applications.length }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Qualified Applicants</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Eligible for examination</p>
        </div>
        <div class="bg-white rounded-xl border border-blue-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-blue-700">{{ scheduledForType }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Scheduled for {{ examType }}</p>
        </div>
      </div>

      <!-- Batch Schedule Panel -->
      <div v-if="canSchedule" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <button
          @click="batchOpen = !batchOpen"
          class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <div class="text-left">
              <p class="text-sm font-semibold text-gray-900">Batch Schedule</p>
              <p class="text-xs text-gray-400 mt-0.5">Set one date, time, and venue for all applicants at once (per exam type)</p>
            </div>
          </div>
          <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="batchOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
          </svg>
        </button>

        <div v-show="batchOpen" class="border-t border-gray-100 px-6 pb-6 pt-5">
          <form @submit.prevent="submitBatch">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
              <div>
                <label class="field-label">Examination Type</label>
                <select v-model="batchForm.exam_type" class="input" required>
                  <option value="TWE">TWE — Technical Written Examination</option>
                  <option value="CBWE">CBWE — Competency-Based Written Examination</option>
                </select>
              </div>
              <div>
                <label class="field-label">Date &amp; Time</label>
                <input v-model="batchForm.scheduled_at" type="datetime-local" class="input" required />
              </div>
              <div>
                <label class="field-label">Venue / Location</label>
                <input v-model="batchForm.venue" type="text" class="input" placeholder="e.g. Conference Room A" required />
              </div>
              <div>
                <label class="field-label">Notes (optional)</label>
                <input v-model="batchForm.notes" type="text" class="input" placeholder="e.g. Bring valid ID" />
              </div>
            </div>
            <div class="flex items-center justify-between">
              <p v-if="batchError" class="text-sm text-red-600">{{ batchError }}</p>
              <div v-else class="text-xs text-gray-400">
                This will schedule all <strong>{{ applications.length }}</strong> applicant(s) for
                <strong>{{ examType }}</strong> at the same date and venue.
                Existing schedules for the selected type will be overwritten.
              </div>
              <button type="submit" :disabled="batchSubmitting" class="btn-primary ml-4 flex-shrink-0">
                <svg v-if="batchSubmitting" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ batchSubmitting ? 'Scheduling…' : `Schedule All for ${batchForm.exam_type}` }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Individual Schedule Form -->
      <div v-if="canSchedule" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
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
                {{ editingId ? 'Edit Schedule' : 'Add / Update Schedule' }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">Set or update the exam schedule for an individual applicant</p>
            </div>
          </div>
          <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="formOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
          </svg>
        </button>

        <div v-show="formOpen" class="border-t border-gray-100 px-6 pb-6 pt-5">
          <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
              <div>
                <label class="field-label">Applicant (Blind Code)</label>
                <select v-model="form.application_id" class="input" :disabled="!!editingId" required>
                  <option value="">— Select applicant —</option>
                  <option v-for="app in applications" :key="app.id" :value="app.id">
                    {{ app.token }}
                  </option>
                </select>
              </div>
              <div>
                <label class="field-label">Examination Type</label>
                <select v-model="form.exam_type" class="input" :disabled="!!editingId" required>
                  <option value="TWE">TWE — Technical Written Examination</option>
                  <option value="CBWE">CBWE — Competency-Based Written Examination</option>
                </select>
              </div>
              <div>
                <label class="field-label">Date &amp; Time</label>
                <input v-model="form.scheduled_at" type="datetime-local" class="input" required />
              </div>
              <div>
                <label class="field-label">Venue / Location</label>
                <input v-model="form.venue" type="text" class="input" placeholder="e.g. Conference Room A" required />
              </div>
              <div>
                <label class="field-label">Notes (optional)</label>
                <input v-model="form.notes" type="text" class="input" placeholder="Additional instructions" />
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
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Table header with selection action bar -->
        <div class="px-6 py-4 border-b border-gray-100">
          <!-- Selection bar — shown when rows are checked -->
          <div v-if="selected.length > 0"
            class="flex items-center gap-3 flex-wrap">
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
            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
              <button
                @click="sendNotification(examType)"
                :disabled="notifying"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                {{ notifying === examType ? 'Sending…' : `Notify — ${examType}` }}
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
              <h2 class="text-sm font-bold text-gray-900">{{ examType }} Schedule — All Applicants</h2>
              <p class="text-xs text-gray-400 mt-0.5">Check rows to select, then use Notify to send schedule notices</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500">
              <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>Scheduled
              </span>
              <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>Not set
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
              <strong>{{ notifyResult.notified }}</strong> notification{{ notifyResult.notified !== 1 ? 's' : '' }} queued for
              <strong>{{ notifyResult.exam_type }}</strong>.
              <span v-if="notifyResult.skipped > 0">
                {{ notifyResult.skipped }} skipped (no {{ notifyResult.exam_type }} schedule set).
              </span>
            </span>
          </div>
          <button @click="notifyResult = null" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div v-if="loading" class="divide-y divide-gray-100">
          <div v-for="n in 4" :key="n" class="px-6 py-4 flex items-center gap-4 animate-pulse">
            <div class="w-4 h-4 bg-gray-100 rounded"></div>
            <div class="w-20 h-4 bg-gray-100 rounded"></div>
            <div class="flex-1 h-4 bg-gray-100 rounded"></div>
            <div class="flex-1 h-4 bg-gray-100 rounded"></div>
          </div>
        </div>

        <div v-else-if="!applications.length" class="flex flex-col items-center justify-center py-16 text-center">
          <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-3">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-500">No applicants in this vacancy</p>
          <p class="text-xs text-gray-400 mt-1">QS results must be locked before scheduling exams.</p>
        </div>

        <div v-else class="overflow-x-auto">
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
                <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                  {{ examType }} Schedule
                  <div class="font-normal normal-case text-gray-300">{{ examType === 'TWE' ? 'Technical Written Exam' : 'Competency-Based Written Exam' }}</div>
                </th>
                <th v-if="canSchedule" class="pl-3 pr-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="(app, idx) in applications"
                :key="app.id"
                class="hover:bg-gray-50/50 transition-colors cursor-pointer"
                :class="isSelected(app.id) ? 'bg-[#1a5276]/5' : ''"
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
                  <span class="block text-[10px] font-medium px-1.5 py-0.5 rounded mt-1 inline-flex bg-gray-100 text-gray-500 capitalize">
                    {{ app.status.replace('_', ' ') }}
                  </span>
                </td>

                <!-- {{ examType }} Schedule -->
                <td class="px-3 py-4">
                  <div v-if="getSchedule(app.id, examType)" class="flex flex-col gap-0.5">
                    <span class="text-sm font-semibold text-gray-900">{{ formatDt(getSchedule(app.id, examType).scheduled_at) }}</span>
                    <span class="text-xs text-gray-500">{{ getSchedule(app.id, examType).venue }}</span>
                    <span v-if="getSchedule(app.id, examType).notes" class="text-[10px] text-gray-400 italic">{{ getSchedule(app.id, examType).notes }}</span>
                  </div>
                  <div v-else class="flex items-center gap-1.5 text-xs text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-gray-200 inline-block flex-shrink-0"></span>
                    Not scheduled
                  </div>
                </td>

                <!-- Actions -->
                <td v-if="canSchedule" class="pl-3 pr-6 py-4" @click.stop>
                  <div class="flex items-center justify-end gap-3">
                    <button
                      @click="editSchedule(app.id, examType)"
                      class="text-[11px] font-semibold text-[#1a5276] hover:underline transition-colors">
                      {{ getSchedule(app.id, examType) ? 'Edit' : 'Set' }}
                    </button>
                    <button
                      v-if="getSchedule(app.id, examType)"
                      @click="deleteSchedule(getSchedule(app.id, examType))"
                      :disabled="deleting[getSchedule(app.id, examType).id]"
                      class="text-[11px] font-semibold text-red-500 hover:underline disabled:opacity-50 transition-colors">
                      {{ deleting[getSchedule(app.id, examType).id] ? '…' : 'Clear' }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="!loading && applications.length"
          class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-400">
          <span>{{ applications.length }} applicant{{ applications.length !== 1 ? 's' : '' }}</span>
          <span>Blind Codes preserve applicant anonymity during the examination stage.</span>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex items-center justify-between pt-2">
        <a :href="`/hrmpsb/qs-results/${props.vacancyId}`" class="btn-secondary">
          <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
          </svg>
          QS Results
        </a>
        <a :href="`/hrmpsb/exam-results/${props.vacancyId}`" class="btn-primary">
          Exam Results
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
import { useConfirm } from '@/composables/useConfirm'
import api from '@/services/api'

const { confirm, alert } = useConfirm()

const props = defineProps({ vacancyId: Number, exam_type: { type: String, default: 'TWE' } })

const examType = computed(() => props.exam_type ?? 'TWE')
const examTypeLabel = computed(() => examType.value === 'TWE' ? 'Technical Written Examination (TWE)' : 'Competency-Based Written Examination (CBWE)')
const stageNumber = computed(() => examType.value === 'TWE' ? 3 : 4)
const stageLabel = computed(() => examType.value === 'TWE'
  ? 'Qualifying Exam (TWE)'
  : 'Qualifying Exam (CBWE)')

const loading       = ref(true)
const vacancy       = ref(null)
const applications  = ref([])
const schedules     = ref([])
const canSchedule   = ref(false)

const batchOpen       = ref(false)
const batchSubmitting = ref(false)
const batchError      = ref(null)
const batchForm       = ref({ exam_type: 'TWE', scheduled_at: '', venue: '', notes: '' })

const formOpen       = ref(true)
const formSubmitting = ref(false)
const formError      = ref(null)
const editingId      = ref(null)
const form           = ref({ application_id: '', exam_type: 'TWE', scheduled_at: '', venue: '', notes: '' })

const deleting     = reactive({})

// ── Selection state ───────────────────────────────────────────────────────
const selected        = ref([])
const lastCheckedIdx  = ref(null)
const notifying       = ref(null)
const notifyResult    = ref(null)

// ── Computed ──────────────────────────────────────────────────────────────

const scheduleMap = computed(() => {
  const map = {}
  for (const s of schedules.value) {
    if (!map[s.application_id]) map[s.application_id] = {}
    map[s.application_id][s.exam_type] = s
  }
  return map
})

const scheduledForType = computed(() =>
  applications.value.filter(a => scheduleMap.value[a.id]?.[examType.value]).length
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

function getSchedule(appId, type) {
  return scheduleMap.value[appId]?.[type] ?? null
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

function editSchedule(appId, type) {
  const existing = getSchedule(appId, type)
  editingId.value = existing?.id ?? null
  form.value = {
    application_id: appId,
    exam_type: type,
    scheduled_at: existing ? toDatetimeLocal(existing.scheduled_at) : '',
    venue: existing?.venue ?? '',
    notes: existing?.notes ?? '',
  }
  formOpen.value = true
  nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))
}

function cancelEdit() {
  editingId.value = null
  form.value = { application_id: '', exam_type: 'TWE', scheduled_at: '', venue: '', notes: '' }
}

// ── Data ──────────────────────────────────────────────────────────────────

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/exam-schedules/${props.vacancyId}`)
    vacancy.value      = data.vacancy
    applications.value = data.applications
    schedules.value    = data.schedules
    canSchedule.value  = data.can_schedule
    selected.value     = selected.value.filter(id => data.applications.some(a => a.id === id))
  } catch (e) {
    console.error('Failed to load exam schedules', e)
  } finally {
    loading.value = false
  }
}

async function submitBatch() {
  batchSubmitting.value = true
  batchError.value = null
  try {
    const payload = { ...batchForm.value, exam_type: examType.value }
    const { data } = await api.post(`/exam-schedules/batch/${props.vacancyId}`, payload)
    batchForm.value = { exam_type: examType.value, scheduled_at: '', venue: '', notes: '' }
    batchOpen.value = false
    await load()
    await alert(`Successfully scheduled ${data.scheduled} applicant(s) for ${data.exam_type}.`)
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
      await api.put(`/exam-schedules/${editingId.value}`, {
        scheduled_at: form.value.scheduled_at,
        venue: form.value.venue,
        notes: form.value.notes,
      })
    } else {
      await api.post('/exam-schedules', form.value)
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
  const ok = await confirm(`Remove the ${schedule.exam_type} schedule for this applicant?`)
  if (!ok) return
  deleting[schedule.id] = true
  try {
    await api.delete(`/exam-schedules/${schedule.id}`)
    await load()
  } catch (e) {
    await alert(e.response?.data?.message ?? 'Failed to delete schedule.')
  } finally {
    delete deleting[schedule.id]
  }
}

async function sendNotification(et) {
  if (!selected.value.length) return

  const hasSchedule = selected.value.filter(id => getSchedule(id, et))
  const missing = selected.value.length - hasSchedule.length

  let confirmMsg = `Send ${et} schedule notification to ${hasSchedule.length} applicant(s)?`
  if (missing > 0) {
    confirmMsg += `\n\nNote: ${missing} selected applicant(s) have no ${et} schedule and will be skipped.`
  }
  const confirmed = await confirm(confirmMsg)
  if (!confirmed) return

  notifying.value = et
  notifyResult.value = null
  try {
    const { data } = await api.post(`/exam-schedules/notify/${props.vacancyId}`, {
      application_ids: selected.value,
      exam_type: et,
    })
    notifyResult.value = data
    clearSelection()
  } catch (e) {
    await alert(e.response?.data?.message ?? 'Failed to send notifications.')
  } finally {
    notifying.value = null
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
