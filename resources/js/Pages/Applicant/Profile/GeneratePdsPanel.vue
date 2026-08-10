<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-200">
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
          <h3 class="text-base font-semibold text-gray-900">Personal Data Sheet</h3>
          <p class="mt-1 text-sm text-gray-500">
            CS Form No. 212 (Revised 2026), generated from the details you entered above.
          </p>
        </div>
        <span v-if="generatedAt"
          class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
          Last generated {{ formatDate(generatedAt) }}
        </span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="px-6 py-6 animate-pulse space-y-3">
      <div v-for="n in 4" :key="n" class="h-4 bg-gray-100 rounded w-2/3"></div>
    </div>

    <template v-else>
      <!-- Section checklist -->
      <ul class="divide-y divide-gray-100">
        <li v-for="section in orderedSections" :key="section.key"
          class="px-6 py-3 flex items-start gap-3">
          <span class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center"
            :class="statusClass(section)">
            <svg v-if="section.complete" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span v-else class="text-[10px] font-bold leading-none">!</span>
          </span>

          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-sm font-medium text-gray-800">{{ section.label }}</span>
              <span v-if="section.optional"
                class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Optional</span>
            </div>
            <p v-if="!section.complete && section.missing.length"
              class="mt-0.5 text-xs" :class="section.optional ? 'text-gray-400' : 'text-amber-600'">
              {{ describeMissing(section) }}
            </p>
          </div>
        </li>
      </ul>

      <!-- Actions -->
      <div class="px-6 py-5 bg-gray-50 border-t border-gray-200">
        <p v-if="!canGenerate" class="mb-3 text-sm text-amber-700">
          Complete the highlighted sections above to generate your PDS.
        </p>

        <div class="flex flex-wrap items-center gap-3">
          <button type="button" :disabled="!canGenerate || downloading === 'xlsx'"
            @click="download('xlsx')"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            :class="canGenerate ? 'bg-primary hover:bg-primary/90' : 'bg-gray-400'">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12v8m0 0l-3-3m3 3l3-3M12 3v6"/>
            </svg>
            {{ downloading === 'xlsx' ? 'Preparing…' : 'Download Excel' }}
          </button>

          <button type="button" :disabled="!canGenerate || downloading === 'pdf'"
            @click="download('pdf')"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold border transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            :class="canGenerate
              ? 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'
              : 'border-gray-200 text-gray-400 bg-white'">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            {{ downloading === 'pdf' ? 'Preparing…' : 'Download PDF' }}
          </button>
        </div>

        <p class="mt-3 text-xs text-gray-500">
          Print, sign, and have the form notarised before submitting it. The generated file follows
          CSC Memorandum Circular No. 05, s. 2026.
        </p>

        <!-- The attachment stands on its own: it needs work history, not a
             complete PDS, so it is not gated on the checklist above. -->
        <div class="mt-5 pt-5 border-t border-gray-200">
          <p class="text-sm font-medium text-gray-800">Work Experience Sheet</p>
          <p class="mt-0.5 mb-3 text-xs text-gray-500">
            Attachment to CS Form No. 212, built from the duties and accomplishments on your work history.
          </p>
          <div class="flex flex-wrap items-center gap-3">
            <button type="button" :disabled="downloading === 'wes-docx'"
              @click="downloadSheet('docx')"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors disabled:opacity-50">
              {{ downloading === 'wes-docx' ? 'Preparing…' : 'Download Word' }}
            </button>
            <button type="button" :disabled="downloading === 'wes-pdf'"
              @click="downloadSheet('pdf')"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors disabled:opacity-50">
              {{ downloading === 'wes-pdf' ? 'Preparing…' : 'Download PDF' }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { pdsApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  generatedAt: { type: String, default: null },
})

const emit = defineEmits(['generated'])

const toast = useToast()

const loading     = ref(true)
const downloading = ref(null)
const sections    = ref({})
const canGenerate = ref(false)

