<template>
  <HrmbsboardLayout title="Pre-Assessment Matrix">
    <div class="space-y-6 pb-20 sm:pb-6">

    <!-- Vacancy Banner -->
    <VacancyBanner
      :vacancy="vacancy"
      :stage="1"
      stageLabel="Pre-Assessment Matrix"
      :loading="loading"
    />

    <!-- ── Expand/collapse toolbar ───────────────────────────────────────────── -->
    <div class="flex flex-wrap items-center gap-2 mb-4">
      <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mr-1">Columns:</span>

      <button v-for="grp in groups" :key="grp.key"
        @click="grp.open = !grp.open"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-semibold transition-colors"
        :class="grp.open
          ? 'bg-[#2a338f] text-white border-[#2a338f]'
          : 'bg-white text-gray-600 border-gray-300 hover:border-[#2a338f] hover:text-[#2a338f]'">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round"
            :d="grp.open ? 'M19 9l-7 7-7-7' : 'M9 5l7 7-7 7'"/>
        </svg>
        {{ grp.label }}
      </button>

      <div class="ml-auto flex items-center gap-3">
        <div class="flex items-center gap-3 text-xs text-gray-500">
          <span class="flex items-center gap-1.5">
            <span class="inline-block w-2 h-2 rounded-full bg-green-400"></span> On file
          </span>
          <span class="flex items-center gap-1.5">
            <span class="inline-block w-2 h-2 rounded-full bg-red-400"></span> No file
          </span>
          <span class="flex items-center gap-1.5">
            <span class="inline-block w-2 h-2 rounded-full bg-gray-300"></span> N/A
          </span>
        </div>
        <span v-if="!loading" class="text-xs text-gray-400">
          {{ rows.length }} applicant{{ rows.length !== 1 ? 's' : '' }}
        </span>
      </div>
    </div>

    <!-- ── Error ─────────────────────────────────────────────────────────────── -->
    <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700 mb-4">
      {{ error }}
    </div>

    <!-- ── Matrix table ──────────────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

      <!-- Loading skeleton -->
      <div v-if="loading" class="p-6 space-y-2">
        <div v-for="n in 5" :key="n" class="h-12 bg-gray-100 rounded-lg animate-pulse"></div>
      </div>

      <!-- No applicants -->
      <div v-else-if="!rows.length" class="flex flex-col items-center justify-center py-20 text-center">
        <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8z"/>
        </svg>
        <p class="text-sm font-semibold text-gray-500">No applicants found for this position</p>
      </div>

      <!-- Mobile scroll hint -->
      <div v-else class="flex items-center gap-1.5 px-4 py-1.5 bg-blue-50 border-b border-blue-100 text-xs text-blue-500 sm:hidden">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        Scroll right to see all columns
      </div>

      <!-- Matrix -->
      <div v-if="rows.length" class="overflow-auto" style="max-height: 72vh;">
        <table class="text-xs border-collapse" style="min-width: max-content;">

          <!-- ── Two-row thead ──────────────────────────────────────────────── -->
          <thead class="sticky top-0 z-30">

            <!-- Row 1: group headers -->
            <tr class="bg-gray-50 border-b border-gray-200">

              <!-- Fixed identifiers (rowspan 2) — z-40 to sit above both sticky row and sticky columns -->
              <th rowspan="2" class="sticky left-0 z-40 bg-gray-50 border-r border-gray-200 px-3 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                Applicant No.
              </th>
              <th rowspan="2" class="sticky left-[110px] z-40 bg-gray-50 border-r border-gray-200 px-3 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider" style="min-width:200px">
                Name of Applicant
              </th>

              <!-- Group: Documentary Requirements -->
              <th :colspan="docGroup.open ? 4 : 1"
                class="border border-gray-200 px-3 py-2 text-center font-semibold text-purple-700 bg-purple-50 whitespace-nowrap">
                <div class="flex items-center justify-center gap-1.5">
                  <button @click="docGroup.open = !docGroup.open" class="flex items-center gap-1 hover:text-purple-900">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" :d="docGroup.open ? 'M19 9l-7 7-7-7' : 'M9 5l7 7-7 7'"/>
                    </svg>
                    Completeness of Documentary Requirements
                  </button>
                </div>
              </th>

              <!-- Standalone columns (rowspan 2) -->
              <th rowspan="2" class="border border-gray-200 px-3 py-2 text-center font-semibold text-gray-600 bg-gray-50 whitespace-nowrap" style="min-width:110px">
                Requirements Submitted
              </th>
              <th rowspan="2" class="border border-gray-200 px-3 py-2 text-center font-semibold text-gray-600 bg-gray-50 whitespace-nowrap" style="min-width:160px">
                Secretariat Remarks
              </th>
              <th rowspan="2" class="border border-gray-200 px-3 py-2 text-center font-semibold text-indigo-700 bg-indigo-50" style="min-width:160px; max-width:200px">
                <p class="whitespace-nowrap">Education</p>
                <p v-if="vacancy?.education_req" class="text-[10px] font-normal text-indigo-500 mt-1 leading-relaxed whitespace-normal text-center">
                  {{ vacancy.education_req }}
                </p>
              </th>
              <th rowspan="2" class="border border-gray-200 px-3 py-2 text-center font-semibold text-indigo-700 bg-indigo-50" style="min-width:140px; max-width:180px">
                <p class="whitespace-nowrap">License / Registration</p>
                <p class="text-[10px] font-normal text-indigo-400 mt-1 italic text-center">if applicable</p>
              </th>
              <th rowspan="2" class="border border-gray-200 px-3 py-2 text-center font-semibold text-indigo-700 bg-indigo-50" style="min-width:160px; max-width:200px">
                <p class="whitespace-nowrap">Experience</p>
                <p v-if="vacancy?.experience_req" class="text-[10px] font-normal text-indigo-500 mt-1 leading-relaxed whitespace-normal text-center">
                  {{ vacancy.experience_req }}
                </p>
              </th>
              <th rowspan="2" class="border border-gray-200 px-3 py-2 text-center font-semibold text-indigo-700 bg-indigo-50" style="min-width:160px; max-width:200px">
                <p class="whitespace-nowrap">Training</p>
                <p v-if="vacancy?.training_req" class="text-[10px] font-normal text-indigo-500 mt-1 leading-relaxed whitespace-normal text-center">
                  {{ vacancy.training_req }}
                </p>
              </th>
              <th rowspan="2" class="border border-gray-200 px-3 py-2 text-center font-semibold text-indigo-700 bg-indigo-50" style="min-width:160px; max-width:200px">
                <p class="whitespace-nowrap">Eligibility</p>
                <p v-if="vacancy?.eligibility_req" class="text-[10px] font-normal text-indigo-500 mt-1 leading-relaxed whitespace-normal text-center">
                  {{ vacancy.eligibility_req }}
                </p>
              </th>

              <!-- Group: Pre-Assessment -->
              <th :colspan="preGroup.open ? 4 : 1"
                class="border border-gray-200 px-3 py-2 text-center font-semibold text-teal-700 bg-teal-50 whitespace-nowrap">
                <div class="flex items-center justify-center gap-1.5">
                  <button @click="preGroup.open = !preGroup.open" class="flex items-center gap-1 hover:text-teal-900">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" :d="preGroup.open ? 'M19 9l-7 7-7-7' : 'M9 5l7 7-7 7'"/>
                    </svg>
                    Pre-Assessment
                  </button>
                </div>
              </th>

              <!-- Group: HRMPSB Assessment -->
              <th :colspan="hrmpsbGroup.open ? hrmpsbMembers.length + 1 : 1"
                class="border border-gray-200 px-3 py-2 text-center font-semibold text-orange-700 bg-orange-50 whitespace-nowrap">
                <div class="flex items-center justify-center gap-1.5">
                  <button @click="hrmpsbGroup.open = !hrmpsbGroup.open" class="flex items-center gap-1 hover:text-orange-900">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" :d="hrmpsbGroup.open ? 'M19 9l-7 7-7-7' : 'M9 5l7 7-7 7'"/>
                    </svg>
                    HRMPSB Assessment
                  </button>
                </div>
              </th>

              <!-- Actions (rowspan 2) -->
              <th rowspan="2" class="border border-gray-200 px-3 py-2 text-center font-semibold text-gray-600 bg-gray-50 whitespace-nowrap">
                Action
              </th>
            </tr>

            <!-- Row 2: sub-column headers -->
            <tr class="bg-gray-50 border-b border-gray-300">

              <!-- Doc Requirements sub-columns -->
              <template v-if="docGroup.open">
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-purple-600 bg-purple-50/60 whitespace-nowrap">PDS</th>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-purple-600 bg-purple-50/60 whitespace-nowrap">IPCR</th>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-purple-600 bg-purple-50/60 whitespace-nowrap">TOR</th>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-purple-600 bg-purple-50/60 whitespace-nowrap">Certificate of Eligibility</th>
              </template>
              <template v-else>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-purple-600 bg-purple-50/60 text-xs">Docs</th>
              </template>

              <!-- Pre-Assessment sub-columns -->
              <template v-if="preGroup.open">
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-teal-600 bg-teal-50/60 whitespace-nowrap">Requirements Submitted</th>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-teal-600 bg-teal-50/60 whitespace-nowrap">HRD Assessment on QS</th>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-teal-600 bg-teal-50/60 whitespace-nowrap" style="min-width:160px">HRD Remarks</th>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-teal-600 bg-teal-50/60 whitespace-nowrap">PDS Link</th>
              </template>
              <template v-else>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-teal-600 bg-teal-50/60 text-xs">Pre-Asmt</th>
              </template>

              <!-- HRMPSB sub-columns -->
              <template v-if="hrmpsbGroup.open">
                <th v-for="member in hrmpsbMembers" :key="member.user_id"
                  class="border border-gray-200 px-3 py-2 text-center font-medium text-orange-600 bg-orange-50/60 whitespace-nowrap" style="min-width:130px">
                  <div class="leading-tight">
                    <p>{{ member.name }}</p>
                    <p class="text-[10px] font-normal text-orange-400 mt-0.5">{{ member.role }}</p>
                  </div>
                </th>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-orange-700 bg-orange-50 whitespace-nowrap font-semibold">Consensus</th>
              </template>
              <template v-else>
                <th class="border border-gray-200 px-3 py-2 text-center font-medium text-orange-600 bg-orange-50/60 text-xs">HRMPSB</th>
              </template>

            </tr>
          </thead>

          <!-- ── tbody ──────────────────────────────────────────────────────── -->
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(row, idx) in rows" :key="row.id"
              class="group transition-colors hover:bg-blue-50/40"
              :class="rowBg(row.id, idx)">

              <!-- Applicant No. -->
              <td class="sticky left-0 z-10 border-r border-gray-200 px-3 py-2.5 whitespace-nowrap font-mono text-gray-500 text-[11px] group-hover:bg-blue-75/60"
                :class="rowBg(row.id, idx)">
                {{ row.applicant_no }}
              </td>

              <!-- Name -->
              <td class="sticky left-[110px] z-10 border-r border-gray-200 px-3 py-2.5 font-medium text-gray-900 whitespace-nowrap group-hover:bg-blue-75/60"
                :class="rowBg(row.id, idx)">
                {{ row.applicant.full_name || '—' }}
              </td>

              <!-- ── Documentary Requirements ────────────────────────────── -->
              <template v-if="docGroup.open">
                <!-- PDS -->
                <td class="border border-gray-100 px-2 py-2 bg-purple-50/20">
                  <DocRemarkCell
                    :has-file="row.applicant.has_pds"
                    :status="getDraft(row.id).pds_submitted"
                    :remarks="getDraft(row.id).pds_remarks"
                    :readonly="!isSecretary"
                    @update:status="v => isSecretary && setDraft(row.id, 'pds_submitted', v)"
                    @update:remarks="v => isSecretary && setDraft(row.id, 'pds_remarks', v)" />
                </td>
                <!-- IPCR -->
                <td class="border border-gray-100 px-2 py-2 bg-purple-50/20">
                  <DocRemarkCell
                    :has-file="row.applicant.has_ipcr"
                    :status="getDraft(row.id).ipcr_submitted"
                    :remarks="getDraft(row.id).ipcr_remarks"
                    :optional="true"
                    :readonly="!isSecretary"
                    @update:status="v => isSecretary && setDraft(row.id, 'ipcr_submitted', v)"
                    @update:remarks="v => isSecretary && setDraft(row.id, 'ipcr_remarks', v)" />
                </td>
                <!-- TOR -->
                <td class="border border-gray-100 px-2 py-2 bg-purple-50/20">
                  <DocRemarkCell
                    :has-file="row.applicant.has_tor"
                    :status="getDraft(row.id).tor_submitted"
                    :remarks="getDraft(row.id).tor_remarks"
                    :readonly="!isSecretary"
                    @update:status="v => isSecretary && setDraft(row.id, 'tor_submitted', v)"
                    @update:remarks="v => isSecretary && setDraft(row.id, 'tor_remarks', v)" />
                </td>
                <!-- CoE -->
                <td class="border border-gray-100 px-2 py-2 bg-purple-50/20">
                  <DocRemarkCell
                    :has-file="row.applicant.has_coe"
                    :status="getDraft(row.id).coe_submitted"
                    :remarks="getDraft(row.id).coe_remarks"
                    :readonly="!isSecretary"
                    @update:status="v => isSecretary && setDraft(row.id, 'coe_submitted', v)"
                    @update:remarks="v => isSecretary && setDraft(row.id, 'coe_remarks', v)" />
                </td>
              </template>
              <template v-else>
                <td class="border border-gray-100 px-3 py-2.5 text-center bg-purple-50/10">
                  <span class="text-[10px] text-gray-400">{{ docSummary(row) }}</span>
                </td>
              </template>

              <!-- Requirements Submitted (Complete / Incomplete) -->
              <td class="border border-gray-100 px-2 py-2.5 text-center">
                <select :value="getDraft(row.id).requirements_complete"
                  :disabled="!isSecretary"
                  @change="e => setDraft(row.id, 'requirements_complete', e.target.value === '' ? null : e.target.value === 'true')"
                  class="text-xs border border-gray-200 rounded px-1.5 pr-7 py-1 bg-white focus:ring-1 focus:ring-[#2a338f] focus:outline-none w-full max-w-[110px] disabled:opacity-60 disabled:cursor-not-allowed"
                  :class="getDraft(row.id).requirements_complete === true ? 'text-green-700 border-green-300'
                         : getDraft(row.id).requirements_complete === false ? 'text-red-600 border-red-300'
                         : 'text-gray-500'">
                  <option value="">— Select —</option>
                  <option value="true">Complete</option>
                  <option value="false">Incomplete</option>
                </select>
                <!-- Remarks mandatory if incomplete -->
                <div v-if="getDraft(row.id).requirements_complete === false" class="mt-1">
                  <textarea :value="getDraft(row.id).requirements_remarks"
                    :readonly="!isSecretary"
                    @input="e => isSecretary && setDraft(row.id, 'requirements_remarks', e.target.value)"
                    rows="2" placeholder="Remarks (required)…"
                    class="text-xs border border-red-300 rounded px-1.5 py-1 w-full focus:ring-1 focus:ring-red-400 focus:outline-none resize-none read-only:opacity-60 read-only:cursor-default"
                    style="min-width:120px"></textarea>
                </div>
              </td>

              <!-- Secretariat Remarks -->
              <td class="border border-gray-100 px-2 py-2.5">
                <textarea :value="getDraft(row.id).secretariat_remarks"
                  :readonly="!isSecretary"
                  @input="e => isSecretary && setDraft(row.id, 'secretariat_remarks', e.target.value)"
                  rows="2" placeholder="Remarks…"
                  class="text-xs border border-gray-200 rounded px-1.5 py-1 w-full focus:ring-1 focus:ring-[#2a338f] focus:outline-none resize-none read-only:opacity-60 read-only:cursor-default"
                  style="min-width:150px"></textarea>
              </td>

              <!-- ── QS columns — applicant profile data (read-only) ───────── -->
              <!-- Education -->
              <td class="border border-gray-100 px-2 py-2 bg-indigo-50/20" style="min-width:200px; max-width:240px">
                <ul v-if="row.applicant.education?.length" class="space-y-2">
                  <li v-for="(e, i) in row.applicant.education" :key="i"
                    class="text-[11px] text-gray-700 leading-snug border-b border-indigo-100 pb-1.5 last:border-0 last:pb-0">
                    <p class="font-semibold text-indigo-800">{{ e.degree_course || e.level || '—' }}</p>
                    <p class="text-gray-500">{{ e.school_name }}</p>
                    <p v-if="e.year_graduated" class="text-gray-400">Graduated: {{ e.year_graduated }}</p>
                    <p v-else-if="e.period_from" class="text-gray-400">{{ e.period_from }}{{ e.period_to ? ' – ' + e.period_to : '' }}</p>
                    <p v-if="e.honors" class="text-indigo-500 italic">{{ e.honors }}</p>
                  </li>
                </ul>
                <span v-else class="text-[11px] text-gray-300 italic">No entries</span>
              </td>
              <!-- License / Registration -->
              <td class="border border-gray-100 px-2 py-2 bg-indigo-50/20">
                <textarea :value="getDraft(row.id).license_remarks"
                  :readonly="!isSecretary"
                  @input="e => isSecretary && setDraft(row.id, 'license_remarks', e.target.value)"
                  rows="3" placeholder="Enter license / registration details…"
                  class="text-xs border border-gray-200 rounded px-1.5 py-1 w-full focus:ring-1 focus:ring-indigo-400 focus:outline-none resize-none read-only:opacity-60 read-only:cursor-default"
                  style="min-width:160px"></textarea>
              </td>
              <!-- Experience -->
              <td class="border border-gray-100 px-2 py-2 bg-indigo-50/20" style="min-width:220px; max-width:260px">
                <ul v-if="row.applicant.experience?.length" class="space-y-2">
                  <li v-for="(w, i) in row.applicant.experience" :key="i"
                    class="text-[11px] text-gray-700 leading-snug border-b border-indigo-100 pb-1.5 last:border-0 last:pb-0">
                    <p class="font-semibold text-indigo-800">{{ w.position_title }}</p>
                    <p class="text-gray-500">{{ w.department_agency }}</p>
                    <p class="text-gray-400">
                      {{ w.date_from }} – {{ w.is_present ? 'Present' : (w.date_to || '—') }}
                    </p>
                    <span v-if="w.government_service"
                      class="inline-block text-[9px] font-semibold bg-blue-100 text-blue-600 px-1 py-0.5 rounded mt-0.5">
                      Gov't Service
                    </span>
                  </li>
                </ul>
                <span v-else class="text-[11px] text-gray-300 italic">No entries</span>
              </td>
              <!-- Training -->
              <td class="border border-gray-100 px-2 py-2 bg-indigo-50/20" style="min-width:200px; max-width:240px">
                <ul v-if="row.applicant.trainings?.length" class="space-y-2">
                  <li v-for="(t, i) in row.applicant.trainings" :key="i"
                    class="text-[11px] text-gray-700 leading-snug border-b border-indigo-100 pb-1.5 last:border-0 last:pb-0">
                    <p class="font-semibold text-indigo-800">{{ t.title }}</p>
                    <p v-if="t.conducted_by" class="text-gray-500">{{ t.conducted_by }}</p>
                    <p class="text-gray-400">
                      {{ t.date_from }}{{ t.date_to && t.date_to !== t.date_from ? ' – ' + t.date_to : '' }}
                      <span v-if="t.hours"> · {{ t.hours }}h</span>
                    </p>
                  </li>
                </ul>
                <span v-else class="text-[11px] text-gray-300 italic">No entries</span>
              </td>
              <!-- Eligibility -->
              <td class="border border-gray-100 px-2 py-2 bg-indigo-50/20" style="min-width:160px">
                <p v-if="row.applicant.eligibility" class="text-[11px] font-medium text-indigo-800 leading-snug">
                  {{ row.applicant.eligibility }}
                </p>
                <span v-else class="text-[11px] text-gray-300 italic">Not specified</span>
              </td>

              <!-- ── Pre-Assessment group ────────────────────────────────── -->
              <template v-if="preGroup.open">
                <!-- Requirements Submitted (mirror) -->
                <td class="border border-gray-100 px-3 py-2.5 text-center bg-teal-50/30">
                  <CompletenessBadge :value="getDraft(row.id).requirements_complete" />
                </td>
                <!-- HRD Assessment on QS -->
                <td class="border border-gray-100 px-2 py-2.5 text-center bg-teal-50/30">
                  <select :value="getDraft(row.id).hrd_assessment"
                    :disabled="!isSecretary"
                    @change="e => setDraft(row.id, 'hrd_assessment', e.target.value === '' ? null : e.target.value === 'true')"
                     class="text-xs border border-gray-200 rounded px-1.5 pr-7 py-1 bg-white focus:ring-1 focus:ring-[#2a338f] focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed"
                    :class="getDraft(row.id).hrd_assessment === true ? 'text-green-700 border-green-300'
                           : getDraft(row.id).hrd_assessment === false ? 'text-red-600 border-red-300'
                           : 'text-gray-500'">
                    <option value="">— Select —</option>
                    <option value="true">Qualified</option>
                    <option value="false">Not Qualified</option>
                  </select>
                </td>
                <!-- HRD Remarks -->
                <td class="border border-gray-100 px-2 py-2.5 bg-teal-50/30">
                  <textarea :value="getDraft(row.id).hrd_remarks"
                    :readonly="!isSecretary"
                    @input="e => isSecretary && setDraft(row.id, 'hrd_remarks', e.target.value)"
                    rows="2" placeholder="HRD remarks…"
                    class="text-xs border border-gray-200 rounded px-1.5 py-1 w-full focus:ring-1 focus:ring-[#2a338f] focus:outline-none resize-none read-only:opacity-60 read-only:cursor-default"
                    style="min-width:150px"></textarea>
                </td>
                <!-- PDS Link -->
                <td class="border border-gray-100 px-3 py-2.5 text-center bg-teal-50/30">
                  <button @click="viewDocument(row.applicant.pds_url)"
                    :disabled="!row.applicant.pds_url"
                    class="inline-flex items-center gap-1 font-medium whitespace-nowrap cursor-pointer transition-colors"
                    :class="row.applicant.pds_url ? 'text-[#2a338f] hover:underline' : 'text-gray-300 cursor-not-allowed'">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View PDS
                  </button>
                </td>
              </template>
              <template v-else>
                <!-- Collapsed pre-assessment summary -->
                <td class="border border-gray-100 px-3 py-2.5 text-center bg-teal-50/20">
                  <QualifiedBadge :value="getDraft(row.id).hrd_assessment" />
                </td>
              </template>

              <!-- ── HRMPSB Assessment group ─────────────────────────────── -->
              <template v-if="hrmpsbGroup.open">
                <td v-for="member in hrmpsbMembers" :key="member.user_id"
                  class="border border-gray-100 px-2 py-2.5 bg-orange-50/20"
                  :class="member.user_id === currentUserId && !isSecretary ? 'ring-1 ring-inset ring-orange-200' : 'text-center'">

                  <!-- Current member's own editable column -->
                  <template v-if="member.user_id === currentUserId && !isSecretary">
                    <div class="flex flex-col gap-1" style="min-width:180px">
                      <p class="text-[9px] font-semibold text-orange-500 uppercase tracking-wide mb-0.5">Your Assessment</p>
                      <!-- 4 QS fields as compact labelled rows -->
                      <div v-for="(field, label) in {
                        education_meets: 'Education',
                        experience_meets: 'Experience',
                        training_meets: 'Training',
                        eligibility_meets: 'Eligibility',
                      }" :key="field" class="flex items-center gap-1.5">
                        <span class="text-[9px] text-gray-500 w-[58px] shrink-0">{{ label }}</span>
                        <select :value="memberDrafts[row.id]?.[field]"
                          @change="e => setMemberDraft(row.id, field, e.target.value === '' ? null : e.target.value === 'true')"
                          class="flex-1 text-[10px] border rounded px-1 pr-6 py-0.5 focus:ring-1 focus:outline-none"
                          :class="memberDrafts[row.id]?.[field] === true  ? 'border-green-300 bg-green-50 text-green-700 focus:ring-green-400'
                                : memberDrafts[row.id]?.[field] === false ? 'border-red-300 bg-red-50 text-red-600 focus:ring-red-400'
                                : 'border-gray-200 bg-white text-gray-500 focus:ring-orange-400'">
                          <option value="">—</option>
                          <option value="true">Meets</option>
                          <option value="false">Does Not Meet</option>
                        </select>
                      </div>
                      <!-- Remarks -->
                      <textarea :value="memberDrafts[row.id]?.remarks"
                        @input="e => setMemberDraft(row.id, 'remarks', e.target.value)"
                        rows="2" placeholder="Remarks…"
                        class="text-[10px] border border-gray-200 rounded px-1.5 py-1 w-full focus:ring-1 focus:ring-orange-400 focus:outline-none resize-none mt-0.5">
                      </textarea>
                      <!-- Save -->
                      <button v-if="memberDrafts[row.id]?.dirty"
                        @click="saveMemberAssessment(row.id)"
                        :disabled="memberSaving[row.id]"
                        class="mt-0.5 inline-flex items-center justify-center gap-1 px-2 py-1 rounded bg-orange-500 hover:bg-orange-600 text-white text-[10px] font-semibold transition-colors disabled:opacity-60">
                        <svg v-if="memberSaving[row.id]" class="w-2.5 h-2.5 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        {{ memberSaving[row.id] ? 'Saving…' : 'Save Assessment' }}
                      </button>
                      <p v-if="memberSaveErrors[row.id]" class="text-xs text-red-500 leading-tight mt-0.5">
                        {{ memberSaveErrors[row.id] }}
                      </p>
                      <!-- Already saved indicator -->
                      <QualifiedBadge v-if="!memberDrafts[row.id]?.dirty" :value="getMemberEval(row, member.user_id)" />
                    </div>
                  </template>

                  <!-- Read-only view for other members or when secretariat is viewing -->
                  <template v-else>
                    <QualifiedBadge :value="getMemberEval(row, member.user_id)" />
                    <p v-if="getMemberRemarks(row, member.user_id)"
                      class="text-[10px] text-gray-400 mt-0.5 leading-tight max-w-[120px] mx-auto break-words">
                      {{ getMemberRemarks(row, member.user_id) }}
                    </p>
                  </template>

                </td>
                <!-- Consensus — secretariat only -->
                <td class="border border-gray-100 px-2 py-2.5 text-center bg-orange-50/40">
                  <select :value="getDraft(row.id).consensus"
                    :disabled="!isSecretary"
                    @change="e => setDraft(row.id, 'consensus', e.target.value === '' ? null : e.target.value === 'true')"
                     class="text-xs border border-gray-200 rounded px-1.5 pr-7 py-1 bg-white focus:ring-1 focus:ring-orange-400 focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed"
                    :class="getDraft(row.id).consensus === true ? 'text-green-700 border-green-300'
                           : getDraft(row.id).consensus === false ? 'text-red-600 border-red-300'
                           : 'text-gray-500'">
                    <option value="">— Select —</option>
                    <option value="true">Qualified</option>
                    <option value="false">Not Qualified</option>
                  </select>
                </td>
              </template>
              <template v-else>
                <!-- Collapsed HRMPSB summary -->
                <td class="border border-gray-100 px-3 py-2.5 text-center bg-orange-50/20">
                  <QualifiedBadge :value="getDraft(row.id).consensus" />
                </td>
              </template>

              <!-- Action (secretariat row save only; member assessment saves inline in their column) -->
              <td class="border border-gray-100 px-3 py-2.5 text-center">
                <template v-if="isSecretary">
                  <button v-if="drafts[row.id]?.dirty"
                    @click="saveRow(row.id)"
                    :disabled="saving[row.id]"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#2a338f] hover:bg-[#1e2570] text-white font-semibold text-[11px] transition-colors disabled:opacity-60">
                    <svg v-if="saving[row.id]" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    {{ saving[row.id] ? 'Saving…' : 'Save' }}
                  </button>
                  <span v-else class="text-[11px] text-gray-300">—</span>
                  <p v-if="saveErrors[row.id]" class="text-[10px] text-red-500 mt-1 max-w-[100px] leading-tight">
                    {{ saveErrors[row.id] }}
                  </p>
                </template>
                <span v-else class="text-[11px] text-gray-300">—</span>
              </td>

            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── Bottom note ────────────────────────────────────────────────────────── -->
    <p v-if="rows.length && isSecretary" class="mt-3 text-xs text-gray-400 text-right">
      Rows with unsaved changes are highlighted in yellow. Member assessments shown in their columns are read-only for the secretariat.
    </p>
    <p v-else-if="rows.length" class="mt-3 text-xs text-gray-400 text-right">
      Secretariat fields are read-only. Use your column in the HRMPSB Assessment section to submit your QS assessment per applicant.
    </p>

    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const props = defineProps({ vacancyId: Number })

