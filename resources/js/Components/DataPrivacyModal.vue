<template>
  <Teleport to="body">
    <div v-if="visible" class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
      style="background: rgba(0,0,0,0.70); backdrop-filter: blur(4px);">

      <!-- Step 1: Image Privacy Notice -->
      <div v-if="step === 1"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col overflow-hidden animate-fade-in max-h-[92vh]">

        <!-- Header -->
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 flex-shrink-0" style="background-color: #2a338f;">
          <img src="/images/csc-logo.png" alt="CSC Logo" class="h-9 w-9 object-contain flex-shrink-0"
            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
          <div class="w-9 h-9 rounded-lg bg-white/15 items-center justify-center flex-shrink-0 hidden">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold text-white leading-tight">Civil Service Commission</p>
            <p class="text-xs text-white/70">Regional Office VIII</p>
          </div>
        </div>

        <!-- Image area -->
        <div class="flex-1 overflow-y-auto flex flex-col items-center justify-center p-6">
          <img
            src="/images/privacy-notice.jpg"
            alt="CSC Data Privacy Notice"
            class="w-full rounded-xl object-contain border-2 border-[#2a338f]"
            style="max-height: 65vh;"
            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />

          <!-- Fallback when image is not yet provided -->
          <div class="w-full hidden flex-col items-center justify-center bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl py-14 text-center gap-3">
            <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 19.5h16.5M13.5 3.75h-3A2.25 2.25 0 008.25 6v.75M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <div>
              <p class="text-sm font-semibold text-gray-500">Privacy Notice Image</p>
              <p class="text-xs text-gray-400 mt-1">Place <code class="bg-gray-100 px-1 rounded">privacy-notice.jpg</code> in <code class="bg-gray-100 px-1 rounded">public/images/</code></p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50">
          <p class="text-xs text-gray-400">1 of 2 — Data Privacy Notice</p>
          <button @click="step = 2"
            class="inline-flex items-center gap-2 px-5 py-2 text-white text-sm font-semibold rounded-lg transition-colors"
            style="background-color: #2a338f;">
            Next
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Step 2: Detailed Privacy Notice -->
      <div v-if="step === 2"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col overflow-hidden animate-fade-in max-h-[90vh]">

        <!-- Header -->
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 flex-shrink-0" style="background-color: #2a338f;">
          <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold text-white leading-tight">Data Privacy Notice</p>
            <p class="text-xs text-white/70">Civil Service Commission Regional Office VIII</p>
          </div>
        </div>

        <!-- Scrollable content -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5 text-sm text-gray-700 leading-relaxed">

          <div>
            <h3 class="font-bold text-gray-900 mb-1">1. Introduction</h3>
            <p>The Civil Service Commission (CSC), through the Civil Service Commission Regional Office VIII - Human Resource Division, is committed to protecting your personal data in accordance with the <strong>Data Privacy Act of 2012</strong> and applicable regulations.</p>
            <p class="mt-2">This Privacy Notice explains how we collect, use, store, and protect your personal data, and informs you of your rights as a data subject.</p>
          </div>

          <div>
            <h3 class="font-bold text-gray-900 mb-1">2. Personal Data We Collect</h3>
            <p class="mb-2">We may collect the following types of personal data:</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
              <li><strong>Basic Information:</strong> e.g., name, address, contact details</li>
              <li><strong>Employment Information:</strong> e.g., position, agency, service records</li>
              <li><strong>Other Information</strong> as may be required for the processing of your application</li>
            </ul>
          </div>

          <div>
            <h3 class="font-bold text-gray-900 mb-1">3. Purpose of Processing</h3>
            <p class="mb-2">Your personal data is processed for the following purposes:</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
              <li>Recruitment and selection processes</li>
              <li>Communication and coordination</li>
              <li>Compliance with legal and regulatory requirements</li>
            </ul>
          </div>

          <div>
            <h3 class="font-bold text-gray-900 mb-1">5. Data Sharing and Disclosure</h3>
            <p class="mb-2">We may share your personal data with:</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
              <li>Other CSC Satellite and Field Offices and units</li>
            </ul>
            <p class="mt-2">All data sharing is done in compliance with applicable laws and regulations.</p>
          </div>

          <div>
            <h3 class="font-bold text-gray-900 mb-1">6. Data Retention</h3>
            <p>Your personal data will be retained only for as long as necessary to fulfill the purposes stated above, or as required by applicable laws, rules, and CSC records retention policies.</p>
            <p class="mt-2">After the retention period, data shall be securely disposed of or anonymized.</p>
          </div>

          <div>
            <h3 class="font-bold text-gray-900 mb-1">8. Security Measures</h3>
            <p>The CSC implements appropriate organizational, physical, and technical security measures to protect your personal data against unauthorized access, disclosure, alteration, or destruction.</p>
          </div>

          <div>
            <h3 class="font-bold text-gray-900 mb-2">9. Contact Information</h3>
            <div class="space-y-3">
              <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                <p class="font-semibold text-gray-900 text-xs uppercase tracking-wide mb-1">Data Protection Officer</p>
                <p class="font-medium text-gray-800">Atty. Ariel G. Ronquillo</p>
                <p class="text-gray-500 text-xs">Data Protection Officer (DPO) · Assistant Commissioner for Legal</p>
                <p class="text-xs mt-1"><span class="text-gray-400">Email:</span> <a href="mailto:dpo@csc.gov.ph" class="text-[#2a338f] hover:underline">dpo@csc.gov.ph</a></p>
              </div>
              <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                <p class="font-semibold text-gray-900 text-xs uppercase tracking-wide mb-1">Compliance Officers for Privacy</p>
                <p class="font-medium text-gray-800">Atty. Antonia B. Quijano / Atty. Charmaine Apelado-Bula</p>
                <p class="text-gray-500 text-xs">Legal Services Division</p>
                <p class="text-xs mt-1"><span class="text-gray-400">Email:</span> <a href="mailto:ro08.lsd@csc.gov.ph" class="text-[#2a338f] hover:underline">ro08.lsd@csc.gov.ph</a></p>
              </div>
            </div>
          </div>

          <div>
            <h3 class="font-bold text-gray-900 mb-1">10. Updates to this Privacy Notice</h3>
            <p>This Privacy Notice may be updated from time to time. Any changes will be posted through official CSC channels.</p>
          </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50 flex-shrink-0">
          <button @click="step = 1"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back
          </button>
          <div class="flex items-center gap-3">
            <p class="text-xs text-gray-400">2 of 2</p>
            <button @click="accept"
              class="inline-flex items-center gap-2 px-5 py-2 text-white text-sm font-semibold rounded-lg transition-colors"
              style="background-color: #2a338f;">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
              </svg>
              I Understand & Accept
            </button>
          </div>
        </div>
      </div>

    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const STORAGE_KEY = 'dp_accepted_v1'

const visible = ref(false)
const step    = ref(1)

onMounted(() => {
  if (!sessionStorage.getItem(STORAGE_KEY)) {
    visible.value = true
  }
  window.addEventListener('keydown', onKey)
})

onUnmounted(() => {
  window.removeEventListener('keydown', onKey)
})

function onKey(e) {
  if (!visible.value) return
  if (e.key !== 'Enter' && e.key !== ' ') return
  e.preventDefault()
  if (step.value === 1) {
    step.value = 2
  } else {
    accept()
  }
}

function accept() {
  sessionStorage.setItem(STORAGE_KEY, '1')
  visible.value = false
}
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: scale(0.97) translateY(8px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.2s ease-out;
}
</style>
