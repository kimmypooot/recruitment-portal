<template>
    <AdminLayout title="Applications">

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- PAGE 1 — Vacant Positions                                               -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <template v-if="view === 'list'">

      <!-- Status tabs -->
      <div class="flex flex-wrap gap-1.5 mb-4">
        <button v-for="tab in vacancyStatusTabs" :key="tab.value"
          @click="vacancyFilters.status = tab.value; resetAndFetchVacancies()"
          class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors"
          :class="vacancyFilters.status === tab.value
            ? 'bg-primary text-white shadow-sm'
            : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
          {{ tab.label }}
          <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold"
            :class="vacancyFilters.status === tab.value ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'">
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Toolbar -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
        <div class="relative flex-1">
          <Icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input v-model="vacancyFilters.search" @input="onVacancySearch" type="text" placeholder="Search vacant positions…"
            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none shadow-sm" />
        </div>
        <select v-model="vacancyFilters.sort" @change="resetAndFetchVacancies"
          class="px-3 pr-8 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary focus:outline-none shadow-sm">
          <option value="">Default Order</option>
          <option value="newest">Newest Published</option>
          <option value="deadline_desc">Deadline (Latest First)</option>
          <option value="sg_desc">Salary Grade (High → Low)</option>
          <option value="sg_asc">Salary Grade (Low → High)</option>
        </select>
      </div>

      <!-- Table card -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <SkeletonLoader v-if="vacanciesLoading" variant="table-row" :count="6" wrapper-class="p-6 space-y-3" />

        <!-- Error state -->
        <div v-else-if="vacancyError" role="alert" class="py-20 text-center px-6">
          <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-3">
            <Icon name="alert" size="6" class="text-red-400" />
          </div>
          <p class="text-sm font-semibold text-gray-700 mb-1">Failed to load positions</p>
          <p class="text-xs text-gray-400 mb-4">{{ vacancyError }}</p>
          <button @click="fetchVacancies"
            class="text-sm font-medium text-primary hover:underline">Try again</button>
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
              <tr v-for="(v, idx) in vacancies" :key="v.id"
                class="hover:bg-gray-50/70 transition-colors">

                <!-- # -->
                <td class="px-5 py-4 text-xs text-gray-400 font-medium">{{ (vacancyMeta.from ?? 1) + idx }}</td>

                <!-- Vacant Position -->
                <td class="px-5 py-4">
                  <p class="font-semibold text-gray-900 leading-snug">{{ v.position_title }}</p>
                  <p v-if="v.place_of_assignment" class="text-xs text-gray-400 mt-0.5">{{ v.place_of_assignment }}</p>
                  <span :class="statusBadgeClass(v.status)"
                    class="inline-block mt-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-full">
                    {{ statusLabel(v.status) }}
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
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary bg-primary/8 hover:bg-primary/15 rounded-lg transition-colors">
                    <Icon name="user" size="3.5" />
                    View Applicants
                  </button>
                </td>

              </tr>

              <tr v-if="!vacancies.length">
                <td colspan="9" class="px-5 py-20 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <Icon name="briefcase" size="10" class="text-gray-200" />
                    <p class="text-sm font-medium text-gray-400">No vacant positions found</p>
                    <button v-if="vacancyFilters.search || vacancyFilters.status || vacancyFilters.sort" @click="clearVacancyFilters"
                      class="text-xs text-primary hover:underline">Clear filters</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="!vacanciesLoading && vacancyMeta.last_page > 1"
          class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-xs text-gray-500">
            Showing <strong class="text-gray-700">{{ vacancyMeta.from }}</strong>–<strong class="text-gray-700">{{ vacancyMeta.to }}</strong>
            of <strong class="text-gray-700">{{ vacancyMeta.total }}</strong>
          </span>
          <div class="flex items-center gap-1">
            <button :disabled="vacancyMeta.current_page === 1" @click="goVacancyPage(vacancyMeta.current_page - 1)"
              class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
              <Icon name="chevronLeft" size="4" />
            </button>
            <span class="px-3 py-1 text-xs font-medium text-gray-700">
              {{ vacancyMeta.current_page }} / {{ vacancyMeta.last_page }}
            </span>
            <button :disabled="vacancyMeta.current_page === vacancyMeta.last_page" @click="goVacancyPage(vacancyMeta.current_page + 1)"
              class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
              <Icon name="chevronRight" size="4" />
            </button>
          </div>
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
          class="flex items-center gap-1.5 text-sm text-primary font-medium hover:underline">
          <Icon name="chevronLeft" size="4" />
          Back to Vacant Positions
        </button>
        <Icon name="chevronRight" size="3.5" class="text-gray-300" />
        <span class="text-sm text-gray-500 truncate max-w-xs">{{ selectedVacancy?.position_title }}</span>
        <Icon name="chevronRight" size="3.5" class="text-gray-300" />
        <span class="text-sm font-semibold text-gray-800">Applicants</span>
      </div>

      <!-- Position Information Header -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-5 mb-5">
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
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

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
              <Icon name="search" size="3.5" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400" />
              <input v-model="filters.search" @input="onSearch" type="text"
                placeholder="Search applicant…"
                class="w-full sm:w-56 pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
            </div>
            <select v-model="filters.status" @change="resetAndFetch"
              class="px-2.5 pr-7 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
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
                <option value="interviewed">Interviewed</option>
                <option value="recommended">Recommended</option>
              </optgroup>
              <optgroup label="Final">
                <option value="appointed">Appointed</option>
                <option value="completed">Completed</option>
                <option value="withdrawn">Withdrawn</option>
              </optgroup>
            </select>
            <button @click="exportApplicants" :disabled="exporting"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors disabled:opacity-50 whitespace-nowrap">
              <svg v-if="exporting" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              <Icon v-else name="download" size="3.5" />
              Export CSV
            </button>
          </div>
        </div>

        <!-- Selection bar -->
        <div v-if="!loading && selectedIds.size > 0"
          class="px-5 py-2.5 bg-primary/5 border-b border-primary/10 flex items-center gap-3 flex-wrap flex-shrink-0">
          <span class="text-xs font-semibold text-primary">
            {{ selectedIds.size }} applicant{{ selectedIds.size !== 1 ? 's' : '' }} selected
          </span>
          <div class="flex items-center gap-2 ml-auto">
            <button @click="batchUpdateOpen = true"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-primary hover:bg-primary-dark rounded-lg transition-colors">
              <Icon name="document" size="3" />
              Update Status
            </button>
            <button @click="clearSelection"
              class="text-xs text-gray-500 hover:text-gray-800 font-medium transition-colors">
              Clear
            </button>
          </div>
        </div>

        <SkeletonLoader v-if="loading" variant="application-row" :count="6" />

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm" style="min-width: 1050px;">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
                <th class="pl-5 pr-2 py-3.5 w-10">
                  <input type="checkbox" ref="headerCheckbox"
                    :checked="allOnPageSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" />
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
              <tr v-for="(app, idx) in applications" :key="app.id"
                :class="['hover:bg-gray-50/80 transition-colors', selectedIds.has(app.id) ? 'bg-primary/3' : '']">

                <!-- Checkbox -->
                <td class="pl-5 pr-2 py-4 w-10">
                  <input type="checkbox"
                    :checked="selectedIds.has(app.id)"
                    @click.stop="handleRowCheck($event, app, idx)"
                    class="rounded border-gray-300 text-primary focus:ring-primary cursor-pointer" />
                </td>

                <!-- Applicant Name (Last, First Middle) -->
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold flex-shrink-0">
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
                <td class="px-4 py-4 text-xs text-gray-600 truncate max-w-[180px]" :title="app.applicant?.user?.email ?? ''">
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
                      <Icon name="paperclip" size="4" />
                    </button>
                    <button @click="openCredentials(app)" title="View Applicant Credentials"
                      class="p-1.5 text-teal-600 bg-teal-50 hover:bg-teal-100 rounded-md transition-colors">
                      <Icon name="document" size="4" />
                    </button>
                    <button @click="openDetail(app)" title="View Details"
                      class="p-1.5 text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                      <Icon name="eye" size="4" />
                    </button>
                    <button @click="openUpdate(app)" title="Update Status"
                      class="p-1.5 text-primary bg-primary/8 hover:bg-primary/15 rounded-md transition-colors">
                      <Icon name="document" size="4" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!applications.length">
                <td colspan="9" class="px-5 py-20 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <Icon name="user" size="8" class="text-gray-200" />
                    <p class="text-sm font-medium text-gray-400">No applicants found</p>
                    <button v-if="filters.search || filters.status" @click="clearFilters"
                      class="text-xs text-primary hover:underline">Clear filters</button>
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
              <Icon name="chevronLeft" size="4" />
            </button>
            <span class="px-3 py-1 text-xs font-medium text-gray-700">
              {{ meta.current_page }} / {{ meta.last_page }}
            </span>
            <button :disabled="meta.current_page === meta.last_page" @click="goPage(meta.current_page + 1)"
              class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
              <Icon name="chevronRight" size="4" />
            </button>
          </div>
        </div>

      </div>
    </template>

    <ApplicationDetailDrawer
      :app="detailTarget"
      @close="detailTarget = null"
      @update-status="app => { detailTarget = null; openUpdate(app) }"
    />

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

    <UpdateStatusModal
      :app="updateTarget"
      @close="updateTarget = null"
      @saved="onStatusSaved"
    />

    <BatchUpdateStatusModal
      :open="batchUpdateOpen"
      :ids="[...selectedIds]"
      @close="batchUpdateOpen = false"
      @saved="onBatchSaved"
    />

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
import ApplicationDetailDrawer from './Applications/ApplicationDetailDrawer.vue'
import UpdateStatusModal from './Applications/UpdateStatusModal.vue'
import BatchUpdateStatusModal from './Applications/BatchUpdateStatusModal.vue'
import { useConfirm } from '@/composables/useConfirm'
import { useToast } from '@/composables/useToast'
import Icon from '@/Components/UI/Icon.vue'
import SkeletonLoader from '@/Components/UI/SkeletonLoader.vue'
import { formatDate } from '@/utils/dates'
import { statusLabel, statusBadgeClass } from '@/config/statusConfig'
const { alert } = useConfirm()
const toast = useToast()

