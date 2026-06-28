<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-gray-100 flex items-center justify-center p-4 sm:p-6">

    <!-- Error state -->
    <div v-if="error" class="max-w-lg w-full bg-white rounded-2xl shadow-xl border border-red-200 p-8 text-center">
      <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <h2 class="text-lg font-bold text-gray-900 mb-2">Link Invalid or Expired</h2>
      <p class="text-sm text-gray-500">{{ error }}</p>
    </div>

    <!-- Success state -->
    <div v-else-if="submitted" class="max-w-lg w-full">
      <div class="bg-white rounded-2xl shadow-xl border border-green-200 p-8 sm:p-10 text-center">
        <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Report Submitted</h2>
        <p class="text-gray-500 mb-6">Your background investigation report has been received. The HRMPSB will review it and get in touch if needed.</p>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">
          <p class="font-medium">Reference No.</p>
          <p class="font-mono text-xs mt-0.5 opacity-75">{{ submittedRef }}</p>
        </div>
      </div>
    </div>

    <!-- Form -->
    <template v-else>
      <div class="max-w-3xl w-full">

        <!-- Header card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden mb-6">
          <div class="h-2 bg-gradient-to-r from-[#2a338f] via-blue-600 to-[#1a5276]"></div>
          <div class="p-6 sm:p-8">
            <div class="flex items-start gap-4 mb-6">
              <img src="/images/csc-logo.png" alt="CSC Logo" class="h-14 w-14 object-contain flex-shrink-0"
                onerror="this.style.display='none'" />
              <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">
                  Background Investigation Report
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                  Civil Service Commission — Regional Office VIII
                </p>
              </div>
            </div>

            <!-- Info grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 bg-gray-50 rounded-xl p-4 sm:p-5 text-sm">
              <div>
                <span class="text-gray-400 text-xs font-medium uppercase tracking-wider">Position</span>
                <p class="text-gray-900 font-semibold mt-0.5">{{ positionTitle }}</p>
              </div>
              <div>
                <span class="text-gray-400 text-xs font-medium uppercase tracking-wider">Place of Assignment</span>
                <p class="text-gray-900 font-medium mt-0.5">{{ placeOfAssignment }}</p>
              </div>
              <div>
                <span class="text-gray-400 text-xs font-medium uppercase tracking-wider">Item No.</span>
                <p class="text-gray-800 font-mono mt-0.5">{{ itemNumber }}</p>
              </div>
              <div>
                <span class="text-gray-400 text-xs font-medium uppercase tracking-wider">Salary Grade</span>
                <p class="text-gray-800 font-mono mt-0.5">{{ salaryGrade }}</p>
              </div>
              <div class="sm:col-span-2 border-t border-gray-200 pt-3 mt-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                  <div>
                    <span class="text-gray-400 text-xs font-medium uppercase tracking-wider">Investigator</span>
                    <p class="text-gray-900 font-semibold mt-0.5">{{ investigatorName }}</p>
                  </div>
                  <div>
                    <span class="text-gray-400 text-xs font-medium uppercase tracking-wider">Applicant</span>
                    <p class="text-gray-900 font-medium mt-0.5">{{ applicantName }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Progress steps -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden mb-6">
          <div class="px-6 sm:px-8 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between gap-3 flex-wrap">
              <h2 class="text-sm font-semibold text-gray-900">Report Form</h2>
              <span class="text-xs text-gray-400">Step {{ currentStep }} of 3</span>
            </div>
            <div class="flex gap-1.5 mt-3">
              <div v-for="s in 3" :key="s" class="flex-1 h-1.5 rounded-full transition-colors duration-300"
                :class="s <= currentStep ? 'bg-[#2a338f]' : 'bg-gray-200'"></div>
            </div>
          </div>

          <form @submit.prevent="submitReport" class="p-6 sm:p-8 space-y-8">
            <!-- Step 1: Competencies -->
            <div v-if="currentStep === 1">
              <div class="flex items-center justify-between gap-3 flex-wrap mb-1">
                <label class="text-sm font-semibold text-gray-800">
                  I. On Competencies <span class="text-red-500">*</span>
                </label>
                <span class="text-xs font-medium"
                  :class="charCount('on_competencies') >= 1000 ? 'text-green-600' : 'text-gray-400'">
                  {{ Math.min(charCount('on_competencies'), 5000) }} / 5,000
                </span>
              </div>
              <p class="text-xs text-gray-400 mb-3">Describe how the applicant's competencies align with the requirements of the position based on your investigation.</p>
              <textarea
                v-model="form.on_competencies"
                rows="8"
                maxlength="5000"
                class="w-full border border-gray-300 rounded-xl px-4 py-3.5 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] resize-none transition-shadow"
                :class="{ 'border-red-300 bg-red-50/30': errors.on_competencies, 'border-green-300 bg-green-50/30': form.on_competencies.length >= 1000 }"
                placeholder="Assess the applicant's competencies, skills, and qualifications relevant to the position..."
              ></textarea>
              <!-- Character progress bar -->
              <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-300"
                  :class="charPct('on_competencies') >= 100 ? 'bg-green-500' : charPct('on_competencies') >= 50 ? 'bg-amber-400' : 'bg-[#2a338f]'"
                  :style="{ width: Math.min(charPct('on_competencies'), 100) + '%' }"></div>
              </div>
              <div class="flex justify-between mt-1">
                <p v-if="errors.on_competencies" class="text-xs text-red-500">{{ errors.on_competencies }}</p>
                <p v-else class="text-xs text-gray-400">
                  <span v-if="charCount('on_competencies') < 1000">{{ 1000 - charCount('on_competencies') }} more characters needed</span>
                  <span v-else class="text-green-600 font-medium">Minimum reached</span>
                </p>
              </div>
            </div>

            <!-- Step 2: Performance -->
            <div v-if="currentStep === 2">
              <div class="flex items-center justify-between gap-3 flex-wrap mb-1">
                <label class="text-sm font-semibold text-gray-800">
                  II. On Performance &amp; Other Relevant Information <span class="text-red-500">*</span>
                </label>
                <span class="text-xs font-medium"
                  :class="charCount('on_performance') >= 1000 ? 'text-green-600' : 'text-gray-400'">
                  {{ Math.min(charCount('on_performance'), 5000) }} / 5,000
                </span>
              </div>
              <p class="text-xs text-gray-400 mb-3">Provide your assessment of the applicant's performance, work ethic, interpersonal skills, and any other relevant observations.</p>
              <textarea
                v-model="form.on_performance"
                rows="8"
                maxlength="5000"
                class="w-full border border-gray-300 rounded-xl px-4 py-3.5 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] resize-none transition-shadow"
                :class="{ 'border-red-300 bg-red-50/30': errors.on_performance, 'border-green-300 bg-green-50/30': form.on_performance.length >= 1000 }"
                placeholder="Assess the applicant's performance, work history, and other relevant information..."
              ></textarea>
              <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-300"
                  :class="charPct('on_performance') >= 100 ? 'bg-green-500' : charPct('on_performance') >= 50 ? 'bg-amber-400' : 'bg-[#2a338f]'"
                  :style="{ width: Math.min(charPct('on_performance'), 100) + '%' }"></div>
              </div>
              <div class="flex justify-between mt-1">
                <p v-if="errors.on_performance" class="text-xs text-red-500">{{ errors.on_performance }}</p>
                <p v-else class="text-xs text-gray-400">
                  <span v-if="charCount('on_performance') < 1000">{{ 1000 - charCount('on_performance') }} more characters needed</span>
                  <span v-else class="text-green-600 font-medium">Minimum reached</span>
                </p>
              </div>
            </div>

            <!-- Step 3: File Upload & Submit -->
            <div v-if="currentStep === 3">
              <div class="flex items-center justify-between gap-3 flex-wrap mb-1">
                <label class="text-sm font-semibold text-gray-800">
                  Upload Report (PDF) <span class="text-red-500">*</span>
                </label>
              </div>
              <p class="text-xs text-gray-400 mb-3">Upload the completed and signed Background Investigation Report in PDF format. Maximum file size is 10 MB.</p>

              <!-- Drop zone -->
              <div
                @drop.prevent="handleDrop"
                @dragover.prevent
                @click="$refs.fileInput.click()"
                class="relative border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all group"
                :class="selectedFile
                  ? 'border-green-300 bg-green-50/30'
                  : 'border-gray-300 hover:border-[#2a338f] hover:bg-blue-50/30'"
              >
                <input ref="fileInput" type="file" accept=".pdf" @change="handleFile" class="hidden" />

                <!-- File selected -->
                <template v-if="selectedFile">
                  <div class="w-14 h-14 rounded-xl bg-green-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                  </div>
                  <p class="text-sm font-semibold text-gray-800">{{ selectedFile.name }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ (selectedFile.size / 1024 / 1024).toFixed(1) }} MB</p>
                  <button type="button" @click.stop="selectedFile = null; errors.file = ''"
                    class="mt-3 text-xs text-red-500 hover:text-red-700 font-medium transition-colors">
                    Remove file
                  </button>
                </template>

                <!-- No file -->
                <template v-else>
                  <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3 group-hover:bg-[#2a338f]/5 transition-colors">
                    <svg class="w-7 h-7 text-gray-400 group-hover:text-[#2a338f] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                  </div>
                  <p class="text-sm text-gray-500">
                    Drop your PDF here, or <span class="text-[#2a338f] font-semibold">browse files</span>
                  </p>
                  <p class="text-xs text-gray-400 mt-1">PDF only, max 10 MB</p>
                </template>
              </div>
              <p v-if="errors.file" class="text-xs text-red-500 mt-2">{{ errors.file }}</p>
            </div>

            <!-- Error banner -->
            <div v-if="submitError" class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700 flex items-start gap-3">
              <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
              <span>{{ submitError }}</span>
            </div>

            <!-- Navigation buttons -->
            <div class="flex items-center justify-between pt-2 gap-3 flex-wrap">
              <button v-if="currentStep > 1" type="button" @click="currentStep--"
                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                ← Back
              </button>
              <div v-else></div>

              <button v-if="currentStep < 3" type="button" @click="nextStep"
                class="px-6 py-2.5 text-sm font-semibold text-white bg-[#2a338f] hover:bg-[#1e2570] rounded-xl shadow-sm transition-colors">
                Continue →
              </button>
              <button v-else type="submit" :disabled="submitting"
                class="px-8 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm transition-all"
                :class="submitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'">
                <span v-if="submitting" class="flex items-center gap-2">
                  <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  Submitting…
                </span>
                <span v-else class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Submit Report
                </span>
              </button>
            </div>
          </form>
        </div>

        <!-- Privacy notice -->
        <div class="bg-white/80 backdrop-blur rounded-xl border border-gray-200 p-4 sm:p-5 text-xs text-gray-400">
          <div class="flex items-start gap-2.5">
            <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <p>
              This report is confidential and intended solely for the Civil Service Commission's HRMPSB.
              By submitting, you confirm that the information provided is accurate and complete to the best of your knowledge.
              Data will be handled in accordance with the Data Privacy Act of 2012 (RA 10173).
            </p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  token: String,
  investigatorName: String,
  applicantName: String,
  positionTitle: String,
  itemNumber: String,
  salaryGrade: String,
  placeOfAssignment: String,
  error: String,
})

