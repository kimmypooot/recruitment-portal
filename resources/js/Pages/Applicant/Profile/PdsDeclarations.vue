<template>
  <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <header class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
      <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
        </svg>
      </div>
      <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-900">Declarations</p>
        <p class="text-xs text-gray-500 mt-0.5">
          Items 34–42 — you must answer every question. These are made under oath.
        </p>
      </div>
      <span v-if="unanswered > 0"
        class="ml-auto flex-shrink-0 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
        {{ unanswered }} unanswered
      </span>
      <span v-else
        class="ml-auto flex-shrink-0 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
        All answered
      </span>
    </header>

    <div class="divide-y divide-gray-100">
      <div v-for="question in QUESTIONS" :key="question.field" class="px-6 py-5">
        <div class="flex items-start justify-between gap-4 flex-wrap">
          <p class="text-sm text-gray-800 flex-1 min-w-[16rem]">
            <span class="font-semibold text-gray-500 mr-1.5">{{ question.number }}</span>
            {{ question.text }}
          </p>

          <div class="flex items-center gap-2 flex-shrink-0">
            <button v-for="choice in [true, false]" :key="String(choice)"
              type="button" @click="answer(question.field, choice)"
              class="px-4 py-1.5 rounded-lg text-xs font-bold border transition-colors"
              :class="form[question.field] === choice
                ? (choice ? 'bg-rose-600 border-rose-600 text-white' : 'bg-gray-700 border-gray-700 text-white')
                : 'bg-white border-gray-300 text-gray-500 hover:border-gray-400'">
              {{ choice ? 'YES' : 'NO' }}
            </button>
          </div>
        </div>

        <!-- Follow-ups only matter once the answer is YES. -->
        <div v-if="question.details && form[question.field] === true" class="mt-3 grid gap-3"
          :class="question.extra ? 'sm:grid-cols-3' : 'sm:grid-cols-1'">
          <div :class="question.extra ? 'sm:col-span-1' : ''">
            <label :class="labelClass">{{ question.detailsLabel || 'If YES, give details' }}</label>
            <input v-model="form[question.details]" type="text" @change="scheduleSave" :class="inputClass" />
          </div>

          <div v-for="extra in question.extra || []" :key="extra.field">
            <label :class="labelClass">{{ extra.label }}</label>
            <input v-model="form[extra.field]" :type="extra.type || 'text'" @change="scheduleSave" :class="inputClass" />
          </div>
        </div>
      </div>

      <!-- Item 42 -->
      <div class="px-6 py-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Government-issued ID</p>
        <p class="text-xs text-gray-400 mb-3">
          Shown to the person administering your oath. Item 42.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-x-6 gap-y-5">
          <div>
            <label :class="labelClass">ID type</label>
            <input v-model="form.government_id_type" type="text" placeholder="e.g. PRC, Passport"
              @change="scheduleSave" :class="inputClass" />
          </div>
          <div>
            <label :class="labelClass">ID / License / Passport No.</label>
            <input v-model="form.government_id_number" type="text" @change="scheduleSave" :class="inputClass" />
          </div>
          <div>
            <label :class="labelClass">Date of issuance</label>
            <input v-model="form.government_id_issuance_date" type="date" @change="scheduleSave" :class="inputClass" />
          </div>
          <div>
            <label :class="labelClass">Place of issuance</label>
            <input v-model="form.government_id_issuance_place" type="text" @change="scheduleSave" :class="inputClass" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted } from 'vue'
import { pdsApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const emit = defineEmits(['changed'])

const toast = useToast()

const labelClass = 'block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5'
const inputClass = 'w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition'

/** Wording follows the printed form; item numbers match CS Form No. 212. */
const QUESTIONS = [
  {
    number: '34 a.', field: 'q34a_related_third_degree', details: 'q34_details',
    text: 'Are you related within the third degree by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office, or to the person who has immediate supervision over you?',
  },
  {
    number: '34 b.', field: 'q34b_related_fourth_degree',
    text: 'Are you related within the fourth degree (for Local Government Unit career employees)?',
  },
  {
    number: '35 a.', field: 'q35a_guilty_admin_offense', details: 'q35a_details',
    text: 'Have you ever been found guilty of any administrative offense?',
  },
  {
    number: '35 b.', field: 'q35b_criminally_charged', details: 'q35b_details',
    text: 'Have you been criminally charged before any court?',
    extra: [
      { field: 'q35b_date_filed', label: 'Date filed', type: 'date' },
      { field: 'q35b_case_status', label: 'Status of case/s' },
    ],
  },
  {
    number: '36.', field: 'q36_convicted', details: 'q36_details',
    text: 'Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?',
  },
  {
    number: '37.', field: 'q37_separated_from_service', details: 'q37_details',
    text: 'Have you ever been separated from the service through resignation, retirement, dropping from the rolls, dismissal, termination, end of term, finished contract, or phasing out (abolition) in the public or private sector?',
  },
  {
    number: '38 a.', field: 'q38a_election_candidate', details: 'q38a_details',
    text: 'Have you ever been a candidate in a national or local election held within the last year (except Barangay elections)?',
  },
  {
    number: '38 b.', field: 'q38b_resigned_to_campaign', details: 'q38b_details',
    text: 'Have you resigned from government service during the three-month period before the last election to promote or actively campaign for a national or local candidate?',
  },
  {
    number: '39.', field: 'q39_immigrant_status', details: 'q39_country',
    detailsLabel: 'If YES, country',
    text: 'Have you acquired the status of an immigrant or permanent resident of another country?',
  },
  {
    number: '40 a.', field: 'q40a_indigenous_group', details: 'q40a_group_name',
    detailsLabel: 'If YES, please specify',
    text: 'Are you a member of any indigenous group? (RA 8371)',
  },
  {
    number: '40 b.', field: 'q40b_pwd', details: 'q40b_id_no',
    detailsLabel: 'If YES, ID number',
    text: 'Are you a person with disability? (RA 7277, as amended)',
  },
  {
    number: '40 c.', field: 'q40c_solo_parent', details: 'q40c_id_no',
    detailsLabel: 'If YES, ID number',
    text: 'Are you a solo parent? (RA 11861)',
  },
]

const form    = reactive({})
const loading = ref(true)

const unanswered = computed(() =>
  QUESTIONS.filter(q => form[q.field] === null || form[q.field] === undefined).length
)

let timer = null

function scheduleSave() {
  if (loading.value) return
  clearTimeout(timer)
  timer = setTimeout(save, 700)
}

watch(form, scheduleSave)

/**
 * Answering is explicit: clicking the current answer again clears it back to
 * unanswered rather than flipping it, so nobody can be nudged into a YES/NO
 * they did not intend on a sworn document.
 */
function answer(field, value) {
  form[field] = form[field] === value ? null : value
}

async function load() {
  loading.value = true
  try {
    const { data } = await pdsApi.show()
    const declaration = data.declaration ?? {}

    QUESTIONS.forEach(({ field, details, extra }) => {
      form[field] = declaration[field] ?? null
      if (details) form[details] = declaration[details] ?? ''
      ;(extra ?? []).forEach(({ field: key }) => {
        form[key] = declaration[key] ? String(declaration[key]).substring(0, 10) : ''
      })
    })

    form.government_id_type          = declaration.government_id_type ?? ''
    form.government_id_number        = declaration.government_id_number ?? ''
    form.government_id_issuance_date = declaration.government_id_issuance_date
      ? String(declaration.government_id_issuance_date).substring(0, 10) : ''
    form.government_id_issuance_place = declaration.government_id_issuance_place ?? ''
  } catch {
    toast.error('Could not load your declarations.')
  } finally {
    await nextTick()
    loading.value = false
  }
}

async function save() {
  // Empty strings would fail the date rules; send null instead.
  const payload = Object.fromEntries(
    Object.entries(form).map(([key, value]) => [key, value === '' ? null : value])
  )

  try {
    await pdsApi.updateDeclaration(payload)
    emit('changed')
  } catch {
    toast.error('Could not save your declarations.')
  }
}

onMounted(load)

defineExpose({ reload: load })
</script>
