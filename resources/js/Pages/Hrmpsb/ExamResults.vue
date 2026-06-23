<template>
<<<<<<< HEAD
  <HrmbsboardLayout title="Examination Results" :vacancyId="props.vacancyId">
    <div class="space-y-6">

      <!-- ── Vacancy Banner ────────────────────────────────────────────── -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="2"
        stageLabel="Written Examination (TWE &amp; CBWE)"
        :loading="loading">
        <!-- Passing threshold note -->
        <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
          <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Passing threshold for all examinations is
          <strong class="text-amber-600 font-bold">{{ passingThreshold }}%</strong>.
          An applicant must pass all required exam types to be eligible for BEI.
        </div>
      </VacancyBanner>

      <!-- ── Summary Cards ─────────────────────────────────────────────── -->
      <div v-if="!loading && applications.length" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-gray-900">{{ applications.length }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Qualified Applicants</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Proceeded to Exam Stage</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-green-700">{{ passedCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Passed All Exams</p>
          <p class="text-[10px] text-gray-400 mt-0.5">≥ {{ passingThreshold }}% in all types</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-red-500">{{ failedCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Did Not Pass</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Below threshold / incomplete</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-4 shadow-sm text-center">
          <p class="text-2xl font-bold text-amber-500">{{ pendingCount }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">Pending Encoding</p>
          <p class="text-[10px] text-gray-400 mt-0.5">No scores recorded yet</p>
        </div>
      </div>

      <!-- ── Encode Panel (secretariat only) ──────────────────────────── -->
      <div v-if="canEncode" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Panel header with toggle -->
        <button
          @click="encodeOpen = !encodeOpen"
          class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-[#1a5276] flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            <div class="text-left">
              <p class="text-sm font-semibold text-gray-900">Encode Examination Score</p>
              <p class="text-xs text-gray-400 mt-0.5">HRMPSB Member — enter raw score and max items for TWE or CBWE</p>
            </div>
          </div>
          <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0"
            :class="encodeOpen ? 'rotate-180' : ''"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
          </svg>
        </button>

        <div v-show="encodeOpen" class="border-t border-gray-100 px-6 pb-6 pt-5">
          <form @submit.prevent="submitScore">
            <!-- Row 1 -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
              <div>
                <label class="field-label">Applicant (Blind Code)</label>
                <select v-model="form.application_id" class="input" required>
                  <option value="">— Select blind code —</option>
                  <option v-for="app in applications" :key="app.id" :value="app.id">
                    {{ appLabel(app) }}
                  </option>
                </select>
              </div>
              <div>
                <label class="field-label">Examination Type</label>
                <select v-model="form.exam_type" class="input" required>
                  <option value="TWE">TWE — Technical Written Examination</option>
                  <option value="CBWE">CBWE — Competency-Based Written Examination</option>
                </select>
              </div>
              <div>
                <label class="field-label">No. of Items / Max Score</label>
                <input v-model.number="form.max_score" type="number" step="1" min="1" max="500" class="input" required />
              </div>
            </div>

            <!-- Row 2 -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
              <div>
                <label class="field-label">Raw Score</label>
                <input
                  v-model.number="form.raw_score"
                  type="number" step="0.01" min="0"
                  :max="form.max_score"
                  class="input"
                  :class="scoreError ? 'border-red-400 bg-red-50 focus:ring-red-400' : ''"
                  required />
                <p v-if="scoreError" class="text-xs text-red-500 mt-1">{{ scoreError }}</p>
              </div>

              <!-- Live preview -->
              <div class="sm:col-span-2">
                <div v-if="form.raw_score !== '' && form.max_score"
                  class="flex items-center gap-3 px-4 py-3 rounded-xl border"
                  :class="Number(previewPct) >= passingThreshold
                    ? 'bg-green-50 border-green-200'
                    : 'bg-red-50 border-red-200'">
                  <div>
                    <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider">Score Preview</p>
                    <p class="text-2xl font-bold mt-0.5"
                      :class="Number(previewPct) >= passingThreshold ? 'text-green-700' : 'text-red-600'">
                      {{ previewPct }}%
                    </p>
                  </div>
                  <div class="ml-auto">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold"
                      :class="Number(previewPct) >= passingThreshold
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-700'">
                      {{ Number(previewPct) >= passingThreshold ? '✓ PASS' : '✗ FAIL' }}
                    </span>
                  </div>
                </div>
                <div v-else class="h-[66px] rounded-xl border border-dashed border-gray-200 flex items-center justify-center">
                  <p class="text-xs text-gray-400">Enter raw score to preview result</p>
                </div>
              </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <p v-if="encodeError" class="text-sm text-red-600">{{ encodeError }}</p>
              <div v-else></div>
              <button type="submit" :disabled="submitting || !!scoreError" class="btn-primary">
                <svg v-if="submitting" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ submitting ? 'Saving…' : 'Save Examination Score' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── Results Table ─────────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Examination Results — Ranked List</h2>
            <p class="text-xs text-gray-400 mt-0.5">Sorted by highest exam percentage (descending)</p>
          </div>
          <div class="flex items-center gap-2 text-xs text-gray-500">
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>Passed
            </span>
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>Failed
            </span>
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>Pending
            </span>
          </div>
        </div>

        <div v-if="loading" class="divide-y divide-gray-100">
          <div v-for="n in 5" :key="n" class="px-6 py-4 flex items-center gap-4">
            <div class="w-8 h-8 rounded-full bg-gray-100 animate-pulse flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-100 rounded w-1/4 animate-pulse"></div>
              <div class="h-3 bg-gray-50 rounded w-1/3 animate-pulse"></div>
            </div>
            <div class="w-20 h-8 bg-gray-100 rounded animate-pulse"></div>
            <div class="w-20 h-8 bg-gray-100 rounded animate-pulse"></div>
            <div class="w-16 h-6 bg-gray-100 rounded-full animate-pulse"></div>
          </div>
        </div>

        <div v-else-if="!applications.length"
          class="flex flex-col items-center justify-center py-16 text-center">
          <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-3">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-500">No applicants in the examination stage</p>
          <p class="text-xs text-gray-400 mt-1">QS results must be locked before applicants appear here.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/70">
                <th class="pl-6 pr-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider w-14">Rank</th>
                <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Blind Code / Applicant</th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                  <div>TWE</div>
                  <div class="font-normal normal-case text-gray-300">Technical Written Exam</div>
                </th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                  <div>CBWE</div>
                  <div class="font-normal normal-case text-gray-300">Competency-Based Written Exam</div>
                </th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Average %</th>
                <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Result</th>
                <th v-if="canEncode" class="pl-3 pr-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="(app, idx) in applications" :key="app.id"
                class="group transition-colors"
                :class="{
                  'bg-green-50/30 hover:bg-green-50/60': app.overall_passed && app.exam_results.length,
                  'bg-red-50/20 hover:bg-red-50/40':    !app.overall_passed && app.exam_results.length,
                  'hover:bg-gray-50':                     !app.exam_results.length,
                }">

                <!-- Rank -->
                <td class="pl-6 pr-3 py-4 text-center">
                  <div v-if="app.exam_results.length" class="flex items-center justify-center">
                    <span v-if="idx === 0"
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-amber-100 text-amber-700 ring-2 ring-amber-300">
                      1
                    </span>
                    <span v-else-if="idx === 1"
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 text-gray-600 ring-2 ring-gray-300">
                      2
                    </span>
                    <span v-else-if="idx === 2"
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-orange-100 text-orange-700 ring-2 ring-orange-300">
                      3
                    </span>
                    <span v-else
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold text-gray-400 bg-gray-50">
                      {{ idx + 1 }}
                    </span>
                  </div>
                  <span v-else class="text-gray-300 text-xs">—</span>
                </td>

                <!-- Blind Code / Name -->
                <td class="px-3 py-4">
                  <div>
                    <p v-if="app.unmasked" class="text-sm font-semibold text-gray-900">{{ app.name }}</p>
                    <p v-else class="text-sm font-bold font-mono text-[#1a5276]">{{ app.token ?? '—' }}</p>
                    <span class="inline-flex items-center gap-1 text-[10px] font-medium px-1.5 py-0.5 rounded mt-1"
                      :class="app.status === 'qualified'
                        ? 'bg-blue-50 text-blue-600'
                        : 'bg-gray-100 text-gray-500'">
                      {{ app.status.replace('_', ' ').replace(/^\w/, c => c.toUpperCase()) }}
                    </span>
                  </div>
                </td>

                <!-- TWE -->
                <td class="px-3 py-4 text-center">
                  <div v-if="getScore(app, 'TWE')" class="inline-flex flex-col items-center gap-1">
                    <span class="text-base font-bold"
                      :class="getScore(app, 'TWE').passed ? 'text-green-700' : 'text-red-600'">
                      {{ getScore(app, 'TWE').percentage }}%
                    </span>
                    <span class="text-[10px] text-gray-400">
                      {{ getScore(app, 'TWE').raw_score }} / {{ getScore(app, 'TWE').max_score }} items
                    </span>
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                      :class="getScore(app, 'TWE').passed
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-600'">
                      {{ getScore(app, 'TWE').passed ? 'PASS' : 'FAIL' }}
                    </span>
                  </div>
                  <div v-else class="inline-flex flex-col items-center gap-1 text-gray-300">
                    <span class="text-sm">—</span>
                    <span class="text-[10px]">Not yet encoded</span>
                  </div>
                </td>

                <!-- CBWE -->
                <td class="px-3 py-4 text-center">
                  <div v-if="getScore(app, 'CBWE')" class="inline-flex flex-col items-center gap-1">
                    <span class="text-base font-bold"
                      :class="getScore(app, 'CBWE').passed ? 'text-green-700' : 'text-red-600'">
                      {{ getScore(app, 'CBWE').percentage }}%
                    </span>
                    <span class="text-[10px] text-gray-400">
                      {{ getScore(app, 'CBWE').raw_score }} / {{ getScore(app, 'CBWE').max_score }} items
                    </span>
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                      :class="getScore(app, 'CBWE').passed
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-600'">
                      {{ getScore(app, 'CBWE').passed ? 'PASS' : 'FAIL' }}
                    </span>
                  </div>
                  <div v-else class="inline-flex flex-col items-center gap-1 text-gray-300">
                    <span class="text-sm">—</span>
                    <span class="text-[10px]">Not yet encoded</span>
                  </div>
                </td>

                <!-- Average % -->
                <td class="px-3 py-4 text-center">
                  <span v-if="avgPct(app) !== null" class="text-sm font-bold text-gray-700">
                    {{ avgPct(app) }}%
                  </span>
                  <span v-else class="text-gray-300 text-sm">—</span>
                </td>

                <!-- Overall Result -->
                <td class="px-3 py-4 text-center">
                  <span v-if="app.exam_results.length"
                    class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1 rounded-full"
                    :class="app.overall_passed
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-700'">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                      <path v-if="app.overall_passed" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ app.overall_passed ? 'PASSED' : 'FAILED' }}
                  </span>
                  <span v-else class="inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-100">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pending
                  </span>
                </td>

                <!-- Actions -->
                <td v-if="canEncode" class="pl-3 pr-6 py-4 text-right">
                  <button
                    v-if="app.exam_results.length"
                    @click="deleteScore(app)"
                    :disabled="deleting[app.id]"
                    class="text-xs text-gray-400 hover:text-red-600 hover:underline disabled:opacity-50 transition-colors">
                    {{ deleting[app.id] ? 'Clearing…' : 'Clear scores' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Table footer legend -->
        <div v-if="!loading && applications.length"
          class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-400 flex-wrap gap-2">
          <span>{{ applications.length }} applicant{{ applications.length !== 1 ? 's' : '' }} in this stage</span>
          <span>Blind Codes are used to preserve applicant anonymity during examination assessment.</span>
        </div>
      </div>

      <!-- ── Navigation ────────────────────────────────────────────────── -->
      <div class="flex items-center justify-between pt-2">
        <a :href="`/hrmpsb/qs-results/${props.vacancyId}`" class="btn-secondary">
          <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
          </svg>
          QS Results
        </a>
        <a :href="`/hrmpsb/bei-rating/${props.vacancyId}`" class="btn-primary">
          BEI Rating
          <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
          </svg>
        </a>
      </div>

=======
  <HrmbsboardLayout title="Exam Results">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Exam Results</h1>
          <p v-if="vacancy" class="text-sm text-gray-500 mt-1">{{ vacancy.position_title }}</p>
        </div>
        <span
          v-if="vacancy"
          class="text-xs font-medium px-2 py-1 rounded-full"
          :class="vacancy.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
        >{{ vacancy.status }}</span>
      </div>

      <!-- Encode Form (secretariat only) -->
      <div v-if="canEncode" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Encode Score</h2>
        <form @submit.prevent="submitScore" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Applicant Token</label>
            <select v-model="form.application_id" class="input" required>
              <option value="">Select applicant…</option>
              <option v-for="app in applications" :key="app.id" :value="app.id">
                {{ app.unmasked ? app.name : app.token }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Exam Type</label>
            <select v-model="form.exam_type" class="input" required>
              <option value="TWE">TWE</option>
              <option value="CBWE">CBWE</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Raw Score</label>
            <input v-model.number="form.raw_score" type="number" step="0.01" min="0" max="100" class="input" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Score</label>
            <input v-model.number="form.max_score" type="number" step="0.01" min="1" max="100" class="input" required />
          </div>
          <div class="sm:col-span-4 flex justify-end">
            <button type="submit" :disabled="submitting" class="btn-primary">
              {{ submitting ? 'Saving…' : 'Save Score' }}
            </button>
          </div>
        </form>
        <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
      </div>

      <!-- Results Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Applicant</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">TWE</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">CBWE</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="loading">
              <td colspan="4" class="px-4 py-8 text-center text-gray-400">Loading…</td>
            </tr>
            <tr v-else-if="applications.length === 0">
              <td colspan="4" class="px-4 py-8 text-center text-gray-400">No applications found.</td>
            </tr>
            <tr v-for="app in applications" :key="app.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <span v-if="app.unmasked" class="font-medium text-gray-900">{{ app.name }}</span>
                <span v-else class="font-mono text-indigo-700 font-semibold">{{ app.token }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">{{ app.status }}</span>
              </td>
              <td class="px-4 py-3">
                <template v-if="getScore(app, 'TWE')">
                  <span class="font-semibold">{{ getScore(app, 'TWE').raw_score }}</span>
                  <span class="text-gray-400"> / {{ getScore(app, 'TWE').max_score }}</span>
                  <span class="ml-1 text-xs text-gray-500">({{ getScore(app, 'TWE').percentage }}%)</span>
                </template>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-4 py-3">
                <template v-if="getScore(app, 'CBWE')">
                  <span class="font-semibold">{{ getScore(app, 'CBWE').raw_score }}</span>
                  <span class="text-gray-400"> / {{ getScore(app, 'CBWE').max_score }}</span>
                  <span class="ml-1 text-xs text-gray-500">({{ getScore(app, 'CBWE').percentage }}%)</span>
                </template>
                <span v-else class="text-gray-400">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Navigation -->
      <div class="flex justify-between pt-2">
        <a :href="`/hrmpsb/qs-results/${vacancyId}`" class="btn-secondary">← QS Results</a>
        <a :href="`/hrmpsb/bei-rating/${vacancyId}`" class="btn-primary">BEI Rating →</a>
      </div>
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
    </div>
  </HrmbsboardLayout>
</template>

<script setup>
<<<<<<< HEAD
import { ref, computed, reactive, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
=======
import { ref, computed, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
import api from '@/services/api'

const props = defineProps({ vacancyId: Number })

<<<<<<< HEAD
const loading          = ref(true)
const submitting       = ref(false)
const encodeOpen       = ref(true)
const encodeError      = ref(null)
const vacancy          = ref(null)
const applications     = ref([])
const canEncode        = ref(false)
const passingThreshold = ref(70)
const deleting         = reactive({})

const form = ref({
  application_id: '',
  exam_type:      'TWE',
  raw_score:      '',
  max_score:      100,
})

// ── Computed ──────────────────────────────────────────────────────────────

const scoreError = computed(() => {
  if (form.value.raw_score === '' || !form.value.max_score) return null
  if (Number(form.value.raw_score) > Number(form.value.max_score)) {
    return `Raw score cannot exceed max score (${form.value.max_score})`
  }
  return null
})

const previewPct = computed(() => {
  if (!form.value.max_score || form.value.raw_score === '') return '—'
  return ((form.value.raw_score / form.value.max_score) * 100).toFixed(2)
})

const passedCount  = computed(() => applications.value.filter(a => a.overall_passed && a.exam_results.length).length)
const failedCount  = computed(() => applications.value.filter(a => !a.overall_passed && a.exam_results.length).length)
const pendingCount = computed(() => applications.value.filter(a => !a.exam_results.length).length)

// ── Helpers ───────────────────────────────────────────────────────────────

function appLabel(app) {
  if (app.token) return app.token
  return 'Pending-' + app.id
}

=======
const loading = ref(true)
const submitting = ref(false)
const error = ref(null)
const vacancy = ref(null)
const applications = ref([])
const canEncode = ref(false)

const form = ref({
  application_id: '',
  exam_type: 'TWE',
  raw_score: '',
  max_score: 100,
})

>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
function getScore(app, type) {
  return app.exam_results?.find(r => r.exam_type === type) ?? null
}

<<<<<<< HEAD
function avgPct(app) {
  if (!app.exam_results?.length) return null
  const sum = app.exam_results.reduce((acc, r) => acc + Number(r.percentage), 0)
  return (sum / app.exam_results.length).toFixed(2)
}

// ── Data ──────────────────────────────────────────────────────────────────

=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/exam-results/${props.vacancyId}`)
<<<<<<< HEAD
    vacancy.value          = data.vacancy
    applications.value     = data.applications
    canEncode.value        = data.can_encode ?? false
    passingThreshold.value = data.passing_threshold ?? 70
  } catch (e) {
    encodeError.value = e.response?.data?.message ?? 'Failed to load exam results.'
=======
    vacancy.value = data.vacancy
    applications.value = data.applications
    canEncode.value = data.can_encode ?? false
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load exam results.'
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
  } finally {
    loading.value = false
  }
}

async function submitScore() {
<<<<<<< HEAD
  if (scoreError.value) return
  submitting.value = true
  encodeError.value = null
  try {
    await api.post('/exam-results', {
      application_id: form.value.application_id,
      exam_type:      form.value.exam_type,
      raw_score:      form.value.raw_score,
      max_score:      form.value.max_score,
      vacancy_id:     props.vacancyId,
=======
  submitting.value = true
  error.value = null
  try {
    await api.post('/exam-results', {
      ...form.value,
      vacancy_id: props.vacancyId,
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
    })
    form.value = { application_id: '', exam_type: 'TWE', raw_score: '', max_score: 100 }
    await load()
  } catch (e) {
<<<<<<< HEAD
    encodeError.value = e.response?.data?.message ?? 'Failed to save score.'
=======
    error.value = e.response?.data?.message ?? 'Failed to save score.'
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
  } finally {
    submitting.value = false
  }
}

<<<<<<< HEAD
async function deleteScore(app) {
  if (!confirm(`Clear all exam scores for this applicant? This cannot be undone.`)) return
  deleting[app.id] = true
  try {
    for (const r of app.exam_results) {
      await api.delete(`/exam-results/${r.id}`)
    }
    await load()
  } finally {
    deleting[app.id] = false
  }
}

=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
onMounted(load)
</script>

<style scoped>
@reference "../../../css/app.css";
<<<<<<< HEAD
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
=======
.input {
  @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent;
}
.btn-primary {
  @apply inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition;
}
.btn-secondary {
  @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 transition;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
}
</style>