// ── State ─────────────────────────────────────────────────────────────────────
const view               = ref('list')
const vacanciesLoading   = ref(true)
const vacancyError       = ref('')
const loading            = ref(false)
const exporting          = ref(false)
const vacancies          = ref([])
const vacancyMeta        = ref({})
const vacancyStatusCounts = ref({})
const applications       = ref([])
const meta                = ref({})
const selectedVacancy    = ref(null)
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

const sortState = reactive({ field: '', direction: 'asc' })
const filters   = reactive({ search: '', status: '', page: 1 })

const vacancyFilters = reactive({ search: '', status: '', sort: '', page: 1 })

const VACANCY_STATUS_TAB_DEFS = [
  { value: '',          label: 'All' },
  { value: 'draft',     label: 'Draft' },
  { value: 'published', label: 'Published' },
  { value: 'closed',    label: 'Closed' },
  { value: 'filled',    label: 'Filled' },
  { value: 'archived',  label: 'Archived' },
]

const vacancyStatusTabs = computed(() =>
  VACANCY_STATUS_TAB_DEFS.map(t => ({
    ...t,
    count: t.value === '' ? (vacancyStatusCounts.value.all ?? 0) : (vacancyStatusCounts.value[t.value] ?? 0),
  }))
)

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
  resetAndFetch()
}

