<template>
  <ApplicantLayout>
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">

      <!-- Full-page skeleton overlay -->
      <div v-if="pageLoading" class="animate-pulse space-y-6">
        <!-- Skeleton header -->
        <div class="space-y-2">
          <div class="h-7 bg-gray-200 rounded w-64"></div>
          <div class="h-4 bg-gray-100 rounded w-96"></div>
        </div>
        <!-- Skeleton tab bar -->
        <div class="bg-white border border-gray-200 rounded-xl p-1.5 shadow-sm">
          <div class="flex gap-1">
            <div v-for="n in 3" :key="n" class="flex-1 h-9 bg-gray-100 rounded-lg"></div>
          </div>
        </div>
        <!-- Skeleton form rows -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5 shadow-sm">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded w-20"></div>
              <div class="h-9 bg-gray-100 rounded"></div>
            </div>
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded w-20"></div>
              <div class="h-9 bg-gray-100 rounded"></div>
            </div>
          </div>
          <div class="space-y-2">
            <div class="h-3 bg-gray-200 rounded w-24"></div>
            <div class="h-9 bg-gray-100 rounded"></div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded w-20"></div>
              <div class="h-9 bg-gray-100 rounded"></div>
            </div>
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded w-20"></div>
              <div class="h-9 bg-gray-100 rounded"></div>
            </div>
          </div>
          <div class="space-y-2">
            <div class="h-3 bg-gray-200 rounded w-24"></div>
            <div class="h-9 bg-gray-100 rounded"></div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded w-16"></div>
              <div class="h-9 bg-gray-100 rounded"></div>
            </div>
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded w-16"></div>
              <div class="h-9 bg-gray-100 rounded"></div>
            </div>
            <div class="space-y-2">
              <div class="h-3 bg-gray-200 rounded w-14"></div>
              <div class="h-9 bg-gray-100 rounded"></div>
            </div>
          </div>
        </div>
        <!-- Skeleton save bar -->
        <div class="h-14 bg-white rounded-lg border border-gray-200 shadow-sm flex items-center justify-end px-6 gap-4">
          <div class="h-4 w-12 bg-gray-200 rounded"></div>
          <div class="h-9 w-32 bg-gray-200 rounded-lg"></div>
        </div>
      </div>

      <!-- Real content -->
      <div v-if="!pageLoading">

      <!-- Page header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Complete Your Profile</h1>
        <p class="mt-1.5 text-sm text-gray-500 max-w-2xl">
          Fill out all sections below before submitting an application.
        </p>
      </div>

      <!-- Step tabs -->
      <div class="bg-white border border-gray-200 rounded-xl p-1.5 mb-7 shadow-sm">
        <div class="flex gap-1">
          <button v-for="tab in tabs" :key="tab.key"
            @click="switchTab(tab.key)"
            class="flex flex-1 items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors min-w-0"
            :class="activeTab === tab.key
              ? 'bg-[#2a338f] text-white shadow-sm'
              : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon"/>
            </svg>
            <span class="truncate">{{ tab.label }}</span>
            <span v-if="tab.complete"
              class="flex-shrink-0 w-2 h-2 rounded-full"
              :class="activeTab === tab.key ? 'bg-green-300' : 'bg-green-500'">
            </span>
          </button>
        </div>
      </div>

      <!-- ═══ Tab 1 — Personal Info ═══ -->
      <PersonalInfoTab
        v-show="activeTab === 'personal'"
        :personal="personal"
        :photo-path="photoPath"
        :photo-url="photoUrl"
        :regions="regions"
        :auth-email="authUser.email"
        :google-id="authUser.google_id"
        :google-avatar="authUser.google_avatar"
        :errors="errors"
        @open-photo-modal="openPhotoModal"
        @google-linked="refreshAuthUser"
        @google-unlinked="refreshAuthUser"
      />

      <!-- ═══ Tab 2 — Qualifications ═══ -->
      <QualificationsTab
        v-show="activeTab === 'qualifications'"
        :personal="personal"
        :experiences="sortedExperiences"
        :education-records="sortedEducation"
        :trainings="sortedTrainings"
        @add-experience="openExpModal()"
        @edit-experience="openExpModal($event)"
        @delete-experience="deleteExperience($event)"
        @add-education="openEduModal()"
        @edit-education="openEduModal($event)"
        @delete-education="deleteEducation($event)"
        @add-training="openTrainingModal()"
        @edit-training="openTrainingModal($event)"
        @delete-training="deleteTraining($event)"
      />

      <!-- ═══ Tab 3 — Documents ═══ -->
      <DocumentsTab
        v-show="activeTab === 'documents'"
        :doc-fields="docFields"
        :doc-files="docFiles"
        :doc-paths="docPaths"
        :auth-token="authToken"
        :is-complete="isComplete"
        :saving="savingDocs"
        :errors="docErrors"
        @file-select="onDocFileSelect"
      />

      <!-- Sticky save bar -->
      <div v-if="activeTab !== 'documents'"
        class="sticky bottom-0 mt-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 bg-white/95 backdrop-blur-sm border-t border-gray-200 flex items-center justify-end gap-4 z-20">
        <div v-if="saveIndicator" class="flex items-center gap-2 text-green-600 text-sm font-medium">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          Saved
        </div>
        <button @click="savePersonal" :disabled="savingPersonal"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
          <svg v-if="savingPersonal" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
          {{ savingPersonal ? 'Saving…' : 'Save Changes' }}
        </button>
      </div>

      <!-- Document upload bar (separate because docs use FormData upload) -->
      <div v-if="activeTab === 'documents'"
        class="sticky bottom-0 mt-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 bg-white/95 backdrop-blur-sm border-t border-gray-200 flex items-center justify-end gap-4 z-20">
        <div v-if="saveIndicator" class="flex items-center gap-2 text-green-600 text-sm font-medium">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          Uploaded
        </div>
        <button @click="uploadDocuments" :disabled="savingDocs"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
          <svg v-if="savingDocs" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
          {{ savingDocs ? 'Uploading…' : 'Upload Documents' }}
        </button>
      </div>

    </div>

    <!-- ═══ MODAL — Add Work Experience ═══ -->
    <Teleport to="body">
      <div v-if="expModal.open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="expModal.open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 flex-shrink-0">
            <div>
              <h3 class="text-base font-semibold text-gray-900">{{ expModal.editingId ? 'Edit Work Experience' : 'Add Work Experience' }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">Fields marked <span class="text-red-500">*</span> are required</p>
            </div>
            <button @click="expModal.open = false"
              class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="px-7 py-6 overflow-y-auto space-y-5">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Position Title <span class="text-red-500 normal-case">*</span></label>
              <input v-model="expForm.position_title" type="text" placeholder="e.g. Administrative Officer II"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Department / Agency <span class="text-red-500 normal-case">*</span></label>
              <input v-model="expForm.department_agency" type="text" placeholder="e.g. Civil Service Commission"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Salary Grade</label>
                <input v-model="expForm.salary_grade" type="text" placeholder="e.g. 15-1"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Appointment Status</label>
                <select v-model="expForm.appointment_status"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
                  <option value="">— Select —</option>
                  <option value="Permanent">Permanent</option>
                  <option value="Temporary">Temporary</option>
                  <option value="Casual">Casual</option>
                  <option value="Contractual">Contractual</option>
                  <option value="Co-terminus">Co-terminus</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Date From <span class="text-red-500 normal-case">*</span></label>
                <input v-model="expForm.date_from" type="date"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Date To</label>
                <input v-model="expForm.date_to" type="date" :disabled="expForm.is_present"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition disabled:opacity-40 disabled:cursor-not-allowed" />
                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                  <input type="checkbox" v-model="expForm.is_present" class="w-4 h-4 accent-blue-700 rounded" />
                  <span class="text-xs text-gray-600">Currently employed here</span>
                </label>
              </div>
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
              <input type="checkbox" v-model="expForm.government_service" class="w-4 h-4 accent-blue-700 rounded" />
              <span class="text-sm text-gray-700 font-medium">This is a Government Service position</span>
            </label>
            <p v-if="expModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ expModal.error }}</p>
          </div>
          <div class="flex items-center justify-end gap-3 px-7 py-4 border-t border-gray-100 flex-shrink-0">
            <button @click="expModal.open = false"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
            <button @click="saveExperience" :disabled="expModal.saving"
              class="inline-flex items-center gap-2 px-5 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60">
              <svg v-if="expModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ expModal.saving ? 'Saving…' : expModal.editingId ? 'Save Changes' : 'Add Experience' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ═══ MODAL — Add Education ═══ -->
    <Teleport to="body">
      <div v-if="eduModal.open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="eduModal.open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 flex-shrink-0">
            <div>
              <h3 class="text-base font-semibold text-gray-900">{{ eduModal.editingId ? 'Edit Education' : 'Add Educational Attainment' }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">Fields marked <span class="text-red-500">*</span> are required</p>
            </div>
            <button @click="eduModal.open = false"
              class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="px-7 py-6 overflow-y-auto space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Level <span class="text-red-500 normal-case">*</span></label>
                <select v-model="eduForm.level"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
                  <option value="">— Select —</option>
                  <option value="Elementary">Elementary</option>
                  <option value="Secondary">Secondary / High School</option>
                  <option value="Vocational / Trade Course">Vocational / Trade Course</option>
                  <option value="College">College</option>
                  <option value="Graduate Studies">Graduate Studies</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Year Graduated</label>
                <input v-model="eduForm.year_graduated" type="text" placeholder="e.g. 2019"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">School Name <span class="text-red-500 normal-case">*</span></label>
              <input v-model="eduForm.school_name" type="text" placeholder="e.g. University of the Philippines Cebu"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Degree / Course</label>
              <input v-model="eduForm.degree_course" type="text" placeholder="e.g. BS Computer Science"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-5 gap-y-5">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Period From</label>
                <input v-model="eduForm.period_from" type="text" placeholder="e.g. 2015"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Period To</label>
                <input v-model="eduForm.period_to" type="text" placeholder="e.g. 2019"
                  :disabled="eduForm.period_to_present"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition disabled:opacity-40 disabled:cursor-not-allowed" />
                <label class="inline-flex items-center gap-2 mt-2 cursor-pointer select-none">
                  <input type="checkbox" v-model="eduForm.period_to_present"
                    @change="eduForm.period_to_present ? (eduForm.period_to = '') : null"
                    class="w-4 h-4 rounded border-gray-300 text-[#2a338f] accent-[#2a338f] cursor-pointer" />
                  <span class="text-xs text-gray-600 font-medium">Present</span>
                </label>
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Units Earned</label>
                <input v-model="eduForm.units_earned" type="text" placeholder="e.g. 140 or N/A"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Scholarship / Honors</label>
              <input v-model="eduForm.honors" type="text" placeholder="e.g. Cum Laude"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>
            <p v-if="eduModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ eduModal.error }}</p>
          </div>
          <div class="flex items-center justify-end gap-3 px-7 py-4 border-t border-gray-100 flex-shrink-0">
            <button @click="eduModal.open = false"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
            <button @click="saveEducation" :disabled="eduModal.saving"
              class="inline-flex items-center gap-2 px-5 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60">
              <svg v-if="eduModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ eduModal.saving ? 'Saving…' : eduModal.editingId ? 'Save Changes' : 'Add Education' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ═══ MODAL — Add Training ═══ -->
    <Teleport to="body">
      <div v-if="trainingModal.open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="trainingModal.open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 flex-shrink-0">
            <div>
              <h3 class="text-base font-semibold text-gray-900">{{ trainingModal.editingId ? 'Edit Training' : 'Add Training' }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">Fields marked <span class="text-red-500">*</span> are required</p>
            </div>
            <button @click="trainingModal.open = false"
              class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="px-7 py-6 overflow-y-auto space-y-5">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Title <span class="text-red-500 normal-case">*</span></label>
              <input v-model="trainingForm.title" type="text" placeholder="e.g. Leadership Seminar"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Date From <span class="text-red-500 normal-case">*</span></label>
                <input v-model="trainingForm.date_from" type="date"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Date To</label>
                <input v-model="trainingForm.date_to" type="date"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Hours</label>
                <input v-model="trainingForm.hours" type="number" min="0" step="0.5" placeholder="e.g. 8"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Type of L&D</label>
                <select v-model="trainingForm.ld_type"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
                  <option value="">— Select —</option>
                  <option value="Managerial">Managerial</option>
                  <option value="Supervisory">Supervisory</option>
                  <option value="Technical">Technical</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Conducted by</label>
              <input v-model="trainingForm.conducted_by" type="text" placeholder="e.g. Civil Service Commission"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>
            <p v-if="trainingModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ trainingModal.error }}</p>
          </div>
          <div class="flex items-center justify-end gap-3 px-7 py-4 border-t border-gray-100 flex-shrink-0">
            <button @click="trainingModal.open = false"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
            <button @click="saveTraining" :disabled="trainingModal.saving"
              class="inline-flex items-center gap-2 px-5 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60">
              <svg v-if="trainingModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ trainingModal.saving ? 'Saving…' : trainingModal.editingId ? 'Save Changes' : 'Add Training' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ═══ MODAL — Upload Profile Photo ═══ -->
    <Teleport to="body">
      <div v-if="photoModal.open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="closePhotoModal">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 flex-shrink-0">
            <div>
              <h3 class="text-base font-semibold text-gray-900">
                {{ photoModal.step === 'select' ? 'Upload Profile Photo' : 'Crop Your Photo' }}
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">
                {{ photoModal.step === 'select' ? 'Choose a clear, professional passport-size photo' : 'Drag to move · Handles to resize' }}
              </p>
            </div>
            <button @click="closePhotoModal"
              class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="overflow-y-auto flex-1">
            <div v-if="photoModal.step === 'select'" class="px-7 py-6 space-y-5">
              <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 space-y-2">
                <p class="text-xs font-semibold text-amber-800 uppercase tracking-wide">Photo Requirements</p>
                <ul class="space-y-1.5">
                  <li v-for="req in photoRequirements" :key="req" class="flex items-start gap-2 text-xs text-amber-700">
                    <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                    </svg>
                    {{ req }}
                  </li>
                </ul>
              </div>
              <label
                class="flex flex-col items-center justify-center gap-3 border-2 border-dashed rounded-xl px-6 py-10 cursor-pointer transition-colors hover:border-[#2a338f]/50 hover:bg-[#2a338f]/5 group"
                :class="photoModal.error ? 'border-red-300 bg-red-50' : 'border-gray-200'">
                <input type="file" accept="image/jpeg,image/png,image/jpg" class="sr-only" @change="onPhotoFileSelect" />
                <div class="w-14 h-14 rounded-full bg-gray-100 group-hover:bg-[#2a338f]/10 flex items-center justify-center transition-colors">
                  <svg class="w-7 h-7 text-gray-400 group-hover:text-[#2a338f] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v13.5A1.5 1.5 0 003.75 21zm10.5-11.25h.008v.008h-.008V9.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                  </svg>
                </div>
                <div class="text-center">
                  <p class="text-sm font-medium text-gray-700 group-hover:text-[#2a338f] transition-colors">Click to browse or drag & drop</p>
                  <p class="text-xs text-gray-400 mt-0.5">JPG or PNG · Max 3 MB</p>
                </div>
              </label>
              <p v-if="photoModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ photoModal.error }}</p>
            </div>
            <div v-else class="px-7 py-5 space-y-4">
              <div class="rounded-xl overflow-hidden bg-gray-900" style="height: 360px;">
                <img ref="cropperImgRef" :src="photoModal.imgSrc" class="block max-w-full" alt="Crop preview" />
              </div>
              <div class="flex items-center justify-between">
                <p class="text-xs text-gray-400">Crop box is freely resizable — no fixed aspect ratio enforced.</p>
                <button @click="photoModal.step = 'select'; destroyCropper()"
                  class="text-xs text-[#2a338f] hover:underline font-medium">← Choose different photo</button>
              </div>
              <p v-if="photoModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ photoModal.error }}</p>
            </div>
          </div>
          <div v-if="photoModal.step === 'crop'"
            class="flex items-center justify-end gap-3 px-7 py-4 border-t border-gray-100 flex-shrink-0">
            <button @click="closePhotoModal"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
            <button @click="savePhoto" :disabled="photoModal.saving"
              class="inline-flex items-center gap-2 px-5 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60">
              <svg v-if="photoModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ photoModal.saving ? 'Saving…' : 'Save Photo' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    </div>

  </ApplicantLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { profileApi } from '@/services/api'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import ApplicantLayout from '@/Layouts/ApplicantLayout.vue'
import PersonalInfoTab from './Profile/PersonalInfoTab.vue'
import QualificationsTab from './Profile/QualificationsTab.vue'
import DocumentsTab from './Profile/DocumentsTab.vue'

const DRAFT_KEY = 'csc_profile_draft'

const pageLoading    = ref(true)
const activeTab      = ref('personal')
const isComplete     = ref(false)
const savingPersonal = ref(false)
const savingDocs     = ref(false)
const saveIndicator  = ref(false)

const photoPath = ref('')
const photoUrl  = computed(() =>
  photoPath.value ? `/profile/photo?token=${authToken}&_=${photoPath.value}` : null
)

const photoModal = reactive({ open: false, saving: false, error: '', step: 'select', imgSrc: '' })
const cropperImgRef = ref(null)
let cropperInstance = null

const photoRequirements = [
  'Recent photo taken within the last 6 months',
  'Plain white or light-colored background',
  'Face the camera directly with eyes open and clearly visible',
  'Neutral expression or a natural, slight smile',
  'No hats, caps, or head coverings (unless for religious reasons)',
  'No sunglasses or tinted eyeglasses',
  'Good, even lighting — avoid shadows on the face',
  'Passport-size or 2×2 inch equivalent is preferred',
]

const experiences = ref([])
const education   = ref([])
const trainings   = ref([])

const sortedExperiences = computed(() =>
  [...experiences.value].sort((a, b) => {
    const aDate = a.is_present ? '9999-99-99' : (a.date_from || '')
    const bDate = b.is_present ? '9999-99-99' : (b.date_from || '')
    return bDate.localeCompare(aDate)
  })
)

const sortedEducation = computed(() =>
  [...education.value].sort((a, b) => {
    const aYear = a.year_graduated || a.period_to || a.period_from || ''
    const bYear = b.year_graduated || b.period_to || b.period_from || ''
    return bYear.localeCompare(aYear)
  })
)

const sortedTrainings = computed(() =>
  [...trainings.value].sort((a, b) => {
    const aDate = a.date_from || ''
    const bDate = b.date_from || ''
    return bDate.localeCompare(aDate)
  })
)

const docFiles = reactive({})
const docPaths = reactive({})

const authUser  = ref(JSON.parse(localStorage.getItem('auth_user') ?? '{}'))
const authToken = localStorage.getItem('auth_token') ?? ''

function refreshAuthUser() {
  const updated = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
  authUser.value = updated
}

const personal = reactive({
  gender: '', civil_status: '', birthday: '', religion: '',
  region: '', province: '', city_municipality: '', barangay: '',
  mobile_number: '',
  eligibility: '', eligibility_other: '',
  indigenous_group: '', pwd: '', solo_parent: '',
})

const errors = reactive({})

const tabs = computed(() => [
  {
    key: 'personal', label: 'Personal Info',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    complete: !!(personal.gender && personal.civil_status && personal.birthday && personal.mobile_number && personal.region),
  },
  {
    key: 'qualifications', label: 'Experience & Education',
    icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    complete: !!(personal.eligibility),
  },
  {
    key: 'documents', label: 'Documents',
    icon: 'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13',
    complete: !!(docPaths.pds && docPaths.app_letter && docPaths.coe && docPaths.tor),
  },
])

const docFields = [
  { key: 'pds',        label: 'Personal Data Sheet (PDS) with Work Experience Sheet', required: true, full: true,
    note: 'CS Form No. 212 (Revised 2025)' },
  { key: 'app_letter', label: 'Application Letter', required: true },
  { key: 'ipcr',       label: 'IPCR', required: false },
  { key: 'coe',        label: 'Certificate of Eligibility', required: true },
  { key: 'tor',        label: 'Authenticated Transcript of Records', required: true },
]

const docErrors = reactive({})

const regions = [
  'NCR - National Capital Region',
  'CAR - Cordillera Administrative Region',
  'Region I - Ilocos Region',
  'Region II - Cagayan Valley',
  'Region III - Central Luzon',
  'Region IV-A - CALABARZON',
  'Region IV-B - MIMAROPA',
  'Region V - Bicol Region',
  'Region VI - Western Visayas',
  'Region VII - Central Visayas',
  'Region VIII - Eastern Visayas',
  'Region IX - Zamboanga Peninsula',
  'Region X - Northern Mindanao',
  'Region XI - Davao Region',
  'Region XII - SOCCSKSARGEN',
  'Region XIII - Caraga',
  'BARMM - Bangsamoro Autonomous Region in Muslim Mindanao',
]

// ─── Modal state ─────────────────────────────────────────────────────────────

const expModal      = reactive({ open: false, saving: false, error: '', editingId: null })
const eduModal      = reactive({ open: false, saving: false, error: '', editingId: null })
const trainingModal = reactive({ open: false, saving: false, error: '', editingId: null })

const expForm = reactive({
  position_title: '', department_agency: '',
  salary_grade: '', appointment_status: '', government_service: false,
  date_from: '', date_to: '', is_present: false,
})

const eduForm = reactive({
  level: '', school_name: '', degree_course: '',
  period_from: '', period_to: '', period_to_present: false,
  units_earned: '', year_graduated: '', honors: '',
})

const trainingForm = reactive({
  title: '', date_from: '', date_to: '', hours: '', ld_type: '', conducted_by: '',
})

// ─── Draft persistence ────────────────────────────────────────────────────────

let draftTimer = null

function saveDraft() {
  clearTimeout(draftTimer)
  draftTimer = setTimeout(() => {
    try {
      const data = { personal: { ...personal }, activeTab: activeTab.value }
      localStorage.setItem(DRAFT_KEY, JSON.stringify(data))
    } catch {}
  }, 800)
}

function restoreDraft() {
  try {
    const raw = localStorage.getItem(DRAFT_KEY)
    if (!raw) return
    const draft = JSON.parse(raw)
    if (draft.personal) {
      Object.keys(draft.personal).forEach(k => {
        if (draft.personal[k] !== undefined && draft.personal[k] !== '') {
          personal[k] = draft.personal[k]
        }
      })
    }
  } catch {}
}

watch(() => ({ ...personal }), saveDraft, { deep: true })

// ─── Unsaved changes guard ───────────────────────────────────────────────────

function switchTab(tabKey) {
  const hasDocChanges = Object.keys(docFiles).length > 0
  if (hasDocChanges && activeTab.value !== tabKey) {
    if (!confirm('You have pending document uploads. Switch tabs anyway?')) return
  }
  activeTab.value = tabKey
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(async () => {
  const tabParam = new URLSearchParams(window.location.search).get('tab')
  if (tabParam && ['personal', 'qualifications', 'documents'].includes(tabParam)) {
    activeTab.value = tabParam
  }

  restoreDraft()

  try {
    const { data } = await profileApi.show()
    if (data.profile) {
      const p = data.profile
      Object.keys(personal).forEach(k => { if (p[k] !== null && p[k] !== undefined) personal[k] = p[k] })
      if (personal.birthday) personal.birthday = String(personal.birthday).substring(0, 10)
      if (p.photo_path) photoPath.value = p.photo_path
      experiences.value = p.work_experiences ?? []
      education.value   = p.educational_attainments ?? []
      trainings.value   = p.trainings ?? []
      ;['pds', 'app_letter', 'ipcr', 'coe', 'tor'].forEach(k => {
        if (p[k + '_path']) docPaths[k] = p[k + '_path']
      })
    }
    isComplete.value = data.is_complete
  } catch {}
  pageLoading.value = false
})

function showSaveIndicator() {
  saveIndicator.value = true
  setTimeout(() => { saveIndicator.value = false }, 2000)
}

// ─── Personal info ────────────────────────────────────────────────────────────

function validateRequired() {
  const required = ['gender', 'civil_status', 'birthday', 'region', 'province', 'city_municipality', 'barangay', 'mobile_number']
  const labels = { gender: 'Gender', civil_status: 'Civil Status', birthday: 'Date of Birth', region: 'Region', province: 'Province', city_municipality: 'City/Municipality', barangay: 'Barangay', mobile_number: 'Mobile Number' }
  let valid = true
  required.forEach(f => {
    if (!personal[f]) { errors[f] = `${labels[f]} is required.`; valid = false }
    else { delete errors[f] }
  })
  return valid
}

async function savePersonal() {
  if (!validateRequired()) return
  savingPersonal.value = true
  try {
    const { data } = await profileApi.update({ ...personal })
    isComplete.value = data.is_complete
    localStorage.removeItem(DRAFT_KEY)
    showSaveIndicator()
  } catch (e) {
    alert(e.response?.data?.message ?? 'Failed to save.')
  } finally {
    savingPersonal.value = false
  }
}

// ─── Documents ────────────────────────────────────────────────────────────────

function onDocFileSelect(key, file) {
  docFiles[key] = file
  delete docErrors[key]
}

async function uploadDocuments() {
  const fd = new FormData()
  let hasFile = false
  ;['pds', 'app_letter', 'ipcr', 'coe', 'tor'].forEach(k => {
    if (docFiles[k]) { fd.append(k, docFiles[k]); hasFile = true }
  })
  if (!hasFile) { alert('Please select at least one file to upload.'); return }

  savingDocs.value = true
  try {
    const { data } = await profileApi.uploadDocuments(fd)
    ;['pds', 'app_letter', 'ipcr', 'coe', 'tor'].forEach(k => {
      if (data.profile[k + '_path']) { docPaths[k] = data.profile[k + '_path']; delete docFiles[k] }
    })
    isComplete.value = data.is_complete
    showSaveIndicator()
  } catch (e) {
    alert(e.response?.data?.message ?? 'Upload failed.')
  } finally {
    savingDocs.value = false
  }
}

// ─── Experience ───────────────────────────────────────────────────────────────

function openExpModal(exp = null) {
  Object.assign(expForm, {
    position_title: exp?.position_title ?? '', department_agency: exp?.department_agency ?? '',
    salary_grade: exp?.salary_grade ?? '', appointment_status: exp?.appointment_status ?? '',
    government_service: exp?.government_service ?? false,
    date_from: exp?.date_from ?? '', date_to: exp?.date_to ?? '', is_present: exp?.is_present ?? false,
  })
  expModal.editingId = exp?.id ?? null
  expModal.error     = ''
  expModal.open      = true
}

async function saveExperience() {
  if (!expForm.position_title || !expForm.department_agency || !expForm.date_from) {
    expModal.error = 'Position title, agency, and date from are required.'
    return
  }
  expModal.saving = true
  expModal.error  = ''
  try {
    if (expModal.editingId) {
      const { data } = await profileApi.updateExperience(expModal.editingId, { ...expForm })
      const idx = experiences.value.findIndex(e => e.id === expModal.editingId)
      if (idx >= 0) experiences.value[idx] = data
    } else {
      const { data } = await profileApi.addExperience({ ...expForm })
      experiences.value.push(data)
    }
    expModal.open = false
  } catch (e) {
    expModal.error = e.response?.data?.message ?? 'Failed to save.'
  } finally {
    expModal.saving = false
  }
}

async function deleteExperience(id) {
  if (!confirm('Remove this work experience?')) return
  await profileApi.deleteExperience(id)
  experiences.value = experiences.value.filter(e => e.id !== id)
}

// ─── Education ────────────────────────────────────────────────────────────────

function openEduModal(edu = null) {
  const isPresent = edu?.period_to === 'Present'
  Object.assign(eduForm, {
    level: edu?.level ?? '', school_name: edu?.school_name ?? '',
    degree_course: edu?.degree_course ?? '',
    period_from: edu?.period_from ?? '',
    period_to: isPresent ? '' : (edu?.period_to ?? ''),
    period_to_present: isPresent,
    units_earned: edu?.units_earned ?? '', year_graduated: edu?.year_graduated ?? '',
    honors: edu?.honors ?? '',
  })
  eduModal.editingId = edu?.id ?? null
  eduModal.error     = ''
  eduModal.open      = true
}

async function saveEducation() {
  if (!eduForm.level || !eduForm.school_name) {
    eduModal.error = 'Level and school name are required.'
    return
  }
  eduModal.saving = true
  eduModal.error  = ''
  try {
    const payload = { ...eduForm }
    if (payload.period_to_present) payload.period_to = 'Present'
    delete payload.period_to_present
    if (eduModal.editingId) {
      const { data } = await profileApi.updateEducation(eduModal.editingId, payload)
      const idx = education.value.findIndex(e => e.id === eduModal.editingId)
      if (idx >= 0) education.value[idx] = data
    } else {
      const { data } = await profileApi.addEducation(payload)
      education.value.push(data)
    }
    eduModal.open = false
  } catch (e) {
    eduModal.error = e.response?.data?.message ?? 'Failed to save.'
  } finally {
    eduModal.saving = false
  }
}

async function deleteEducation(id) {
  if (!confirm('Remove this education record?')) return
  await profileApi.deleteEducation(id)
  education.value = education.value.filter(e => e.id !== id)
}

// ─── Training ─────────────────────────────────────────────────────────────────

function openTrainingModal(t = null) {
  Object.assign(trainingForm, {
    title: t?.title ?? '', date_from: t?.date_from ?? '', date_to: t?.date_to ?? '',
    hours: t?.hours ?? '', ld_type: t?.ld_type ?? '', conducted_by: t?.conducted_by ?? '',
  })
  trainingModal.editingId = t?.id ?? null
  trainingModal.error     = ''
  trainingModal.open      = true
}

async function saveTraining() {
  if (!trainingForm.title || !trainingForm.date_from) {
    trainingModal.error = 'Title and date from are required.'
    return
  }
  trainingModal.saving = true
  trainingModal.error  = ''
  try {
    if (trainingModal.editingId) {
      const { data } = await profileApi.updateTraining(trainingModal.editingId, { ...trainingForm })
      const idx = trainings.value.findIndex(t => t.id === trainingModal.editingId)
      if (idx >= 0) trainings.value[idx] = data
    } else {
      const { data } = await profileApi.addTraining({ ...trainingForm })
      trainings.value.push(data)
    }
    trainingModal.open = false
  } catch (e) {
    trainingModal.error = e.response?.data?.message ?? 'Failed to save.'
  } finally {
    trainingModal.saving = false
  }
}

async function deleteTraining(id) {
  if (!confirm('Remove this training record?')) return
  await profileApi.deleteTraining(id)
  trainings.value = trainings.value.filter(t => t.id !== id)
}

// ─── Photo ────────────────────────────────────────────────────────────────────

function openPhotoModal() {
  photoModal.open  = true
  photoModal.step  = 'select'
  photoModal.error = ''
  photoModal.imgSrc = ''
  destroyCropper()
}

function closePhotoModal() {
  photoModal.open = false
  destroyCropper()
}

function destroyCropper() {
  if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null }
}

async function onPhotoFileSelect(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file) return
  if (!file.type.startsWith('image/')) { photoModal.error = 'Please select a JPG or PNG image.'; return }
  if (file.size > 3 * 1024 * 1024) { photoModal.error = 'Image must be under 3 MB.'; return }

  const reader = new FileReader()
  reader.onload = async (ev) => {
    photoModal.imgSrc = ev.target.result
    photoModal.step   = 'crop'
    photoModal.error  = ''
    destroyCropper()
    await nextTick()
    if (cropperImgRef.value) {
      cropperInstance = new Cropper(cropperImgRef.value, {
        viewMode: 1, dragMode: 'move', autoCropArea: 0.85,
        guides: true, center: true, highlight: false, background: true,
        cropBoxMovable: true, cropBoxResizable: true, toggleDragModeOnDblclick: false,
      })
    }
  }
  reader.readAsDataURL(file)
}

async function savePhoto() {
  if (!cropperInstance) return
  photoModal.saving = true
  photoModal.error  = ''
  try {
    const canvas = cropperInstance.getCroppedCanvas({ maxWidth: 800, maxHeight: 800, imageSmoothingEnabled: true, imageSmoothingQuality: 'high' })
    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.92))
    const fd = new FormData()
    fd.append('photo', blob, 'profile-photo.jpg')
    const { data } = await profileApi.uploadPhoto(fd)
    photoPath.value = data.photo_path
    photoModal.open = false
    destroyCropper()
    showSaveIndicator()
  } catch (e) {
    photoModal.error = e.response?.data?.message ?? 'Upload failed.'
  } finally {
    photoModal.saving = false
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