/** Printed order of the form, so the checklist reads like the PDS itself. */
const SECTION_LABELS = {
  personal_information:      'I. Personal Information',
  family_background:         'II. Family Background',
  educational_background:    'III. Educational Background',
  civil_service_eligibility: 'IV. Civil Service Eligibility',
  work_experience:           'V. Work Experience',
  voluntary_work:            'VI. Voluntary Work',
  learning_development:      'VII. Learning and Development',
  other_information:         'VIII. Other Information',
  declarations:              'Declarations (items 34–42)',
  references:                'References (item 41)',
}

const FIELD_LABELS = {
  place_of_birth:      'place of birth',
  citizenship:         'citizenship',
  residential_address: 'residential address',
  permanent_address:   'permanent address',
  sex_at_birth:        'sex at birth',
  civil_status:        'civil status',
  date_of_birth:       'date of birth',
  mobile_no:           'mobile number',
  father_name:         "father's name",
  mother_maiden_name:  "mother's maiden name",
  elementary:          'elementary',
  secondary:           'secondary',
}

const orderedSections = computed(() =>
  Object.keys(SECTION_LABELS)
    .filter(key => sections.value[key])
    .map(key => ({
      key,
      label: SECTION_LABELS[key],
      ...sections.value[key],
    }))
)

function statusClass(section) {
  if (section.complete) return 'bg-green-100 text-green-700'
  return section.optional ? 'bg-gray-100 text-gray-400' : 'bg-amber-100 text-amber-700'
}

/**
 * The declaration reports one entry per unanswered question, which is far too
 * noisy to list; summarise it instead.
 */
function describeMissing(section) {
  if (section.key === 'declarations') {
    const n = section.missing.length
    return `${n} question${n === 1 ? '' : 's'} still unanswered`
  }

  if (section.key === 'references') {
    const need = section.missing[0]?.match(/need_(\d+)_more/)?.[1]
    return need ? `${need} more reference${need === '1' ? '' : 's'} required` : 'Incomplete'
  }

  if (section.optional) return 'Nothing recorded yet'

  return 'Missing: ' + section.missing.map(f => FIELD_LABELS[f] ?? f.replace(/_/g, ' ')).join(', ')
}

function formatDate(value) {
  if (!value) return ''
  return new Date(value).toLocaleDateString('en-PH', { day: '2-digit', month: 'short', year: 'numeric' })
}

async function load() {
  loading.value = true
  try {
    const { data } = await pdsApi.completeness()
    sections.value    = data.sections ?? {}
    canGenerate.value = !!data.can_generate
  } catch {
    toast.error('Could not check your PDS progress. Please refresh and try again.')
  } finally {
    loading.value = false
  }
}

async function download(format) {
  downloading.value = format
  try {
    const response = await pdsApi.download(format)
    saveBlob(response.data, `PDS.${format}`, response.headers)
    emit('generated')
  } catch (error) {
    toast.error(await readError(error, format))
  } finally {
    downloading.value = null
  }
}

/**
 * Errors come back as a blob because the request asks for one, so the JSON body
 * has to be read back out before it can be shown.
 */
async function downloadSheet(format) {
  downloading.value = `wes-${format}`
  try {
    const response = await pdsApi.downloadWorkExperienceSheet(format)
    saveBlob(response.data, `WorkExperienceSheet.${format}`, response.headers)
  } catch (error) {
    toast.error(await readError(error, format))
  } finally {
    downloading.value = null
  }
}

async function readError(error, format) {
  const status = error?.response?.status

  if (status === 503) {
    return format === 'pdf'
      ? 'PDF generation is unavailable on this server. The Excel download still works.'
      : 'PDS generation is temporarily unavailable.'
  }

  try {
    const text = await error.response.data.text()
    const body = JSON.parse(text)
    if (body?.message) return body.message
  } catch { /* fall through to the generic message */ }

  return 'Could not generate your PDS. Please try again.'
}

function saveBlob(blob, fallbackName, headers) {
  const disposition = headers?.['content-disposition'] ?? ''
  const match = disposition.match(/filename="?([^"]+)"?/)
  const url = window.URL.createObjectURL(new Blob([blob]))
  const link = document.createElement('a')

  link.href = url
  link.download = match?.[1] ?? fallbackName
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(url)
}

defineExpose({ reload: load })

onMounted(load)
</script>