// ── State ─────────────────────────────────────────────────────────────────────
const loading  = ref(true)
const error    = ref(null)
const vacancy  = ref(null)
const rows     = ref([])
const hrmpsbMembers = ref([])

// Per-row draft state { [appId]: { ...fields, dirty: bool } }
const drafts     = reactive({})
const saving     = reactive({})
const saveErrors = reactive({})

// Per-row member assessment draft (for the currently logged-in HRMPSB member)
const memberDrafts     = reactive({})
const memberSaving     = reactive({})
const memberSaveErrors = reactive({})

// ── Column group visibility ───────────────────────────────────────────────────
const docGroup    = reactive({ key: 'doc',    label: 'Documentary Requirements', open: true })
const preGroup    = reactive({ key: 'pre',    label: 'Pre-Assessment',           open: true })
const hrmpsbGroup = reactive({ key: 'hrmpsb', label: 'HRMPSB Assessment',        open: true })
const groups      = [docGroup, preGroup, hrmpsbGroup]

// ── Auth & role ───────────────────────────────────────────────────────────────
function authHeaders() {
  const t = localStorage.getItem('auth_token')
  return t ? { Authorization: `Bearer ${t}` } : {}
}

const authUser      = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
const currentUserId = authUser.id ?? null
const myRole        = ref(null)

