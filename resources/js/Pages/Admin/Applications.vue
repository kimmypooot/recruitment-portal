<template>
  <AdminLayout title="Applications">

    <div class="flex gap-5 items-start">

      <!-- ── Left panel: Vacancy list ──────────────────────────────────────── -->
      <div
        :class="selectedVacancy ? 'hidden lg:flex' : 'flex'"
        class="flex-col w-full lg:w-72 lg:flex-shrink-0 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <!-- Header -->
        <div class="px-4 py-3.5 border-b border-gray-100">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2.5">Positions</p>
          <div class="relative">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"
              fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input v-model="vacancySearch" type="text" placeholder="Search positions…"
              class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" />
          </div>
        </div>

        <!-- Loading skeleton -->
        <div v-if="vacanciesLoading" class="p-3 space-y-2">
          <div v-for="n in 5" :key="n" class="h-[72px] bg-gray-100 rounded-lg animate-pulse"></div>
        </div>

        <!-- Fetch error -->
        <div v-else-if="vacancyError" class="px-4 py-6 text-center">
          <p class="text-xs font-semibold text-red-600 mb-1">Failed to load positions</p>
          <p class="text-[11px] text-gray-400 mb-3">{{ vacancyError }}</p>
          <button @click="fetchVacancies"
            class="text-xs text-[#2a338f] font-medium hover:underline">Retry</button>
        </div>

        <!-- Vacancy cards -->
        <div v-else class="overflow-y-auto divide-y divide-gray-50">
          <button v-for="v in filteredVacancies" :key="v.id"
            @click="selectVacancy(v)"
            :class="[
              'w-full text-left px-4 py-3.5 transition-colors border-l-2',
              selectedVacancy?.id === v.id
                ? 'bg-[#2a338f]/5 border-[#2a338f]'
                : 'hover:bg-gray-50 border-transparent'
            ]">
            <div class="flex items-start justify-between gap-2 mb-1">
              <p class="text-sm font-semibold text-gray-900 leading-snug">{{ v.position_title }}</p>
              <span :class="vacancyStatusClass(v.status)"
                class="flex-shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded-full capitalize mt-0.5">
                {{ v.status }}
              </span>
            </div>
            <p class="text-xs text-gray-400 mb-2 truncate">
              SG-{{ v.salary_grade }}<span v-if="v.place_of_assignment"> · {{ v.place_of_assignment }}</span>
            </p>
            <div class="flex items-center gap-2">
              <span class="text-xs font-semibold text-gray-700">
                {{ v.applications_count }}
                <span class="font-normal text-gray-400">{{ v.applications_count === 1 ? 'applicant' : 'applicants' }}</span>
              </span>
              <span v-if="pendingCount(v) > 0"
                class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded-full">
                {{ pendingCount(v) }} pending
              </span>
            </div>
          </button>

          <div v-if="!filteredVacancies.length" class="px-4 py-14 text-center">
            <p class="text-sm text-gray-400">No positions found</p>
          </div>
        </div>
      </div>

      <!-- ── Right panel: Applicants ────────────────────────────────────────── -->
      <div
        :class="!selectedVacancy ? 'hidden lg:flex' : 'flex'"
        class="flex-col flex-1 min-w-0 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <!-- Empty state (desktop only, nothing selected) -->
        <div v-if="!selectedVacancy" class="flex-1 flex flex-col items-center justify-center py-28 text-center px-6">
          <div class="w-14 h-14 rounded-2xl bg-[#2a338f]/8 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-[#2a338f]/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/>
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-500">Select a position</p>
          <p class="text-xs text-gray-400 mt-1">Choose from the list to view its applicants</p>
        </div>

        <template v-else>

          <!-- Applicant panel header -->
          <div class="px-5 py-4 border-b border-gray-100 flex-shrink-0">

            <!-- Mobile back button -->
            <button class="lg:hidden flex items-center gap-1.5 text-xs text-[#2a338f] font-semibold mb-3"
              @click="selectedVacancy = null">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
              </svg>
              All Positions
            </button>

            <!-- Title row -->
            <div class="flex flex-col sm:flex-row sm:items-start gap-3 mb-3">
              <div class="flex-1 min-w-0">
                <p class="text-base font-bold text-gray-900">{{ selectedVacancy.position_title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  SG-{{ selectedVacancy.salary_grade }}
                  <span v-if="selectedVacancy.place_of_assignment"> · {{ selectedVacancy.place_of_assignment }}</span>
                  <span v-if="selectedVacancy.deadline_at"> · Deadline {{ formatDate(selectedVacancy.deadline_at) }}</span>
                </p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-xs bg-gray-100 text-gray-700 font-semibold px-2.5 py-1 rounded-full">
                  {{ meta.total ?? selectedVacancy.applications_count }} total
                </span>
                <span v-if="pendingCount(selectedVacancy) > 0"
                  class="text-xs bg-amber-50 text-amber-700 font-semibold px-2.5 py-1 rounded-full">
                  {{ pendingCount(selectedVacancy) }} pending
                </span>
              </div>
            </div>

            <!-- Toolbar -->
            <div class="flex gap-2">
              <div class="relative flex-1">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"
                  fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input v-model="filters.search" @input="onSearch" type="text"
                  placeholder="Search applicant…"
                  class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" />
              </div>
              <select v-model="filters.status" @change="resetAndFetch"
                class="px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
                <option value="">All Statuses</option>
                <optgroup label="Initial">
                  <option value="submitted">Submitted</option>
                  <option value="under_review">Under Review</option>
                </optgroup>
                <optgroup label="Screening">
                  <option value="screened">Screened</option>
                  <option value="qualified">Qualified</option>
                  <option value="disqualified">Disqualified</option>
                </optgroup>
                <optgroup label="Selection">
                  <option value="exam_scheduled">Exam Scheduled</option>
                  <option value="shortlisted">Shortlisted</option>
                  <option value="for_interview">For Interview</option>
                  <option value="recommended">Recommended</option>
                </optgroup>
                <optgroup label="Final">
                  <option value="appointed">Appointed</option>
                  <option value="completed">Completed</option>
                  <option value="withdrawn">Withdrawn</option>
                </optgroup>
              </select>
            </div>
          </div>

          <!-- Loading skeleton -->
          <div v-if="loading" class="p-5 space-y-3">
            <div v-for="n in 6" :key="n" class="flex items-center gap-4 h-12">
              <div class="w-8 h-8 rounded-full bg-gray-100 animate-pulse flex-shrink-0"></div>
              <div class="flex-1 space-y-1.5">
                <div class="h-3 bg-gray-100 rounded w-1/3 animate-pulse"></div>
                <div class="h-2.5 bg-gray-100 rounded w-1/4 animate-pulse"></div>
              </div>
              <div class="h-5 w-20 bg-gray-100 rounded-full animate-pulse"></div>
              <div class="h-7 w-20 bg-gray-100 rounded animate-pulse"></div>
            </div>
          </div>

          <!-- Applicants table -->
          <table v-else class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
                <th class="px-5 py-3">Applicant</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 hidden md:table-cell">Submitted</th>
                <th class="px-5 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="app in applications" :key="app.id"
                class="hover:bg-gray-50/80 transition-colors group">
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#2a338f]/10 flex items-center justify-center text-[#2a338f] text-xs font-bold flex-shrink-0">
                      {{ initials(app.applicant?.user?.name) }}
                    </div>
                    <div class="min-w-0">
                      <p class="font-medium text-gray-900 truncate">{{ app.applicant?.user?.name ?? '—' }}</p>
                      <p class="text-xs text-gray-400 truncate">{{ app.applicant?.user?.email ?? '' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-3.5">
                  <StatusBadge :status="app.status" />
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-400 hidden md:table-cell whitespace-nowrap">
                  {{ formatDate(app.submitted_at ?? app.created_at) }}
                </td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="openDetail(app)"
                      class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                      View
                    </button>
                    <button @click="openUpdate(app)"
                      class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-[#2a338f] bg-[#2a338f]/8 hover:bg-[#2a338f]/15 rounded-md transition-colors">
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                      Status
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!applications.length">
                <td colspan="4" class="px-5 py-14 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <svg class="w-9 h-9 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-400">No applicants found</p>
                    <button v-if="filters.search || filters.status" @click="clearFilters"
                      class="text-xs text-[#2a338f] hover:underline">Clear filters</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="!loading && meta.last_page > 1"
            class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
            <span class="text-xs text-gray-500">
              Showing <strong class="text-gray-700">{{ meta.from }}</strong>–<strong class="text-gray-700">{{ meta.to }}</strong>
              of <strong class="text-gray-700">{{ meta.total }}</strong>
            </span>
            <div class="flex items-center gap-1">
              <button :disabled="meta.current_page === 1" @click="goPage(meta.current_page - 1)"
                class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
              </button>
              <span class="px-3 py-1 text-xs font-medium text-gray-700">
                {{ meta.current_page }} / {{ meta.last_page }}
              </span>
              <button :disabled="meta.current_page === meta.last_page" @click="goPage(meta.current_page + 1)"
                class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
              </button>
            </div>
          </div>

        </template>
      </div>
    </div>

    <!-- ── Detail Drawer ──────────────────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="detailTarget" class="fixed inset-0 z-50 flex justify-end">
      <div class="absolute inset-0 bg-black/40" @click="detailTarget = null"></div>
      <div class="relative bg-white w-full max-w-md h-full flex flex-col shadow-2xl overflow-hidden">

        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#2a338f]/10 flex items-center justify-center text-[#2a338f] text-sm font-bold flex-shrink-0">
              {{ initials(detailTarget.applicant?.user?.name) }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ detailTarget.applicant?.user?.name ?? '—' }}</p>
              <p class="text-xs text-gray-400">{{ detailTarget.applicant?.user?.email ?? '' }}</p>
            </div>
          </div>
          <button @click="detailTarget = null" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Current Status</p>
            <StatusBadge :status="detailTarget.status" />
          </div>

          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Applied Position</p>
            <div class="bg-gray-50 rounded-lg p-3">
              <p class="text-sm font-semibold text-gray-900">{{ detailTarget.vacancy?.position_title ?? '—' }}</p>
              <p class="text-xs text-gray-500 mt-0.5">
                <span v-if="detailTarget.vacancy?.salary_grade">SG-{{ detailTarget.vacancy.salary_grade }}</span>
                <span v-if="detailTarget.vacancy?.place_of_assignment"> · {{ detailTarget.vacancy.place_of_assignment }}</span>
              </p>
            </div>
          </div>

          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Application Timeline</p>
            <div class="space-y-1">
              <div v-for="step in statusPipeline" :key="step.key"
                :class="[
                  'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                  detailTarget.status === step.key ? 'bg-[#2a338f]/5 border border-[#2a338f]/20' : ''
                ]">
                <div :class="[
                    'w-2 h-2 rounded-full flex-shrink-0',
                    detailTarget.status === step.key ? 'bg-[#2a338f]' :
                    isPast(step.key, detailTarget.status) ? 'bg-gray-300' : 'bg-gray-100'
                  ]"></div>
                <span :class="detailTarget.status === step.key ? 'font-medium text-[#2a338f]' : 'text-gray-400'">
                  {{ step.label }}
                </span>
                <span v-if="detailTarget.status === step.key" class="ml-auto text-xs text-[#2a338f] font-medium">Current</span>
              </div>
            </div>
          </div>

          <div v-if="detailTarget.remarks">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Remarks</p>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ detailTarget.remarks }}</p>
          </div>

          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Dates</p>
            <dl class="space-y-1.5">
              <div class="flex gap-3 text-xs">
                <dt class="w-28 text-gray-400 font-medium">Submitted</dt>
                <dd class="text-gray-700">{{ formatDate(detailTarget.submitted_at ?? detailTarget.created_at) }}</dd>
              </div>
              <div v-if="detailTarget.reviewed_at" class="flex gap-3 text-xs">
                <dt class="w-28 text-gray-400 font-medium">Last Updated</dt>
                <dd class="text-gray-700">{{ formatDate(detailTarget.reviewed_at) }}</dd>
              </div>
            </dl>
          </div>

          <!-- CS Forms -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">CS Forms</p>

            <div class="flex gap-2 mb-3">
              <select v-model="csForm.type"
                class="flex-1 px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
                <option value="33A">CS Form 33-A (Appointment)</option>
                <option value="33B">CS Form 33-B (Casual/Contractual)</option>
                <option value="form1">CS Form 1 (Personal Data Sheet)</option>
              </select>
              <button @click="generateForm" :disabled="csForm.generating"
                class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-[#2a338f] text-white rounded-lg hover:bg-[#1e2570] disabled:opacity-60 transition-colors">
                <svg v-if="csForm.generating" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Generate
              </button>
            </div>

            <p v-if="csForm.error" class="text-xs text-red-600 mb-2">{{ csForm.error }}</p>

            <div v-if="csForm.loading" class="space-y-2">
              <div v-for="n in 2" :key="n" class="h-10 bg-gray-100 rounded-lg animate-pulse"></div>
            </div>

            <div v-else-if="csForm.forms.length" class="space-y-2">
              <div v-for="f in csForm.forms" :key="f.id"
                class="flex items-center justify-between gap-2 p-2.5 bg-gray-50 rounded-lg border border-gray-100">
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-gray-800 truncate">{{ f.form_label }}</p>
                  <p class="text-[10px] text-gray-400 mt-0.5">
                    {{ formatDate(f.generated_at) }}
                    <span v-if="f.signed_at" class="ml-1 text-green-600 font-medium">· Signed</span>
                    <span v-if="f.submitted_to_csc_at" class="ml-1 text-blue-600 font-medium">· Submitted</span>
                  </p>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                  <button @click="downloadForm(f)"
                    class="p-1.5 text-gray-500 hover:text-[#2a338f] hover:bg-[#2a338f]/8 rounded-md transition-colors"
                    title="Download PDF">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                  </button>
                  <button v-if="!f.signed_at && csForm.pnpkiReady"
                    @click="signForm(f)" :disabled="csForm.actionId === f.id"
                    class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-md transition-colors disabled:opacity-50"
                    title="Sign via PNPKI">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                  </button>
                  <button v-if="!f.submitted_to_csc_at"
                    @click="markSubmitted(f)" :disabled="csForm.actionId === f.id"
                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors disabled:opacity-50"
                    title="Mark as submitted to CSC">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <p v-else class="text-xs text-gray-400 italic">No forms generated yet.</p>
          </div>

        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex-shrink-0">
          <button @click="openUpdate(detailTarget); detailTarget = null"
            class="w-full py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors">
            Update Status
          </button>
        </div>
      </div>
    </div>
    </Teleport>

    <!-- ── Update Status Modal ────────────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="updateTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="updateTarget = null"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col" style="max-height: 90vh;">

        <div class="px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <h3 class="text-base font-semibold text-gray-900">Update Application Status</h3>
          <p class="text-xs text-gray-400 mt-1">
            {{ updateTarget.applicant?.user?.name ?? 'Applicant' }}
            <span class="mx-1 text-gray-300">·</span>
            {{ updateTarget.vacancy?.position_title ?? 'Position' }}
          </p>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
          <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
            <span class="text-xs text-gray-500 font-medium">Current:</span>
            <StatusBadge :status="updateTarget.status" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Status <span class="text-red-500">*</span></label>
            <select v-model="statusForm.status"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
              <optgroup label="Initial">
                <option value="under_review">Under Review</option>
              </optgroup>
              <optgroup label="Screening">
                <option value="screened">Screened</option>
                <option value="qualified">Qualified</option>
                <option value="disqualified">Disqualified</option>
              </optgroup>
              <optgroup label="Selection">
                <option value="exam_scheduled">Exam Scheduled</option>
                <option value="shortlisted">Shortlisted</option>
                <option value="for_interview">For Interview</option>
                <option value="recommended">Recommended</option>
              </optgroup>
              <optgroup label="Final">
                <option value="appointed">Appointed</option>
                <option value="completed">Completed</option>
                <option value="withdrawn">Withdrawn</option>
              </optgroup>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Remarks <span class="text-gray-400 font-normal">(optional — visible to applicant)</span>
            </label>
            <textarea v-model="statusForm.remarks" rows="3"
              placeholder="Add notes or feedback for the applicant…"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none resize-none"></textarea>
          </div>
        </div>

        <div class="flex justify-between gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl flex-shrink-0">
          <button @click="updateTarget = null"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <button @click="doUpdateStatus" :disabled="saving"
            class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-60 transition-colors">
            <span v-if="saving" class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              Saving…
            </span>
            <span v-else>Save Status</span>
          </button>
        </div>

      </div>
    </div>
    </Teleport>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { debounce } from 'lodash-es'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

// ── State ─────────────────────────────────────────────────────────────────────
const vacanciesLoading = ref(true)
const vacancyError     = ref('')
const loading          = ref(false)
const saving           = ref(false)
const vacancies        = ref([])
const applications     = ref([])
const meta             = ref({})
const selectedVacancy  = ref(null)
const vacancySearch    = ref('')
const updateTarget     = ref(null)
const detailTarget     = ref(null)

const filters    = reactive({ search: '', status: '', page: 1 })
const statusForm = reactive({ status: '', remarks: '' })
const csForm     = reactive({ type: '33A', forms: [], loading: false, generating: false, actionId: null, pnpkiReady: false, error: '' })

// ── Computed ───────────────────────────────────────────────────────────────────
const filteredVacancies = computed(() => {
  const q = vacancySearch.value.trim().toLowerCase()
  if (!q) return vacancies.value
  return vacancies.value.filter(v =>
    v.position_title.toLowerCase().includes(q) ||
    (v.place_of_assignment ?? '').toLowerCase().includes(q)
  )
})

// ── Status pipeline ────────────────────────────────────────────────────────────
const statusPipeline = [
  { key: 'submitted',      label: 'Submitted' },
  { key: 'under_review',   label: 'Under Review' },
  { key: 'screened',       label: 'Screened' },
  { key: 'qualified',      label: 'Qualified' },
  { key: 'exam_scheduled', label: 'Exam Scheduled' },
  { key: 'shortlisted',    label: 'Shortlisted' },
  { key: 'for_interview',  label: 'For Interview' },
  { key: 'recommended',    label: 'Recommended' },
  { key: 'appointed',      label: 'Appointed' },
  { key: 'completed',      label: 'Completed' },
]
const pipelineOrder = statusPipeline.map(s => s.key)
function isPast(key, currentStatus) {
  return pipelineOrder.indexOf(key) < pipelineOrder.indexOf(currentStatus)
}

// ── Helpers ────────────────────────────────────────────────────────────────────
function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function pendingCount(v) {
  const b = v.status_breakdown ?? {}
  return (b.submitted ?? 0) + (b.under_review ?? 0)
}

function vacancyStatusClass(status) {
  return {
    published: 'bg-green-100 text-green-700',
    draft:     'bg-gray-100 text-gray-500',
    closed:    'bg-red-50 text-red-600',
    filled:    'bg-blue-50 text-blue-600',
    archived:  'bg-gray-100 text-gray-400',
  }[status] ?? 'bg-gray-100 text-gray-500'
}

function initials(name) {
  return name?.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase() ?? '?'
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

// ── Data fetching ──────────────────────────────────────────────────────────────
async function fetchVacancies() {
  vacanciesLoading.value = true
  vacancyError.value = ''
  try {
    const { data } = await axios.get('/api/vacancy-application-summary', { headers: authHeaders() })
    vacancies.value = data
  } catch (e) {
    const status = e.response?.status
    if (status === 401) vacancyError.value = 'Not authenticated. Please log in as Admin or HR Manager.'
    else if (status === 403) vacancyError.value = 'Access denied. You must be logged in as Admin or HR Manager to view this.'
    else vacancyError.value = e.response?.data?.message ?? 'An unexpected error occurred.'
  } finally {
    vacanciesLoading.value = false
  }
}

async function fetchApplications() {
  if (!selectedVacancy.value) return
  loading.value = true
  try {
    const { data } = await axios.get('/api/applications', {
      params: {
        vacancy_id: selectedVacancy.value.id,
        search:     filters.search || undefined,
        status:     filters.status || undefined,
        page:       filters.page,
      },
      headers: authHeaders(),
    })
    applications.value = data.data ?? []
    meta.value         = data.meta ?? {}
  } finally {
    loading.value = false
  }
}

function selectVacancy(v) {
  selectedVacancy.value = v
  filters.search = ''
  filters.status = ''
  filters.page   = 1
  fetchApplications()
}

const onSearch = debounce(() => resetAndFetch(), 350)
function resetAndFetch() { filters.page = 1; fetchApplications() }
function goPage(p)       { filters.page = p; fetchApplications() }
function clearFilters()  { filters.search = ''; filters.status = ''; resetAndFetch() }

// ── Detail drawer ──────────────────────────────────────────────────────────────
function openDetail(app) {
  detailTarget.value = app
  csForm.forms = []
  csForm.error = ''
  loadForms(app.id)
}

async function loadForms(applicationId) {
  csForm.loading = true
  try {
    const { data } = await axios.get(`/api/applications/${applicationId}/forms`, { headers: authHeaders() })
    csForm.forms      = data.forms ?? []
    csForm.pnpkiReady = data.pnpki_ready ?? false
  } catch {
    csForm.forms = []
  } finally {
    csForm.loading = false
  }
}

async function generateForm() {
  csForm.generating = true
  csForm.error = ''
  try {
    await axios.post(`/api/applications/${detailTarget.value.id}/forms`, { form_type: csForm.type }, { headers: authHeaders() })
    await loadForms(detailTarget.value.id)
  } catch (e) {
    csForm.error = e.response?.data?.message ?? 'Generation failed.'
  } finally {
    csForm.generating = false
  }
}

async function signForm(form) {
  csForm.actionId = form.id
  csForm.error = ''
  try {
    await axios.patch(`/api/forms/${form.id}/sign`, {}, { headers: authHeaders() })
    await loadForms(detailTarget.value.id)
  } catch (e) {
    csForm.error = e.response?.data?.message ?? 'Signing failed.'
  } finally {
    csForm.actionId = null
  }
}

async function downloadForm(form) {
  try {
    const { data } = await axios.get(`/api/forms/${form.id}/download`, {
      headers: { ...authHeaders() },
      responseType: 'blob',
    })
    const url = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
    const a = document.createElement('a')
    a.href = url
    a.download = `CSForm-${form.form_type}-App${detailTarget.value.id}.pdf`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    csForm.error = 'Download failed.'
  }
}

async function markSubmitted(form) {
  csForm.actionId = form.id
  csForm.error = ''
  try {
    await axios.patch(`/api/forms/${form.id}/mark-submitted`, {}, { headers: authHeaders() })
    await loadForms(detailTarget.value.id)
  } catch (e) {
    csForm.error = e.response?.data?.message ?? 'Action failed.'
  } finally {
    csForm.actionId = null
  }
}

// ── Update Status modal ────────────────────────────────────────────────────────
function openUpdate(app) {
  updateTarget.value = app
  statusForm.status  = app.status
  statusForm.remarks = app.remarks ?? ''
}

async function doUpdateStatus() {
  saving.value = true
  try {
    await axios.patch(`/api/applications/${updateTarget.value.id}/status`, statusForm, { headers: authHeaders() })
    updateTarget.value = null
    await fetchApplications()
    await fetchVacancies()
  } finally {
    saving.value = false
  }
}

onMounted(fetchVacancies)
</script>
