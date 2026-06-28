<template>
    <AdminLayout title="Applications">

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- PAGE 1 — Vacant Positions                                               -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <template v-if="view === 'list'">

      <!-- Toolbar -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="vacancySearch" type="text" placeholder="Search vacant positions…"
            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none shadow-sm" />
        </div>
        <select v-model="vacancyStatusFilter"
          class="px-3 pr-8 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-[#2a338f] focus:outline-none shadow-sm">
          <option value="">All Statuses</option>
          <option value="published">Published</option>
          <option value="draft">Draft</option>
          <option value="closed">Closed</option>
          <option value="filled">Filled</option>
        </select>
      </div>

      <!-- Table card -->
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

        <!-- Loading skeleton -->
        <div v-if="vacanciesLoading" class="p-6 space-y-3">
          <div v-for="n in 6" :key="n" class="flex items-center gap-4 h-12">
            <div class="h-3 bg-gray-100 rounded w-6 animate-pulse flex-shrink-0"></div>
            <div class="flex-1 h-3 bg-gray-100 rounded animate-pulse"></div>
            <div class="h-3 bg-gray-100 rounded w-20 animate-pulse"></div>
            <div class="h-3 bg-gray-100 rounded w-16 animate-pulse hidden md:block"></div>
            <div class="h-3 bg-gray-100 rounded w-24 animate-pulse hidden lg:block"></div>
            <div class="h-3 bg-gray-100 rounded w-20 animate-pulse hidden lg:block"></div>
            <div class="h-3 bg-gray-100 rounded w-16 animate-pulse"></div>
            <div class="h-7 bg-gray-100 rounded-lg w-28 animate-pulse"></div>
          </div>
        </div>

        <!-- Error state -->
        <div v-else-if="vacancyError" class="py-20 text-center px-6">
          <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-700 mb-1">Failed to load positions</p>
          <p class="text-xs text-gray-400 mb-4">{{ vacancyError }}</p>
          <button @click="fetchVacancies"
            class="text-sm font-medium text-[#2a338f] hover:underline">Try again</button>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm" style="min-width: 900px;">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
                <th class="px-5 py-3.5 w-10">#</th>
                <th class="px-5 py-3.5 min-w-[200px]">Vacant Position</th>
                <th class="px-5 py-3.5 min-w-[140px]">Plantilla Item No.</th>
                <th class="px-5 py-3.5 min-w-[100px]">Salary Grade</th>
                <th class="px-5 py-3.5 min-w-[140px]">Monthly Salary</th>
                <th class="px-5 py-3.5 min-w-[130px]">Date of Publication</th>
                <th class="px-5 py-3.5 min-w-[130px]">Date of Deadline</th>
                <th class="px-5 py-3.5 min-w-[120px]">No. of Applicants</th>
                <th class="px-5 py-3.5 text-right min-w-[140px]">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(v, idx) in filteredVacancies" :key="v.id"
                class="hover:bg-gray-50/70 transition-colors">

                <!-- # -->
                <td class="px-5 py-4 text-xs text-gray-400 font-medium">{{ idx + 1 }}</td>

                <!-- Vacant Position -->
                <td class="px-5 py-4">
                  <p class="font-semibold text-gray-900 leading-snug">{{ v.position_title }}</p>
                  <p v-if="v.place_of_assignment" class="text-xs text-gray-400 mt-0.5">{{ v.place_of_assignment }}</p>
                  <span :class="vacancyStatusClass(v.status)"
                    class="inline-block mt-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-full capitalize">
                    {{ v.status }}
                  </span>
                </td>

                <!-- Plantilla Item No. -->
                <td class="px-5 py-4 text-sm text-gray-600">{{ v.plantilla_no ?? '—' }}</td>

                <!-- Salary Grade -->
                <td class="px-5 py-4 text-sm text-gray-700 font-medium whitespace-nowrap">
                  SG-{{ v.salary_grade ?? '—' }}
                </td>

                <!-- Monthly Salary -->
                <td class="px-5 py-4 text-sm text-gray-700 whitespace-nowrap">
                  {{ formatMoney(v.monthly_salary) }}
                </td>

                <!-- Date of Publication -->
                <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">
                  {{ formatDate(v.published_at) }}
                </td>

                <!-- Date of Deadline -->
                <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">
                  {{ formatDate(v.deadline_at) }}
                </td>

                <!-- No. of Applicants -->
                <td class="px-5 py-4">
                  <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-800">
                    {{ v.applications_count }}
                    <span class="text-xs font-normal text-gray-400">
                      {{ v.applications_count === 1 ? 'applicant' : 'applicants' }}
                    </span>
                  </span>
                  <div v-if="pendingCount(v) > 0" class="mt-0.5">
                    <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded-full">
                      {{ pendingCount(v) }} pending
                    </span>
                  </div>
                </td>

                <!-- Action -->
                <td class="px-5 py-4 text-right">
                  <button @click="viewApplicants(v)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[#2a338f] bg-[#2a338f]/8 hover:bg-[#2a338f]/15 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                    View Applicants
                  </button>
                </td>

              </tr>

              <tr v-if="!filteredVacancies.length">
                <td colspan="9" class="px-5 py-20 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <svg class="w-10 h-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-400">No vacant positions found</p>
                    <button v-if="vacancySearch || vacancyStatusFilter" @click="vacancySearch = ''; vacancyStatusFilter = ''"
                      class="text-xs text-[#2a338f] hover:underline">Clear filters</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </template>

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- PAGE 2 — Applicants for Selected Vacancy                                -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <template v-else>

      <!-- Breadcrumb + back -->
      <div class="flex items-center gap-2 flex-wrap mb-4">
        <button @click="backToList"
          class="flex items-center gap-1.5 text-sm text-[#2a338f] font-medium hover:underline">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          Back to Vacant Positions
        </button>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-sm text-gray-500 truncate max-w-xs">{{ selectedVacancy?.position_title }}</span>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-sm font-semibold text-gray-800">Applicants</span>
      </div>

      <!-- Position Information Header -->
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 mb-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Position Information</p>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
          <div>
            <p class="text-[11px] text-gray-400 font-medium mb-1">Vacant Position</p>
            <p class="text-sm font-semibold text-gray-900 leading-snug">{{ selectedVacancy?.position_title ?? '—' }}</p>
          </div>
          <div>
            <p class="text-[11px] text-gray-400 font-medium mb-1">Plantilla Item No.</p>
            <p class="text-sm text-gray-700">{{ selectedVacancy?.plantilla_no ?? '—' }}</p>
          </div>
          <div>
            <p class="text-[11px] text-gray-400 font-medium mb-1">Salary Grade</p>
            <p class="text-sm text-gray-700 font-semibold">SG-{{ selectedVacancy?.salary_grade ?? '—' }}</p>
          </div>
          <div>
            <p class="text-[11px] text-gray-400 font-medium mb-1">Place of Assignment</p>
            <p class="text-sm text-gray-700">{{ selectedVacancy?.place_of_assignment ?? '—' }}</p>
          </div>
          <div>
            <p class="text-[11px] text-gray-400 font-medium mb-1">Publication Period</p>
            <p class="text-sm text-gray-700 whitespace-nowrap">
              {{ formatDate(selectedVacancy?.published_at) }}
              <span class="text-gray-400 mx-1">–</span>
              {{ formatDate(selectedVacancy?.deadline_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Applicants table card -->
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

        <!-- Toolbar -->
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3">
          <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-sm font-semibold text-gray-900">Applicants</span>
            <span class="text-xs bg-gray-100 text-gray-600 font-semibold px-2 py-0.5 rounded-full">
              {{ meta.total ?? selectedVacancy?.applications_count ?? 0 }}
            </span>
          </div>
          <div class="flex gap-2 sm:ml-auto">
            <div class="relative flex-1 sm:flex-none">
              <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input v-model="filters.search" @input="onSearch" type="text"
                placeholder="Search applicant…"
                class="w-full sm:w-56 pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" />
            </div>
            <select v-model="filters.status" @change="resetAndFetch"
              class="px-2.5 pr-7 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
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

        <!-- Selection bar -->
        <div v-if="!loading && selectedIds.size > 0"
          class="px-5 py-2.5 bg-[#2a338f]/5 border-b border-[#2a338f]/10 flex items-center gap-3 flex-wrap flex-shrink-0">
          <span class="text-xs font-semibold text-[#2a338f]">
            {{ selectedIds.size }} applicant{{ selectedIds.size !== 1 ? 's' : '' }} selected
          </span>
          <div class="flex items-center gap-2 ml-auto">
            <button @click="openBatchUpdate"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-[#2a338f] hover:bg-[#1e2570] rounded-lg transition-colors">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Update Status
            </button>
            <button @click="clearSelection"
              class="text-xs text-gray-500 hover:text-gray-800 font-medium transition-colors">
              Clear
            </button>
          </div>
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" class="p-5 space-y-3">
          <div v-for="n in 6" :key="n" class="flex items-center gap-4 h-11">
            <div class="w-4 h-4 bg-gray-100 rounded animate-pulse flex-shrink-0 ml-5"></div>
            <div class="w-8 h-8 rounded-full bg-gray-100 animate-pulse flex-shrink-0"></div>
            <div class="flex-1 space-y-1.5">
              <div class="h-3 bg-gray-100 rounded w-1/3 animate-pulse"></div>
              <div class="h-2.5 bg-gray-100 rounded w-1/4 animate-pulse"></div>
            </div>
            <div class="h-2.5 bg-gray-100 rounded w-20 animate-pulse hidden sm:block"></div>
            <div class="h-2.5 bg-gray-100 rounded w-20 animate-pulse hidden md:block"></div>
            <div class="h-5 w-20 bg-gray-100 rounded-full animate-pulse"></div>
            <div class="h-7 w-36 bg-gray-100 rounded animate-pulse"></div>
          </div>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm" style="min-width: 1050px;">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
                <th class="pl-5 pr-2 py-3.5 w-10">
                  <input type="checkbox" ref="headerCheckbox"
                    :checked="allOnPageSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-[#2a338f] focus:ring-[#2a338f] cursor-pointer" />
                </th>
                <th class="px-4 py-3.5 min-w-[200px] cursor-pointer select-none hover:text-gray-700" @click="toggleSort('name')">
                  Applicant Name<span class="text-[10px]" v-html="sortIcon('name')"></span>
                </th>
                <th class="px-4 py-3.5 min-w-[90px] cursor-pointer select-none hover:text-gray-700" @click="toggleSort('gender')">
                  Gender<span class="text-[10px]" v-html="sortIcon('gender')"></span>
                </th>
                <th class="px-4 py-3.5 min-w-[110px] cursor-pointer select-none hover:text-gray-700" @click="toggleSort('civil_status')">
                  Civil Status<span class="text-[10px]" v-html="sortIcon('civil_status')"></span>
                </th>
                <th class="px-4 py-3.5 min-w-[110px] cursor-pointer select-none hover:text-gray-700" @click="toggleSort('birthday')">
                  Birthday<span class="text-[10px]" v-html="sortIcon('birthday')"></span>
                </th>
                <th class="px-4 py-3.5 min-w-[130px] cursor-pointer select-none hover:text-gray-700" @click="toggleSort('mobile')">
                  Mobile Number<span class="text-[10px]" v-html="sortIcon('mobile')"></span>
                </th>
                <th class="px-4 py-3.5 min-w-[180px] cursor-pointer select-none hover:text-gray-700" @click="toggleSort('email')">
                  Email Address<span class="text-[10px]" v-html="sortIcon('email')"></span>
                </th>
                <th class="px-4 py-3.5 min-w-[120px] cursor-pointer select-none hover:text-gray-700" @click="toggleSort('status')">
                  Status<span class="text-[10px]" v-html="sortIcon('status')"></span>
                </th>
                <th class="px-4 py-3.5 text-right min-w-[200px]">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(app, idx) in sortedApplications" :key="app.id"
                :class="['hover:bg-gray-50/80 transition-colors', selectedIds.has(app.id) ? 'bg-[#2a338f]/3' : '']">

                <!-- Checkbox -->
                <td class="pl-5 pr-2 py-4 w-10">
                  <input type="checkbox"
                    :checked="selectedIds.has(app.id)"
                    @click.stop="handleRowCheck($event, app, idx)"
                    class="rounded border-gray-300 text-[#2a338f] focus:ring-[#2a338f] cursor-pointer" />
                </td>

                <!-- Applicant Name (Last, First Middle) -->
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-[#2a338f]/10 flex items-center justify-center text-[#2a338f] text-xs font-bold flex-shrink-0">
                      {{ initials(app.applicant?.user) }}
                    </div>
                    <p class="font-semibold text-gray-900 leading-snug">{{ formatApplicantName(app) }}</p>
                  </div>
                </td>

                <!-- Gender -->
                <td class="px-4 py-4 text-xs text-gray-600 capitalize">
                  {{ app.applicant?.gender ?? '—' }}
                </td>

                <!-- Civil Status -->
                <td class="px-4 py-4 text-xs text-gray-600 capitalize">
                  {{ app.applicant?.civil_status ?? '—' }}
                </td>

                <!-- Birthday -->
                <td class="px-4 py-4 text-xs text-gray-500 whitespace-nowrap">
                  {{ formatDate(app.applicant?.birthday) }}
                </td>

                <!-- Mobile Number -->
                <td class="px-4 py-4 text-xs text-gray-600 whitespace-nowrap">
                  {{ app.applicant?.mobile_number ?? '—' }}
                </td>

                <!-- Email Address -->
                <td class="px-4 py-4 text-xs text-gray-600 truncate max-w-[180px]">
                  {{ app.applicant?.user?.email ?? '—' }}
                </td>

                <!-- Status -->
                <td class="px-4 py-4">
                  <StatusBadge :status="app.status" />
                </td>

                <!-- Actions -->
                <td class="px-4 py-4">
                  <div class="flex items-center justify-end gap-1">
                    <button @click="openAttachments(app)" title="View Attachments"
                      class="p-1.5 text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-md transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                      </svg>
                    </button>
                    <button @click="openCredentials(app)" title="View Applicant Credentials"
                      class="p-1.5 text-teal-600 bg-teal-50 hover:bg-teal-100 rounded-md transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                    </button>
                    <button @click="openDetail(app)" title="View Details"
                      class="p-1.5 text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </button>
                    <button @click="openUpdate(app)" title="Update Status"
                      class="p-1.5 text-[#2a338f] bg-[#2a338f]/8 hover:bg-[#2a338f]/15 rounded-md transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!sortedApplications.length">
                <td colspan="9" class="px-5 py-20 text-center">
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
        </div>

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

      </div>
    </template>

    <!-- ── Detail Drawer ──────────────────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="detailTarget" class="fixed inset-0 z-50 flex justify-end">
      <div class="absolute inset-0 bg-black/40" @click="detailTarget = null"></div>
      <div class="relative bg-white w-full max-w-md h-full flex flex-col shadow-2xl overflow-hidden">

        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#2a338f]/10 flex items-center justify-center text-[#2a338f] text-sm font-bold flex-shrink-0">
              {{ initials(detailTarget.applicant?.user) }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ formatApplicantName(detailTarget) }}</p>
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
                class="flex-1 px-2.5 pr-7 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
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

    <AttachmentsModal
      :app="attachmentsTarget"
      :profile="attachmentsTarget ? profileCache[attachmentsTarget.id] : null"
      :loading="profileLoading"
      :action-id="docAction"
      @close="attachmentsTarget = null"
      @view="viewDoc"
      @download="downloadDoc"
    />

    <CredentialsDrawer
      :app="credentialsTarget"
      :profile="credentialsData"
      :loading="profileLoading"
      @close="credentialsTarget = null"
    />

    <!-- ── Update Status Modal ────────────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="updateTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="updateTarget = null"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col" style="max-height: 90vh;">

        <div class="px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <h3 class="text-base font-semibold text-gray-900">Update Application Status</h3>
          <p class="text-xs text-gray-400 mt-1">
            {{ formatApplicantName(updateTarget) }}
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
              class="w-full px-3 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
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
                <option value="interviewed">Interviewed</option>
                <option value="recommended">Recommended</option>
              </optgroup>
              <optgroup label="Final">
                <option value="appointed">Appointed</option>
                <option value="completed">Completed</option>
                <option value="withdrawn">Withdrawn</option>
              </optgroup>
            </select>
          </div>

          <!-- Schedule form: shown when exam or interview status is selected -->
          <div v-if="statusForm.status === 'exam_scheduled' || statusForm.status === 'for_interview'"
            class="rounded-xl border p-4 space-y-3"
            :class="statusForm.status === 'exam_scheduled' ? 'bg-orange-50 border-orange-200' : 'bg-violet-50 border-violet-200'">
            <p class="text-xs font-semibold uppercase tracking-wide"
              :class="statusForm.status === 'exam_scheduled' ? 'text-orange-800' : 'text-violet-800'">
              {{ statusForm.status === 'exam_scheduled' ? 'Exam Schedule' : 'Interview Schedule' }}
              <span class="font-normal text-gray-400 ml-1 normal-case">(optional — create now or schedule later)</span>
            </p>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date &amp; Time</label>
              <input type="datetime-local" v-model="scheduleForm.scheduled_at"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
              <input type="text" v-model="scheduleForm.venue" placeholder="e.g. CSC Regional Office, Room 201"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Notes <span class="text-gray-400 font-normal">(optional)</span>
              </label>
              <textarea v-model="scheduleForm.notes" rows="2"
                placeholder="Additional instructions or reminders…"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none resize-none"></textarea>
            </div>
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

    <!-- ── Batch Update Status Modal ────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="batchUpdateOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="batchUpdateOpen = false"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col">

        <div class="px-6 py-5 border-b border-gray-100">
          <h3 class="text-base font-semibold text-gray-900">Batch Update Status</h3>
          <p class="text-xs text-gray-400 mt-1">
            Applying to
            <span class="font-semibold text-gray-700">{{ selectedIds.size }}</span>
            applicant{{ selectedIds.size !== 1 ? 's' : '' }}
          </p>
        </div>

        <div class="px-6 py-5 space-y-4">
          <!-- Progress bar (shown while saving) -->
          <div v-if="batchSaving" class="space-y-2">
            <div class="flex items-center justify-between text-xs text-gray-500">
              <span>Updating… {{ batchProgress }}/{{ batchTotal }}</span>
              <span>{{ Math.round((batchProgress / batchTotal) * 100) }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
              <div class="bg-[#2a338f] h-1.5 rounded-full transition-all duration-300"
                :style="{ width: `${(batchProgress / batchTotal) * 100}%` }"></div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Status <span class="text-red-500">*</span></label>
            <select v-model="batchForm.status" :disabled="batchSaving"
              class="w-full px-3 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white disabled:opacity-60">
              <option value="" disabled>Select a status…</option>
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
                <option value="interviewed">Interviewed</option>
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
              Remarks <span class="text-gray-400 font-normal">(optional — will be applied to all selected)</span>
            </label>
            <textarea v-model="batchForm.remarks" rows="3" :disabled="batchSaving"
              placeholder="Add notes or feedback…"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none resize-none disabled:opacity-60"></textarea>
          </div>

          <p v-if="batchError" class="text-xs text-red-600">{{ batchError }}</p>
        </div>

        <div class="flex justify-between gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl">
          <button @click="batchUpdateOpen = false" :disabled="batchSaving"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors disabled:opacity-50">
            Cancel
          </button>
          <button @click="doBatchUpdate" :disabled="batchSaving || !batchForm.status"
            class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-60 transition-colors">
            <span v-if="batchSaving" class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              Updating…
            </span>
            <span v-else>Apply to {{ selectedIds.size }} Applicant{{ selectedIds.size !== 1 ? 's' : '' }}</span>
          </button>
        </div>

      </div>
    </div>
    </Teleport>

    <!-- ── Toast (top-right) ─────────────────────────────────────────────────── -->
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, watchEffect, onMounted } from 'vue'
import { debounce } from 'lodash-es'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import AttachmentsModal from './Applications/AttachmentsModal.vue'
import CredentialsDrawer from './Applications/CredentialsDrawer.vue'
import { useConfirm } from '@/composables/useConfirm'
import { useToast } from '@/composables/useToast'

const { alert } = useConfirm()
const toast = useToast()

// ── State ─────────────────────────────────────────────────────────────────────
const view               = ref('list')
const vacanciesLoading   = ref(true)
const vacancyError       = ref('')
const loading            = ref(false)
const saving             = ref(false)
const vacancies          = ref([])
const applications       = ref([])
const meta               = ref({})
const selectedVacancy    = ref(null)
const vacancySearch      = ref('')
const vacancyStatusFilter = ref('')
const updateTarget       = ref(null)
const detailTarget       = ref(null)
const attachmentsTarget  = ref(null)
const credentialsTarget  = ref(null)
const profileCache       = ref({})
const profileLoading     = ref(false)
const docAction          = ref('')
const selectedIds        = ref(new Set())
const lastSelectedIndex  = ref(-1)
const headerCheckbox     = ref(null)
const batchUpdateOpen    = ref(false)
const batchSaving        = ref(false)
const batchProgress      = ref(0)
const batchTotal         = ref(0)
const batchError         = ref('')

const sortState    = reactive({ field: '', direction: 'asc' })
const filters      = reactive({ search: '', status: '', page: 1 })
const statusForm   = reactive({ status: '', remarks: '' })
const scheduleForm = reactive({ scheduled_at: '', venue: '', notes: '' })
const batchForm    = reactive({ status: '', remarks: '' })
const csForm       = reactive({ type: '33A', forms: [], loading: false, generating: false, actionId: null, pnpkiReady: false, error: '' })

// ── Computed ───────────────────────────────────────────────────────────────────
const allOnPageSelected = computed(() =>
  applications.value.length > 0 && applications.value.every(a => selectedIds.value.has(a.id))
)
const someOnPageSelected = computed(() =>
  applications.value.some(a => selectedIds.value.has(a.id)) && !allOnPageSelected.value
)

watchEffect(() => {
  if (headerCheckbox.value) {
    headerCheckbox.value.indeterminate = someOnPageSelected.value
  }
})

function toggleSort(field) {
  if (sortState.field === field) {
    sortState.direction = sortState.direction === 'asc' ? 'desc' : 'asc'
  } else {
    sortState.field = field
    sortState.direction = 'asc'
  }
}

function sortIcon(field) {
  if (sortState.field !== field) return ''
  return sortState.direction === 'asc' ? ' ▲' : ' ▼'
}

const sortedApplications = computed(() => {
  const list = [...applications.value]
  if (!sortState.field) return list
  list.sort((a, b) => {
    let va, vb
    switch (sortState.field) {
      case 'name':
        va = (a.applicant?.last_name ?? a.applicant?.user?.full_name ?? '').toLowerCase()
        vb = (b.applicant?.last_name ?? b.applicant?.user?.full_name ?? '').toLowerCase()
        break
      case 'gender':
        va = (a.applicant?.gender ?? '').toLowerCase()
        vb = (b.applicant?.gender ?? '').toLowerCase()
        break
      case 'civil_status':
        va = (a.applicant?.civil_status ?? '').toLowerCase()
        vb = (b.applicant?.civil_status ?? '').toLowerCase()
        break
      case 'birthday':
        va = a.applicant?.birthday ?? ''
        vb = b.applicant?.birthday ?? ''
        break
      case 'mobile':
        va = a.applicant?.mobile_number ?? ''
        vb = b.applicant?.mobile_number ?? ''
        break
      case 'email':
        va = (a.applicant?.user?.email ?? '').toLowerCase()
        vb = (b.applicant?.user?.email ?? '').toLowerCase()
        break
      case 'status':
        va = a.status ?? ''
        vb = b.status ?? ''
        break
      default:
        return 0
    }
    if (va < vb) return sortState.direction === 'asc' ? -1 : 1
    if (va > vb) return sortState.direction === 'asc' ? 1 : -1
    return 0
  })
  return list
})

const filteredVacancies = computed(() => {
  let list = vacancies.value
  const q = vacancySearch.value.trim().toLowerCase()
  if (q) {
    list = list.filter(v =>
      v.position_title.toLowerCase().includes(q) ||
      (v.place_of_assignment ?? '').toLowerCase().includes(q) ||
      (v.plantilla_no ?? '').toLowerCase().includes(q)
    )
  }
  if (vacancyStatusFilter.value) {
    list = list.filter(v => v.status === vacancyStatusFilter.value)
  }
  return list
})

// ── Attachments & credentials ─────────────────────────────────────────────────
const credentialsData = computed(() =>
  credentialsTarget.value ? profileCache.value[credentialsTarget.value.id] : null
)

async function loadApplicantProfile(appId) {
  if (profileCache.value[appId]) return
  profileLoading.value = true
  try {
    const { data } = await axios.get(`/api/applications/${appId}/applicant-profile`, { headers: authHeaders() })
    profileCache.value[appId] = data
  } catch {
    profileCache.value[appId] = null
  } finally {
    profileLoading.value = false
  }
}

async function openAttachments(app) {
  attachmentsTarget.value = app
  await loadApplicantProfile(app.id)
}

async function openCredentials(app) {
  credentialsTarget.value = app
  await loadApplicantProfile(app.id)
}

async function viewDoc(appId, type) {
  docAction.value = type + '-view'
  try {
    const { data } = await axios.get(`/api/applications/${appId}/applicant-documents/${type}`, {
      headers: { ...authHeaders() },
      responseType: 'blob',
    })
    const url = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
    window.open(url, '_blank')
    setTimeout(() => URL.revokeObjectURL(url), 60000)
  } catch {
    await alert('Unable to load document.')
  } finally {
    docAction.value = ''
  }
}

async function downloadDoc(appId, type, label) {
  docAction.value = type + '-dl'
  try {
    const { data } = await axios.get(`/api/applications/${appId}/applicant-documents/${type}?download=1`, {
      headers: { ...authHeaders() },
      responseType: 'blob',
    })
    const url = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
    const a = document.createElement('a')
    a.href = url
    a.download = `${label}.pdf`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    await alert('Unable to download document.')
  } finally {
    docAction.value = ''
  }
}

// ── Status pipeline ────────────────────────────────────────────────────────────
const statusPipeline = [
  { key: 'submitted',      label: 'Submitted' },
  { key: 'under_review',   label: 'Under Review' },
  { key: 'screened',       label: 'Screened' },
  { key: 'qualified',      label: 'Qualified' },
  { key: 'exam_scheduled', label: 'Exam Scheduled' },
  { key: 'shortlisted',    label: 'Shortlisted' },
  { key: 'for_interview',  label: 'For Interview' },
  { key: 'interviewed',    label: 'Interviewed' },
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

function initials(user) {
  if (typeof user === 'object' && user) {
    return ((user.first_name?.[0] ?? '') + (user.last_name?.[0] ?? '')).toUpperCase() || '?'
  }
  return String(user ?? '').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase() || '?'
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

function formatMoney(amount) {
  if (!amount && amount !== 0) return '—'
  return new Intl.NumberFormat('en-PH', {
    style: 'currency', currency: 'PHP', minimumFractionDigits: 2,
  }).format(amount)
}

function formatApplicantName(app) {
  const p = app?.applicant
  if (p?.last_name && p?.first_name) {
    const middle = p.middle_name ? ' ' + p.middle_name : ''
    return `${p.last_name}, ${p.first_name}${middle}`
  }
  return p?.user?.full_name ?? '—'
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

// ── Row selection ──────────────────────────────────────────────────────────────
function clearSelection() {
  selectedIds.value = new Set()
  lastSelectedIndex.value = -1
}

function toggleSelectAll() {
  const s = new Set(selectedIds.value)
  if (allOnPageSelected.value) {
    applications.value.forEach(a => s.delete(a.id))
  } else {
    applications.value.forEach(a => s.add(a.id))
  }
  selectedIds.value = s
}

function handleRowCheck(event, app, idx) {
  const s = new Set(selectedIds.value)
  if (event.shiftKey && lastSelectedIndex.value !== -1) {
    const from = Math.min(lastSelectedIndex.value, idx)
    const to   = Math.max(lastSelectedIndex.value, idx)
    for (let i = from; i <= to; i++) {
      const a = applications.value[i]
      if (a) s.add(a.id)
    }
    selectedIds.value = s
  } else {
    if (s.has(app.id)) s.delete(app.id)
    else s.add(app.id)
    selectedIds.value = s
  }
  lastSelectedIndex.value = idx
}

// ── Batch Update ───────────────────────────────────────────────────────────────
function openBatchUpdate() {
  batchForm.status  = ''
  batchForm.remarks = ''
  batchError.value  = ''
  batchProgress.value = 0
  batchUpdateOpen.value = true
}

async function doBatchUpdate() {
  if (!batchForm.status) return
  const ids = [...selectedIds.value]
  batchSaving.value   = true
  batchTotal.value    = ids.length
  batchProgress.value = 0
  batchError.value    = ''
  let failed = 0
  for (const id of ids) {
    try {
      await axios.patch(`/api/applications/${id}/status`, {
        status:  batchForm.status,
        remarks: batchForm.remarks || undefined,
      }, { headers: authHeaders() })
    } catch {
      failed++
    }
    batchProgress.value++
  }
  batchSaving.value = false
  if (failed > 0) {
    batchError.value = `${failed} application${failed !== 1 ? 's' : ''} could not be updated.`
    return
  }
  toast.success(`Status updated to "${batchForm.status.replace(/_/g, ' ')}" for ${selectedIds.size} applicant${selectedIds.size !== 1 ? 's' : ''}.`)
  batchUpdateOpen.value = false
  clearSelection()
  await fetchApplications()
  await fetchVacancies()
}

// ── Navigation ─────────────────────────────────────────────────────────────────
function viewApplicants(v) {
  selectedVacancy.value = v
  filters.search = ''
  filters.status = ''
  filters.page   = 1
  clearSelection()
  view.value = 'applicants'
  fetchApplications()
}

function backToList() {
  view.value = 'list'
  selectedVacancy.value = null
  applications.value = []
  meta.value = {}
  clearSelection()
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
  updateTarget.value        = app
  statusForm.status         = app.status
  statusForm.remarks        = app.remarks ?? ''
  scheduleForm.scheduled_at = ''
  scheduleForm.venue        = ''
  scheduleForm.notes        = ''
}

async function doUpdateStatus() {
  saving.value = true
  try {
    await axios.patch(`/api/applications/${updateTarget.value.id}/status`, statusForm, { headers: authHeaders() })

    if (scheduleForm.scheduled_at && scheduleForm.venue) {
      const endpoint = statusForm.status === 'exam_scheduled' ? '/api/examinations' : '/api/interviews'
      if (statusForm.status === 'exam_scheduled' || statusForm.status === 'for_interview') {
        await axios.post(endpoint, {
          application_id: updateTarget.value.id,
          scheduled_at:   scheduleForm.scheduled_at,
          venue:          scheduleForm.venue,
          notes:          scheduleForm.notes || undefined,
        }, { headers: authHeaders() }).catch(() => {})
      }
    }

    toast.success(`Status updated to "${statusForm.status.replace(/_/g, ' ')}" for ${formatApplicantName(updateTarget.value)}.`)
    updateTarget.value = null
    await fetchApplications()
    await fetchVacancies()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to update status.')
  } finally {
    saving.value = false
  }
}

onMounted(fetchVacancies)
</script>