const isSecretary = computed(() => {
  if (authUser.role === 'admin') return true
  return !!(myRole.value && ['secretariat', 'hr-chief'].includes(myRole.value.hrmpsb_role))
})

// ── Document viewer ──────────────────────────────────────────────────────────
async function viewDocument(url) {
  try {
    const resp = await axios.get(url, {
      headers: authHeaders(),
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

// ── Data fetching ─────────────────────────────────────────────────────────────
async function loadMatrix() {
  loading.value = true
  error.value   = null
  try {
    const { data } = await axios.get(`/api/pre-assessment/${props.vacancyId}`, {
      headers: authHeaders(),
    })
    vacancy.value       = data.vacancy
    hrmpsbMembers.value = data.hrmpsb_members
    rows.value          = data.applications

    // Initialise drafts from saved pre_assessment data
    data.applications.forEach(row => {
      const pa = row.pre_assessment ?? {}
      drafts[row.id] = {
        pds_submitted:          pa.pds_submitted          ?? null,
        ipcr_submitted:         pa.ipcr_submitted         ?? null,
        tor_submitted:          pa.tor_submitted          ?? null,
        coe_submitted:          pa.coe_submitted          ?? null,
        pds_remarks:            pa.pds_remarks            ?? '',
        ipcr_remarks:           pa.ipcr_remarks           ?? '',
        tor_remarks:            pa.tor_remarks            ?? '',
        coe_remarks:            pa.coe_remarks            ?? '',
        requirements_complete:  pa.requirements_complete  ?? null,
        requirements_remarks:   pa.requirements_remarks   ?? '',
        secretariat_remarks:    pa.secretariat_remarks    ?? '',
        license_remarks:        pa.license_remarks        ?? '',
        hrd_assessment:         pa.hrd_assessment         ?? null,
        hrd_remarks:            pa.hrd_remarks            ?? '',
        consensus:              pa.consensus              ?? null,
        dirty: false,
      }

      // Member draft — pre-populate from any existing QS evaluation for this user
      const myEval = row.qs_evaluations?.find(e => e.evaluator_id === currentUserId)
      memberDrafts[row.id] = {
        education_meets:   myEval?.education_meets   ?? null,
        experience_meets:  myEval?.experience_meets  ?? null,
        training_meets:    myEval?.training_meets    ?? null,
        eligibility_meets: myEval?.eligibility_meets ?? null,
        remarks:           myEval?.remarks           ?? '',
        dirty: false,
      }
    })
  } catch (e) {
    error.value = e?.response?.data?.message ?? 'Failed to load matrix.'
  } finally {
    loading.value = false
  }
}

// ── Draft helpers ─────────────────────────────────────────────────────────────
function getDraft(appId) {
  return drafts[appId] ?? {}
}

function setDraft(appId, field, value) {
  if (!drafts[appId]) return
  drafts[appId][field] = value
  drafts[appId].dirty  = true
  saveErrors[appId]    = null
}

// ── Saving ────────────────────────────────────────────────────────────────────
async function saveRow(appId) {
  const d = drafts[appId]
  if (!d) return

  // Local validation: remarks mandatory if incomplete
  if (d.requirements_complete === false && !d.requirements_remarks?.trim()) {
    saveErrors[appId] = 'Remarks required when Incomplete.'
    return
  }

  saving[appId]     = true
  saveErrors[appId] = null
  try {
    await axios.post(`/api/pre-assessment/${appId}`, {
      pds_submitted:         d.pds_submitted,
      ipcr_submitted:        d.ipcr_submitted,
      tor_submitted:         d.tor_submitted,
      coe_submitted:         d.coe_submitted,
      pds_remarks:           d.pds_remarks           || null,
      ipcr_remarks:          d.ipcr_remarks          || null,
      tor_remarks:           d.tor_remarks           || null,
      coe_remarks:           d.coe_remarks           || null,
      requirements_complete: d.requirements_complete,
      requirements_remarks:  d.requirements_remarks  || null,
      secretariat_remarks:   d.secretariat_remarks   || null,
      license_remarks:       d.license_remarks       || null,
      hrd_assessment:        d.hrd_assessment,
      hrd_remarks:           d.hrd_remarks           || null,
      consensus:             d.consensus,
    }, { headers: authHeaders() })
    drafts[appId].dirty = false
    toast.success('Assessment saved.')
  } catch (e) {
    const msg = e?.response?.data?.errors
      ? Object.values(e.response.data.errors).flat().join(' ')
      : e?.response?.data?.message ?? 'Save failed.'
    saveErrors[appId] = msg
    toast.error(msg)
  } finally {
    saving[appId] = false
  }
}

// ── Member assessment draft helpers ──────────────────────────────────────────
function setMemberDraft(appId, field, value) {
  if (!memberDrafts[appId]) return
  memberDrafts[appId][field] = value
  memberDrafts[appId].dirty  = true
  memberSaveErrors[appId]    = null
}

async function saveMemberAssessment(appId) {
  const d = memberDrafts[appId]
  if (!d) return

  if (d.education_meets === null || d.experience_meets === null
    || d.training_meets === null || d.eligibility_meets === null) {
    memberSaveErrors[appId] = 'All four QS fields must be set before saving.'
    return
  }

  memberSaving[appId]     = true
  memberSaveErrors[appId] = null
  try {
    await axios.post('/api/qs-evaluations', {
      application_id:    appId,
      education_meets:   d.education_meets,
      experience_meets:  d.experience_meets,
      training_meets:    d.training_meets,
      eligibility_meets: d.eligibility_meets,
      remarks:           d.remarks || null,
    }, { headers: authHeaders() })
    memberDrafts[appId].dirty = false
    toast.success('Assessment submitted.')
    // Refresh the row so the overall_qualified badge reflects the new result
    await loadMatrix()
  } catch (e) {
    const msg = e?.response?.data?.message
      ?? Object.values(e?.response?.data?.errors ?? {}).flat().join(' ')
      ?? 'Save failed.'
    memberSaveErrors[appId] = msg
    toast.error(msg)
  } finally {
    memberSaving[appId] = false
  }
}

// ── HRMPSB eval helpers ───────────────────────────────────────────────────────
function getMemberEval(row, userId) {
  const ev = row.qs_evaluations?.find(e => e.evaluator_id === userId)
  return ev ? ev.overall_qualified : null
}
function getMemberRemarks(row, userId) {
  return row.qs_evaluations?.find(e => e.evaluator_id === userId)?.remarks ?? null
}

// ── Row stripe ───────────────────────────────────────────────────────────────
function rowBg(appId, idx) {
  if (drafts[appId]?.dirty) return 'bg-yellow-50/60'
  return idx % 2 === 0 ? 'bg-white' : 'bg-gray-50/70'
}

// ── Doc summary (collapsed) ───────────────────────────────────────────────────
function docSummary(row) {
  const d = getDraft(row.id)
  const submitted = [d.pds_submitted, d.ipcr_submitted, d.tor_submitted, d.coe_submitted]
    .filter(v => v === true).length
  const assessed = [d.pds_submitted, d.ipcr_submitted, d.tor_submitted, d.coe_submitted]
    .filter(v => v !== null).length
  return assessed ? `${submitted}/${assessed} submitted` : '—'
}

// ── Navigation ────────────────────────────────────────────────────────────────
function goBack() {
  router.visit('/hrmpsb/dashboard')
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/hrmpsb/my-role', { headers: authHeaders() })
    myRole.value = data.composition
  } catch {
    // proceed without board role
  }
  await loadMatrix()
})
</script>

<!-- ── Inline sub-components ──────────────────────────────────────────────── -->
<script>
// DocRemarkCell: submission status + secretariat remarks textarea
const DocRemarkCell = {
  props: {
    hasFile:    { type: Boolean, default: false },
    status:     { default: null },   // null | true | false
    remarks:    { type: String, default: '' },
    optional:   { type: Boolean, default: false },
    readonly:   { type: Boolean, default: false },
  },
  emits: ['update:status', 'update:remarks'],
  template: `
    <div class="flex flex-col gap-1.5" style="min-width:150px">
      <!-- System file indicator -->
      <span class="inline-flex items-center gap-1 text-[9px] font-medium text-gray-400">
        <span class="w-1.5 h-1.5 rounded-full inline-block"
          :class="hasFile ? 'bg-green-400' : 'bg-gray-300'"></span>
        {{ hasFile ? 'Uploaded by applicant' : 'Not uploaded' }}
      </span>
      <!-- Secretariat submission status -->
      <select :value="status === null ? '' : status === true ? 'true' : status === false ? (optional ? 'na' : 'false') : ''"
        :disabled="readonly"
        @change="e => {
          if (readonly) return
          const v = e.target.value
          $emit('update:status', v === '' ? null : v === 'true' ? true : v === 'na' ? null : false)
          if (v === 'na') $emit('update:status', null)
        }"
        class="text-xs rounded border px-1.5 pr-7 py-1 focus:ring-1 focus:outline-none w-full disabled:opacity-60 disabled:cursor-not-allowed"
        :class="status === true  ? 'border-green-300 bg-green-50 text-green-700 focus:ring-green-400'
              : status === false ? 'border-red-300 bg-red-50 text-red-600 focus:ring-red-400'
              : 'border-gray-200 bg-white text-gray-500 focus:ring-purple-400'">
        <option value="">— Status —</option>
        <option value="true">Submitted</option>
        <option value="false">Not Submitted</option>
        <option v-if="optional" value="na">N/A</option>
      </select>
      <!-- Remarks -->
      <textarea :value="remarks"
        :readonly="readonly"
        @input="e => { if (!readonly) $emit('update:remarks', e.target.value) }"
        rows="2" placeholder="Remarks…"
        class="text-xs border border-gray-200 rounded px-1.5 py-1 w-full focus:ring-1 focus:ring-purple-400 focus:outline-none resize-none bg-white read-only:opacity-60 read-only:cursor-default">
      </textarea>
    </div>
  `,
}

// CompletenessBadge: display-only chip
const CompletenessBadge = {
  props: { value: { default: null } },
  template: `
    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold"
      :class="value === true  ? 'bg-green-100 text-green-700'
            : value === false ? 'bg-red-100 text-red-600'
            : 'bg-gray-100 text-gray-400'">
      {{ value === true ? 'Complete' : value === false ? 'Incomplete' : '—' }}
    </span>
  `,
}

// QualifiedBadge: display-only chip for qualified/not qualified
const QualifiedBadge = {
  props: { value: { default: null } },
  template: `
    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold"
      :class="value === true  ? 'bg-green-100 text-green-700'
            : value === false ? 'bg-red-100 text-red-600'
            : 'bg-gray-100 text-gray-400'">
      {{ value === true ? 'Qualified' : value === false ? 'Not Qualified' : '—' }}
    </span>
  `,
}

export default {
  components: { DocRemarkCell, CompletenessBadge, QualifiedBadge },
}
</script>
