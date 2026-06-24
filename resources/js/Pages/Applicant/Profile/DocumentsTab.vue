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
        <div class="flex items-start gap-2.5 rounded-lg border border-[#2a338f]/20 bg-[#2a338f]/5 px-4 py-3 text-xs text-[#2a338f] mb-6">
          <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>Only <strong>PDF files</strong> are accepted. Each file must not exceed <strong>5 MB</strong>. Fields marked <span class="text-red-600 font-bold">*</span> are required.</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="doc in docFields" :key="doc.key" :class="doc.full ? 'sm:col-span-2' : ''">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
              {{ doc.label }}
              <span v-if="doc.required" class="text-red-500 normal-case ml-0.5">*</span>
              <span v-else class="normal-case font-normal text-gray-400 ml-1">(optional)</span>
            </p>

            <label
              class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-xl px-4 py-5 cursor-pointer transition-colors group"
              :class="docFiles[doc.key] || docPaths[doc.key]
                ? 'border-green-400 bg-green-50'
                : 'border-gray-200 hover:border-[#2a338f]/40 hover:bg-[#2a338f]/5'">
              <input type="file" :name="doc.key" accept=".pdf" class="sr-only"
                @change="onFileSelect($event, doc.key)" />

              <svg v-if="!(docFiles[doc.key] || docPaths[doc.key])"
                class="w-7 h-7 text-gray-300 group-hover:text-[#2a338f] transition-colors"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
              </svg>
              <svg v-else class="w-7 h-7 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>

              <p class="text-sm font-medium text-center leading-snug"
                :class="docFiles[doc.key] || docPaths[doc.key] ? 'text-green-700' : 'text-gray-500 group-hover:text-[#2a338f]'">
                {{ docFiles[doc.key]?.name ?? (docPaths[doc.key] ? 'Uploaded — click to replace' : 'Click to browse or drag & drop') }}
              </p>
              <p v-if="!(docFiles[doc.key] || docPaths[doc.key])"
                class="text-xs text-gray-400">PDF format only</p>
            </label>

            <div v-if="docPaths[doc.key] && !docFiles[doc.key]" class="mt-1.5 flex justify-end">
              <a :href="viewUrl(docPaths[doc.key])"
                target="_blank"
                class="inline-flex items-center gap-1 text-xs font-medium text-[#2a338f] hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                View document
              </a>
            </div>

            <p v-if="errors[doc.key]" class="mt-1 text-xs text-red-500">{{ errors[doc.key] }}</p>
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

  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  docFields:  { type: Array,  required: true },
  docFiles:   { type: Object, required: true },
  docPaths:   { type: Object, required: true },
  authToken:  { type: String, default: '' },
  isComplete: { type: Boolean, default: false },
  saving:     { type: Boolean, default: false },
  errors:     { type: Object, default: () => ({}) },
})

const emit = defineEmits(['file-select'])

function onFileSelect(event, key) {
  const file = event.target.files[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    alert('File exceeds the 5 MB limit.')
    event.target.value = ''
    return
  }
  emit('file-select', key, file)
}

function viewUrl(path) {
  return path ? `/profile/documents/${path}?token=${props.authToken}` : '#'
}
</script>
