<template>
  <div class="space-y-5">

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-900">Attachments</p>
          <p class="text-xs text-gray-500 mt-0.5">PDF only · Max 5 MB per file</p>
        </div>
      </div>

      <div class="px-6 py-6">
        <div class="flex items-start gap-2.5 rounded-lg border border-primary/20 bg-primary/5 px-4 py-3 text-xs text-primary mb-6">
          <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>Only <strong>PDF files</strong> are accepted. Each file must not exceed <strong>5 MB</strong>. Fields marked <span class="text-red-600 font-bold">*</span> are required.</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="doc in docFields" :key="doc.key" :class="doc.full ? 'sm:col-span-2' : ''">
            <a v-if="doc.templateUrl !== undefined" :href="doc.templateUrl" target="_blank"
              class="inline-flex items-center gap-1.5 mb-3 px-3 py-1.5 text-xs font-semibold text-primary bg-primary/5 border border-primary/20 rounded-lg hover:bg-primary/10 transition-colors">
              <Icon name="download" :size="3.5" />
              Don't have a PDS? Download a template
            </a>

            <BaseFileUploader
              :model-value="docFiles[doc.key]"
              :label="doc.label"
              :required="doc.required"
              :show-optional="!doc.required"
              accept=".pdf"
              :max-size-mb="5"
              :existing-url="docPaths[doc.key] ? viewUrl(docPaths[doc.key]) : ''"
              :existing-label="docTimestamps[doc.key] ? `Updated ${formatTimestamp(docTimestamps[doc.key])}` : ''"
              :error="errors[doc.key]"
              @update:model-value="(file) => $emit('file-select', doc.key, file)"
              @error="alert" />
          </div>
        </div>

        <div v-if="saving" class="mt-6 pt-5 border-t border-gray-100">
          <div class="flex items-center gap-2 text-sm text-gray-500">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Uploading documents…
          </div>
        </div>
      </div>
    </div>

    <div v-if="isComplete"
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-green-200 bg-green-50 px-5 py-4">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-green-800">Your profile is complete!</p>
          <p class="text-xs text-green-700 mt-0.5">You can now apply to open vacancies.</p>
        </div>
      </div>
      <Link href="/applicant/dashboard?tab=vacancies"
        class="flex-shrink-0 self-start sm:self-center px-4 py-2 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg transition-colors">
        Browse Vacancies
      </Link>
    </div>

    <!-- Generate the PDS from the details already captured, instead of
         uploading a separately hand-filled copy. -->
    <GeneratePdsPanel :generated-at="pdsGeneratedAt" @generated="$emit('pds-generated')" />

  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { useConfirm } from '@/composables/useConfirm'
import Icon from '@/Components/UI/Icon.vue'
import BaseFileUploader from '@/Components/UI/BaseFileUploader.vue'
import GeneratePdsPanel from './GeneratePdsPanel.vue'
const { alert } = useConfirm()

const props = defineProps({
  docFields:      { type: Array,  required: true },
  docFiles:       { type: Object, required: true },
  docPaths:       { type: Object, required: true },
  docTimestamps:  { type: Object, default: () => ({}) },
  authToken:      { type: String, default: '' },
  pdsGeneratedAt: { type: String, default: null },
  isComplete:     { type: Boolean, default: false },
  saving:         { type: Boolean, default: false },
  errors:         { type: Object, default: () => ({}) },
})

defineEmits(['file-select', 'pds-generated'])

function viewUrl(path) {
  return path ? `/profile/documents/${path}?token=${props.authToken}` : '#'
}

function formatTimestamp(ts) {
  if (!ts) return ''
  const d = new Date(ts)
  return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
    + ' ' + d.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' })
}
</script>