function sortIcon(field) {
  if (sortState.field !== field) return ''
  return sortState.direction === 'asc' ? ' ▲' : ' ▼'
}

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

// ── Helpers ────────────────────────────────────────────────────────────────────
function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function pendingCount(v) {
  const b = v.status_breakdown ?? {}
  return (b.submitted ?? 0) + (b.under_review ?? 0)
}

function initials(user) {
  if (typeof user === 'object' && user) {
    return ((user.first_name?.[0] ?? '') + (user.last_name?.[0] ?? '')).toUpperCase() || '?'
  }
  return String(user ?? '').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase() || '?'
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
    const middle = p.middle_name ? ' ' + p.middle_name.charAt(0).toUpperCase() + '.' : ''
    return `${p.last_name}, ${p.first_name}${middle}`
  }
  return p?.user?.full_name ?? '—'
}

// ── Data fetching ──────────────────────────────────────────────────────────────
async function fetchVacancies() {
  vacanciesLoading.value = true
  vacancyError.value = ''
  try {
    const { data } = await axios.get('/api/vacancy-application-summary', {
      params: {
        search: vacancyFilters.search || undefined,
        status: vacancyFilters.status || undefined,
        sort: vacancyFilters.sort || undefined,
        page: vacancyFilters.page,
      },
      headers: authHeaders(),
    })
    vacancies.value = data.data ?? []
    vacancyMeta.value = data.meta ?? {}
  } catch (e) {
    const status = e.response?.status
    if (status === 401) vacancyError.value = 'Not authenticated. Please log in as Admin or HR Manager.'
    else if (status === 403) vacancyError.value = 'Access denied. You must be logged in as Admin or HR Manager to view this.'
    else vacancyError.value = e.response?.data?.message ?? 'An unexpected error occurred.'
  } finally {
    vacanciesLoading.value = false
  }
}

