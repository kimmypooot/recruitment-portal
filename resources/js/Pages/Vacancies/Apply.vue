<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">

    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <Link href="/" class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-blue-700 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
          </div>
          <div class="leading-tight">
            <p class="text-sm font-bold text-gray-900">CSC Regional Office</p>
            <p class="text-xs text-gray-500">Recruitment Portal</p>
          </div>
        </Link>
        <nav class="flex items-center gap-5">
          <Link href="/" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Browse Vacancies</Link>
          <Link href="/applicant/dashboard" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Dashboard</Link>
          <button @click="logout" class="text-sm text-red-500 hover:text-red-700 font-medium">Sign out</button>
        </nav>
      </div>
    </header>

    <main class="flex-1 max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">

      <!-- Loading state -->
      <div v-if="loading" class="space-y-4">
        <div class="h-8 w-64 bg-gray-200 rounded animate-pulse"></div>
        <div class="h-40 bg-gray-100 rounded-xl animate-pulse"></div>
        <div class="h-56 bg-gray-100 rounded-xl animate-pulse"></div>
      </div>

      <template v-else-if="vacancy && profile">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6">
          <Link href="/" class="hover:text-blue-700 transition-colors">Vacancies</Link>
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
          </svg>
          <span class="text-gray-600 font-medium truncate">{{ vacancy.position_title }}</span>
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
          </svg>
          <span class="text-gray-600 font-medium">Review & Submit</span>
        </nav>

        <!-- Page header -->
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900">Review Your Application</h1>
          <p class="text-sm text-gray-500 mt-1">
            Confirm your details below. To make changes,
            <Link href="/applicant/complete-profile" class="text-blue-700 hover:underline font-medium">update your profile</Link>
            first.
          </p>
        </div>

        <!-- ── Vacancy Summary Card ─────────────────────────────────── -->
        <div class="bg-blue-700 text-white rounded-xl p-6 mb-6">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-blue-200 mb-1">Applying for</p>
              <h2 class="text-xl font-bold leading-snug">{{ vacancy.position_title }}</h2>
              <p class="text-blue-200 text-sm mt-1 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ vacancy.place_of_assignment }}
              </p>
            </div>
            <div class="text-right flex-shrink-0">
              <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-white/20 text-white">
                SG-{{ vacancy.salary_grade }}
              </span>
              <p class="text-xs text-blue-200 mt-2">
                Deadline: <span class="font-semibold text-white">{{ formatDate(vacancy.deadline_at) }}</span>
              </p>
            </div>
          </div>

          <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 pt-5 border-t border-blue-600">
            <div>
              <p class="text-xs text-blue-300 font-medium">Education Req.</p>
              <p class="text-sm text-white mt-0.5">{{ vacancy.education_req ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-blue-300 font-medium">Experience Req.</p>
              <p class="text-sm text-white mt-0.5">{{ vacancy.experience_req ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-blue-300 font-medium">Eligibility Req.</p>
              <p class="text-sm text-white mt-0.5">{{ vacancy.eligibility_req ?? '—' }}</p>
            </div>
          </div>
        </div>

        <!-- ── Already Applied Banner ──────────────────────────────── -->
        <div v-if="existingApplication"
          class="mb-6 rounded-xl border border-green-200 bg-green-50 p-5 flex items-start gap-4">
          <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-green-800">You've already applied for this position</p>
            <p class="text-xs text-green-700 mt-0.5">
              Submitted on {{ formatDate(existingApplication.created_at) }} · Status:
              <span class="font-semibold capitalize">{{ existingApplication.status?.replace(/_/g, ' ') }}</span>
            </p>
          </div>
          <Link href="/applicant/applications"
            class="flex-shrink-0 px-3 py-1.5 text-xs font-semibold text-green-800 bg-green-100 hover:bg-green-200 rounded-lg transition-colors">
            View Application
          </Link>
        </div>

        <!-- ── Profile Sections ────────────────────────────────────── -->

        <!-- Personal Information -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-5 overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
              <p class="text-sm font-semibold text-gray-900">Personal Information</p>
            </div>
            <Link href="/applicant/complete-profile?tab=personal"
              class="text-xs text-blue-700 hover:underline font-medium flex items-center gap-1">
              Edit
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
              </svg>
            </Link>
          </div>
          <div class="px-6 py-5 grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4">
            <div>
              <p class="text-xs text-gray-400 font-medium">Full Name</p>
              <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ authUser.name ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium">Email Address</p>
              <p class="text-sm text-gray-700 mt-0.5">{{ authUser.email ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium">Mobile Number</p>
              <p class="text-sm text-gray-700 mt-0.5">{{ profile.mobile_number ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium">Gender</p>
              <p class="text-sm text-gray-700 mt-0.5">{{ profile.gender ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium">Civil Status</p>
              <p class="text-sm text-gray-700 mt-0.5">{{ profile.civil_status ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium">Date of Birth</p>
              <p class="text-sm text-gray-700 mt-0.5">{{ profile.birthday ? formatDate(profile.birthday) : '—' }}</p>
            </div>
            <div class="col-span-2 sm:col-span-3">
              <p class="text-xs text-gray-400 font-medium">Address</p>
              <p class="text-sm text-gray-700 mt-0.5">{{ addressLine }}</p>
            </div>
          </div>
        </div>

        <!-- Qualifications -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-5 overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
              </div>
              <p class="text-sm font-semibold text-gray-900">Qualifications</p>
            </div>
            <Link href="/applicant/complete-profile?tab=qualifications"
              class="text-xs text-blue-700 hover:underline font-medium flex items-center gap-1">
              Edit
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
              </svg>
            </Link>
          </div>
          <div class="px-6 py-5 space-y-5">

            <!-- Eligibility -->
            <div>
              <p class="text-xs text-gray-400 font-medium mb-0.5">Civil Service Eligibility</p>
              <p class="text-sm font-semibold text-gray-900">{{ profile.eligibility ?? '—' }}</p>
            </div>

            <!-- Work Experience -->
            <div>
              <p class="text-xs text-gray-400 font-medium mb-2">Work Experience ({{ (profile.work_experiences ?? []).length }} record{{ (profile.work_experiences ?? []).length !== 1 ? 's' : '' }})</p>
              <div v-if="(profile.work_experiences ?? []).length" class="space-y-2">
                <div v-for="exp in (profile.work_experiences ?? []).slice(0, 3)" :key="exp.id"
                  class="flex items-start justify-between gap-4 py-2.5 px-3 rounded-lg bg-gray-50">
                  <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ exp.position_title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ exp.department_agency }} · {{ formatDateRange(exp.date_from, exp.is_present ? null : exp.date_to, exp.is_present) }}</p>
                  </div>
                  <span v-if="exp.government_service"
                    class="flex-shrink-0 text-xs font-medium px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">Gov't</span>
                </div>
                <p v-if="(profile.work_experiences ?? []).length > 3"
                  class="text-xs text-gray-400 pl-3">
                  + {{ (profile.work_experiences ?? []).length - 3 }} more records
                </p>
              </div>
              <p v-else class="text-sm text-gray-400 italic">No work experience records</p>
            </div>

            <!-- Education -->
            <div>
              <p class="text-xs text-gray-400 font-medium mb-2">Educational Attainment ({{ (profile.educational_attainments ?? []).length }} record{{ (profile.educational_attainments ?? []).length !== 1 ? 's' : '' }})</p>
              <div v-if="(profile.educational_attainments ?? []).length" class="space-y-2">
                <div v-for="edu in profile.educational_attainments" :key="edu.id"
                  class="py-2.5 px-3 rounded-lg bg-gray-50">
                  <p class="text-sm font-medium text-gray-900">{{ edu.school_name }}</p>
                  <p class="text-xs text-gray-500 mt-0.5">{{ edu.level }} · {{ edu.degree_course ?? '—' }} · {{ edu.year_graduated ?? '—' }}</p>
                </div>
              </div>
              <p v-else class="text-sm text-gray-400 italic">No education records</p>
            </div>

          </div>
        </div>

        <!-- Documents -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-8 overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
              </div>
              <p class="text-sm font-semibold text-gray-900">Uploaded Documents</p>
            </div>
            <Link href="/applicant/complete-profile?tab=documents"
              class="text-xs text-blue-700 hover:underline font-medium flex items-center gap-1">
              Edit
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
              </svg>
            </Link>
          </div>
          <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div v-for="doc in docList" :key="doc.key"
              class="flex items-center gap-3 py-2.5 px-3 rounded-lg"
              :class="doc.uploaded ? 'bg-green-50' : 'bg-red-50'">
              <svg class="w-4 h-4 flex-shrink-0" :class="doc.uploaded ? 'text-green-500' : 'text-red-400'"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path v-if="doc.uploaded" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold" :class="doc.uploaded ? 'text-green-800' : 'text-red-700'">{{ doc.label }}</p>
                <p class="text-xs mt-0.5" :class="doc.uploaded ? 'text-green-600' : 'text-red-500'">
                  {{ doc.uploaded ? 'Uploaded' : (doc.required ? 'Required — not uploaded' : 'Optional — not uploaded') }}
                </p>
              </div>
              <a v-if="doc.uploaded && doc.url"
                :href="doc.url"
                target="_blank"
                rel="noopener noreferrer"
                class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-green-700 bg-white border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                View
              </a>
            </div>
          </div>
        </div>

        <!-- ── Submit / Already Applied ────────────────────────────── -->
        <div v-if="!existingApplication">

          <!-- Missing required docs warning -->
          <div v-if="missingRequiredDocs.length"
            class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div>
              <p class="text-sm font-semibold text-amber-800">Missing required documents</p>
              <p class="text-xs text-amber-700 mt-0.5">
                Please upload: {{ missingRequiredDocs.join(', ') }} before submitting.
              </p>
              <Link href="/applicant/complete-profile?tab=documents"
                class="inline-block mt-2 text-xs font-semibold text-amber-800 underline">
                Upload documents →
              </Link>
            </div>
          </div>

          <!-- Error -->
          <div v-if="submitError"
            class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ submitError }}
          </div>

          <!-- Declaration + Submit -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <p class="text-xs text-gray-500 leading-relaxed mb-5">
              By submitting this application, I certify that all information provided in my profile is true and correct to the best of my knowledge. I understand that any misrepresentation may disqualify my application.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
              <Link href="/"
                class="w-full sm:w-auto text-center px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
              </Link>
              <button
                @click="submitApplication"
                :disabled="isSubmitting || missingRequiredDocs.length > 0"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                {{ isSubmitting ? 'Submitting…' : 'Submit Application' }}
              </button>
            </div>
          </div>
        </div>

      </template>

      <!-- Error state -->
      <div v-else-if="!loading" class="text-center py-24">
        <p class="text-gray-500 text-sm">Unable to load vacancy details. Please try again.</p>
        <Link href="/" class="mt-4 inline-block text-blue-700 hover:underline text-sm font-medium">Back to vacancies</Link>
      </div>

    </main>

    <!-- Success modal -->
    <Teleport to="body">
      <div v-if="showSuccess"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-center">
          <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Application Submitted!</h3>
          <p class="text-sm text-gray-500 mb-6">
            Your application for <span class="font-semibold text-gray-700">{{ vacancy?.position_title }}</span> has been received. We'll notify you of any updates.
          </p>
          <Link href="/applicant/dashboard"
            class="block w-full py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
            Go to Dashboard
          </Link>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { vacancyApi, applicationApi, profileApi } from '@/services/api'

const props = defineProps({
  vacancyId: { type: Number, required: true },
})

const loading          = ref(true)
const vacancy          = ref(null)
const profile          = ref(null)
const existingApplication = ref(null)
const isSubmitting     = ref(false)
const submitError      = ref('')
const showSuccess      = ref(false)

const authUser = JSON.parse(localStorage.getItem('auth_user') ?? '{}')

// ── Derived ──────────────────────────────────────────────────────────────────

const addressLine = computed(() => {
  const p = profile.value
  if (!p) return '—'
  return [p.barangay, p.city_municipality, p.province, p.region].filter(Boolean).join(', ') || '—'
})

const docList = computed(() => {
  const p = profile.value ?? {}
  const token = localStorage.getItem('auth_token') ?? ''
  const url = (path) => path ? `/profile/documents/${path}?token=${token}` : null
  return [
    { key: 'pds',        label: 'Personal Data Sheet (PDS)', required: true,  uploaded: !!p.pds_path,        url: url(p.pds_path) },
    { key: 'app_letter', label: 'Application Letter',        required: true,  uploaded: !!p.app_letter_path, url: url(p.app_letter_path) },
    { key: 'coe',        label: 'Certificate of Eligibility',required: true,  uploaded: !!p.coe_path,        url: url(p.coe_path) },
    { key: 'tor',        label: 'Transcript of Records',     required: true,  uploaded: !!p.tor_path,        url: url(p.tor_path) },
    { key: 'ipcr',       label: 'IPCR',                     required: false, uploaded: !!p.ipcr_path,       url: url(p.ipcr_path) },
  ]
})

const missingRequiredDocs = computed(() =>
  docList.value.filter(d => d.required && !d.uploaded).map(d => d.label)
)

// ── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(async () => {
  if (!localStorage.getItem('auth_token')) {
    router.visit('/login')
    return
  }

  try {
    const [vacancyRes, profileRes, appsRes] = await Promise.all([
      vacancyApi.show(props.vacancyId),
      profileApi.show(),
      applicationApi.myApplications(),
    ])

    vacancy.value = vacancyRes.data?.data ?? vacancyRes.data
    profile.value = profileRes.data.profile

    // Profile completion gate
    if (!profileRes.data.is_complete) {
      router.visit('/applicant/complete-profile', {
        data: { redirect_message: 'Please complete your profile before applying.' },
      })
      return
    }

    // Already-applied guard
    const apps = appsRes.data ?? []
    existingApplication.value = apps.find(a => a.vacancy_id === props.vacancyId) ?? null

  } catch {
    // 401 handled by api.js interceptor
  } finally {
    loading.value = false
  }
})

// ── Actions ──────────────────────────────────────────────────────────────────

async function submitApplication() {
  isSubmitting.value = true
  submitError.value  = ''
  try {
    await applicationApi.submit({ vacancy_id: props.vacancyId })
    showSuccess.value = true
  } catch (e) {
    submitError.value = e.response?.data?.message ?? 'Submission failed. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}

function logout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  router.visit('/login')
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric',
  })
}

function formatDateRange(from, to, isPresent = false) {
  if (!from) return '—'

  const MONTHS = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'Jun.',
                  'Jul.', 'Aug.', 'Sep.', 'Oct.', 'Nov.', 'Dec.']

  const parse = (str) => {
    const [y, m, d] = str.split('-').map(Number)
    return { y, m: m - 1, d }
  }

  const f = parse(from)
  const fromStr = `${MONTHS[f.m]} ${f.d}`

  if (isPresent) return `${fromStr}, ${f.y} – Present`
  if (!to)       return `${fromStr}, ${f.y}`

  const t = parse(to)

  // Same month & year: "Jan. 1-3, 2020"
  if (f.m === t.m && f.y === t.y) {
    if (f.d === t.d) return `${fromStr}, ${f.y}`
    return `${MONTHS[f.m]} ${f.d}-${t.d}, ${f.y}`
  }

  // Different month, same year: "Jan. 30 - Feb. 2, 2020"
  if (f.y === t.y) {
    return `${fromStr} - ${MONTHS[t.m]} ${t.d}, ${f.y}`
  }

  // Different years: "Jan. 1, 2020 - Feb. 3, 2021"
  return `${fromStr}, ${f.y} - ${MONTHS[t.m]} ${t.d}, ${t.y}`
}
</script>
