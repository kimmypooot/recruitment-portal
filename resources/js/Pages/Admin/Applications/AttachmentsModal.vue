<template>
  <Teleport to="body">
    <div v-if="app" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="$emit('close')"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col" style="max-height: 90vh;">

        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <div>
            <h3 class="text-base font-semibold text-gray-900">Application Attachments</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ applicantName }}</p>
          </div>
          <button @click="$emit('close')" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5">
          <div v-if="loading" class="space-y-3">
            <div v-for="n in 5" :key="n" class="h-16 bg-gray-100 rounded-xl animate-pulse"></div>
          </div>
          <div v-else class="space-y-3">
            <div v-for="doc in docs" :key="doc.type"
              class="flex items-center justify-between gap-3 p-4 rounded-xl border transition-colors"
              :class="doc.path ? 'border-gray-200 bg-white hover:bg-gray-50' : 'border-dashed border-gray-200 bg-gray-50'">

              <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                  :class="doc.path ? 'bg-red-50' : 'bg-gray-100'">
                  <svg class="w-4.5 h-4.5" :class="doc.path ? 'text-red-500' : 'text-gray-300'"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-900 leading-tight">{{ doc.label }}</p>
                  <p class="text-xs mt-0.5" :class="doc.path ? 'text-green-600' : 'text-gray-400'">
                    {{ doc.path ? 'Uploaded' : 'Not uploaded' }}
                    <span v-if="doc.optional && !doc.path" class="ml-1 text-gray-300">(optional)</span>
                  </p>
                </div>
              </div>

              <div v-if="doc.path" class="flex items-center gap-1.5 flex-shrink-0">
                <button @click="$emit('view', app.id, doc.type)"
                  :disabled="actionId === doc.type + '-view'"
                  class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-[#2a338f] bg-[#2a338f]/8 hover:bg-[#2a338f]/15 rounded-lg transition-colors disabled:opacity-50">
                  <svg v-if="actionId === doc.type + '-view'" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  View
                </button>
                <button @click="$emit('download', app.id, doc.type, doc.label)"
                  :disabled="actionId === doc.type + '-dl'"
                  class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors disabled:opacity-50">
                  <svg v-if="actionId === doc.type + '-dl'" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                  </svg>
                  Download
                </button>
              </div>
              <span v-else class="text-xs text-gray-300 flex-shrink-0">—</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  app: { type: Object, default: null },
  profile: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  actionId: { type: String, default: '' },
})

defineEmits(['close', 'view', 'download'])

const DOCS = [
  { type: 'pds',        label: 'Personal Data Sheet (PDS) with Work Experience Sheet', optional: false },
  { type: 'app_letter', label: 'Application Letter',                                   optional: false },
  { type: 'ipcr',       label: 'IPCR (Individual Performance Commitment & Review)',     optional: true  },
  { type: 'coe',        label: 'Certificate of Eligibility',                            optional: false },
  { type: 'tor',        label: 'Transcript of Records (TOR)',                           optional: false },
]

const PATH_KEY = {
  pds: 'pds_path', app_letter: 'app_letter_path',
  ipcr: 'ipcr_path', coe: 'coe_path', tor: 'tor_path',
}

const docs = computed(() => DOCS.map(d => ({ ...d, path: props.profile?.[PATH_KEY[d.type]] ?? null })))

const applicantName = computed(() => {
  if (!props.app) return ''
  const p = props.app?.applicant
  if (p?.last_name && p?.first_name) {
    const middle = p.middle_name ? ' ' + p.middle_name : ''
    return `${p.last_name}, ${p.first_name}${middle}`
  }
  return p?.user?.full_name ?? '—'
})
</script>