async function fetchVacancyStatusCounts() {
  try {
    const { data } = await axios.get('/api/vacancies/status-counts', {
      params: { search: vacancyFilters.search || undefined },
      headers: authHeaders(),
    })
    vacancyStatusCounts.value = data
  } catch {
    vacancyStatusCounts.value = {}
  }
}

const onVacancySearch = debounce(() => { resetAndFetchVacancies(); fetchVacancyStatusCounts() }, 350)

function resetAndFetchVacancies() { vacancyFilters.page = 1; fetchVacancies() }
function goVacancyPage(p) { vacancyFilters.page = p; fetchVacancies() }
function clearVacancyFilters() {
  vacancyFilters.search = ''; vacancyFilters.status = ''; vacancyFilters.sort = ''
  resetAndFetchVacancies()
  fetchVacancyStatusCounts()
}

function currentFilterParams() {
  return {
    vacancy_id: selectedVacancy.value?.id,
    search: filters.search || undefined,
    status: filters.status || undefined,
  }
}

async function fetchApplications() {
  if (!selectedVacancy.value) return
  loading.value = true
  try {
    const { data } = await axios.get('/api/applications', {
      params: {
        ...currentFilterParams(),
        page: filters.page,
        sort_by: sortState.field || undefined,
        sort_dir: sortState.field ? sortState.direction : undefined,
      },
      headers: authHeaders(),
    })
    applications.value = data.data ?? []
    meta.value         = data.meta ?? {}
  } catch (e) {
    toast.error(e?.response?.data?.message ?? 'Failed to load applications.')
  } finally {
    loading.value = false
  }
}

async function exportApplicants() {
  exporting.value = true
  try {
    const { data } = await axios.get('/api/applications/export', {
      params: currentFilterParams(),
      headers: authHeaders(),
      responseType: 'blob',
    })
    const url = URL.createObjectURL(new Blob([data], { type: 'text/csv' }))
    const a = document.createElement('a')
    a.href = url
    a.download = `applicants-${selectedVacancy.value?.plantilla_no ?? selectedVacancy.value?.id}.csv`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    toast.error('Failed to export applicants.')
  } finally {
    exporting.value = false
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
async function onBatchSaved() {
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
  sortState.field = ''
  sortState.direction = 'asc'
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
}

// ── Update Status modal ────────────────────────────────────────────────────────
function openUpdate(app) {
  updateTarget.value = app
}

async function onStatusSaved() {
  updateTarget.value = null
  await fetchApplications()
  await fetchVacancies()
}

onMounted(() => {
  fetchVacancies()
  fetchVacancyStatusCounts()
})
</script>