const currentStep = ref(1)
const form = reactive({
  on_competencies: '',
  on_performance: '',
})
const selectedFile = ref(null)
const submitting = ref(false)
const submitted = ref(false)
const submitError = ref('')
const errors = reactive({})
const fileInput = ref(null)
const submittedRef = ref('')

function charCount(field) {
  return form[field]?.length ?? 0
}

function charPct(field) {
  return (charCount(field) / 1000) * 100
}

function handleFile(e) {
  const file = e.target.files[0]
  if (file && file.type === 'application/pdf') {
    if (file.size > 10 * 1024 * 1024) {
      errors.file = 'File exceeds the 10 MB limit.'
      return
    }
    selectedFile.value = file
    errors.file = ''
  } else {
    errors.file = 'Please select a valid PDF file.'
  }
}

function handleDrop(e) {
  const file = e.dataTransfer.files[0]
  if (file && file.type === 'application/pdf') {
    if (file.size > 10 * 1024 * 1024) {
      errors.file = 'File exceeds the 10 MB limit.'
      return
    }
    selectedFile.value = file
    errors.file = ''
  } else {
    errors.file = 'Please drop a valid PDF file.'
  }
}

function nextStep() {
  if (currentStep.value === 1) {
    errors.on_competencies = ''
    if (form.on_competencies.length < 1000) {
      errors.on_competencies = 'Must be at least 1,000 characters.'
      return
    }
  }
  if (currentStep.value === 2) {
    errors.on_performance = ''
    if (form.on_performance.length < 1000) {
      errors.on_performance = 'Must be at least 1,000 characters.'
      return
    }
  }
  currentStep.value++
}

async function submitReport() {
  errors.on_competencies = ''
  errors.on_performance = ''
  errors.file = ''
  submitError.value = ''

  if (form.on_competencies.length < 1000) {
    errors.on_competencies = 'Must be at least 1,000 characters.'
    currentStep.value = 1
    return
  }
  if (form.on_performance.length < 1000) {
    errors.on_performance = 'Must be at least 1,000 characters.'
    currentStep.value = 2
    return
  }
  if (!selectedFile.value) {
    errors.file = 'Please upload the PDF report.'
    currentStep.value = 3
    return
  }

  submitting.value = true
  try {
    const fd = new FormData()
    fd.append('on_competencies', form.on_competencies)
    fd.append('on_performance', form.on_performance)
    fd.append('file', selectedFile.value)

    await axios.post(`/background-investigation/upload/${props.token}`, fd)
    submittedRef.value = 'BI-' + Date.now().toString(36).toUpperCase()
    submitted.value = true
  } catch (e) {
    if (e.response?.data?.errors) {
      Object.assign(errors, e.response.data.errors)
    } else {
      submitError.value = e.response?.data?.message ?? 'Failed to submit report. Please try again.'
    }
  } finally {
    submitting.value = false
  }
}
</script>
