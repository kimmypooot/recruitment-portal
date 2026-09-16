<template>
  <div class="space-y-5">

    <div class="rounded-xl border px-5 py-4 flex items-start gap-3"
      :class="pdsCanGenerate ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50'">
      <svg class="w-5 h-5 flex-shrink-0 mt-0.5" :class="pdsCanGenerate ? 'text-green-600' : 'text-amber-600'"
        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path v-if="pdsCanGenerate" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
      </svg>
      <div>
        <p class="text-sm font-semibold" :class="pdsCanGenerate ? 'text-green-800' : 'text-amber-800'">
          {{ pdsCanGenerate ? 'Everything below is ready for your PDS.' : 'Some required information below is still missing.' }}
        </p>
        <p class="text-xs mt-0.5" :class="pdsCanGenerate ? 'text-green-700' : 'text-amber-700'">
          Check each section against what you actually filled in — this is your last look before you generate
          the PDS or apply to a vacancy. Use "Edit" on any section to fix something.
        </p>
      </div>
    </div>

    <div v-if="loading" class="bg-white rounded-xl border border-gray-200 p-8 flex items-center justify-center">
      <svg class="w-5 h-5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
      </svg>
    </div>

    <template v-else>
      <ReviewSection title="Personal Information" @edit="$emit('go-to', { tab: 'personal' })">
        <ReviewRow label="Full name" :value="fullName" />
        <ReviewRow label="Gender" :value="personal.gender" />
        <ReviewRow label="Civil status" :value="personal.civil_status" />
        <ReviewRow label="Date of birth" :value="personal.birthday" />
        <ReviewRow label="Region" :value="personal.region" />
        <ReviewRow label="Mobile number" :value="personal.mobile_number" />
      </ReviewSection>

      <ReviewSection title="Additional Personal Details (CS Form 212)"
        @edit="$emit('go-to', { tab: 'pds', anchor: 'section-personal-extra' })">
        <ReviewRow label="Place of birth" :value="personal.place_of_birth" />
        <ReviewRow label="Citizenship" :value="personal.citizenship" />
        <ReviewRow label="Residential address" :value="residentialAddress" />
        <ReviewRow label="Permanent address" :value="permanentAddress" />
      </ReviewSection>

      <ReviewSection title="Family Background" @edit="$emit('go-to', { tab: 'pds', anchor: 'section-family' })">
        <ReviewRow label="Spouse" :value="spouseName" />
        <ReviewRow label="Father" :value="fullPersonName(family, 'father')" />
        <ReviewRow label="Mother's maiden name" :value="fullPersonName(family, 'mother_maiden')" />
        <ReviewRow label="Children" :value="countLabel(children)" />
      </ReviewSection>

      <ReviewSection title="Educational Background" @edit="$emit('go-to', { tab: 'qualifications' })">
        <p v-if="!educationRecords.length" class="text-sm text-gray-400 italic">No education records added yet.</p>
        <ul v-else class="space-y-1.5">
          <li v-for="edu in educationRecords" :key="edu.id" class="text-sm text-gray-700">
            <span class="font-medium text-gray-900">{{ edu.school_name }}</span>
            <span class="text-gray-400"> — {{ edu.level }}<span v-if="edu.year_graduated">, graduated {{ edu.year_graduated }}</span></span>
          </li>
        </ul>
      </ReviewSection>

      <ReviewSection title="Work Experience" @edit="$emit('go-to', { tab: 'qualifications' })">
        <p v-if="!experiences.length" class="text-sm text-gray-400 italic">No work experience added yet.</p>
        <ul v-else class="space-y-1.5">
          <li v-for="exp in experiences" :key="exp.id" class="text-sm text-gray-700">
            <span class="font-medium text-gray-900">{{ exp.position_title }}</span>
            <span class="text-gray-400"> — {{ exp.department_agency }}</span>
          </li>
        </ul>
      </ReviewSection>

      <ReviewSection title="Civil Service Eligibility" @edit="$emit('go-to', { tab: 'qualifications' })">
        <ReviewRow label="Eligibility" :value="personal.eligibility === 'Others' ? personal.eligibility_other : personal.eligibility" />
        <div class="mt-2">
          <p class="text-xs text-gray-400 mb-1.5">
            Detailed eligibility entries printed on the PDS (Section IV) —
            <button type="button" class="text-primary font-semibold hover:underline"
              @click="$emit('go-to', { tab: 'pds', anchor: 'section-eligibility' })">edit on PDS Details</button>
          </p>
          <p v-if="!eligibilities.length" class="text-sm text-gray-400 italic">Nothing recorded yet.</p>
          <ul v-else class="space-y-1">
            <li v-for="e in eligibilities" :key="e.id" class="text-sm text-gray-700">{{ e.eligibility_name || e.name || '—' }}</li>
          </ul>
        </div>
      </ReviewSection>

      <ReviewSection title="Voluntary Work, Training &amp; Other Info"
        @edit="$emit('go-to', { tab: 'pds', anchor: 'section-voluntary' })">
        <ReviewRow label="Voluntary work" :value="countLabel(voluntaryWorks)" />
        <ReviewRow label="Trainings" :value="countLabel(trainings)" />
        <ReviewRow label="Other information (skills, distinctions, memberships)" :value="countLabel(otherInfo)" />
      </ReviewSection>

      <ReviewSection title="Declarations" @edit="$emit('go-to', { tab: 'pds', anchor: 'section-declarations' })">
        <p class="text-sm" :class="declarationsComplete ? 'text-green-700' : 'text-amber-700'">
          {{ declarationsComplete ? 'All declaration questions answered.' : 'Some declaration questions are still unanswered.' }}
        </p>
      </ReviewSection>

      <ReviewSection title="References" @edit="$emit('go-to', { tab: 'pds', anchor: 'section-references' })">
        <p v-if="!namedReferences.length" class="text-sm text-gray-400 italic">No references added yet.</p>
        <ul v-else class="space-y-1">
          <li v-for="r in namedReferences" :key="r.id" class="text-sm text-gray-700">{{ r.name }}</li>
        </ul>
      </ReviewSection>

      <ReviewSection title="Documents" @edit="$emit('go-to', { tab: 'documents' })">
        <ul class="space-y-1.5">
          <li v-for="doc in docFields" :key="doc.key" class="flex items-center gap-2 text-sm">
            <svg v-if="hasDoc(doc.key)" class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <svg v-else class="w-4 h-4 flex-shrink-0" :class="doc.required ? 'text-amber-500' : 'text-gray-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span :class="hasDoc(doc.key) ? 'text-gray-700' : 'text-gray-400'">{{ doc.label }}</span>
            <span v-if="!doc.required" class="text-[11px] text-gray-400">(optional)</span>
          </li>
        </ul>
      </ReviewSection>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h, defineComponent } from 'vue'
import { pdsApi } from '@/services/api'

const props = defineProps({
  name:             { type: Object, required: true },
  personal:         { type: Object, required: true },
  experiences:      { type: Array,  default: () => [] },
  educationRecords: { type: Array,  default: () => [] },
  trainings:        { type: Array,  default: () => [] },
  docFields:        { type: Array,  default: () => [] },
  docPaths:         { type: Object, default: () => ({}) },
  pdsCanGenerate:   { type: Boolean, default: false },
  // Reuse the same completeness data the readiness panel already fetched,
  // rather than re-deriving "is this section done" from raw records here —
  // one source of truth for what counts as complete.
  pdsSections:      { type: Object, default: () => ({}) },
})

defineEmits(['go-to'])

// Small local presentational helpers — kept in this file since they're only
// ever used inside this read-only summary.
const ReviewSection = defineComponent({
  props: { title: String },
  emits: ['edit'],
  setup(p, { slots, emit }) {
    return () => h('div', { class: 'bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden' }, [
      h('div', { class: 'flex items-center justify-between gap-3 px-6 py-4 border-b border-gray-100' }, [
        h('p', { class: 'text-sm font-semibold text-gray-900' }, p.title),
        h('button', {
          type: 'button', class: 'text-xs font-semibold text-primary hover:underline flex-shrink-0',
          onClick: () => emit('edit'),
        }, 'Edit'),
      ]),
      h('div', { class: 'px-6 py-5' }, slots.default?.()),
    ])
  },
})

const ReviewRow = defineComponent({
  props: { label: String, value: { type: String, default: '' } },
  setup(p) {
    return () => h('div', { class: 'flex items-start justify-between gap-4 py-1.5 text-sm' }, [
      h('span', { class: 'text-gray-500 flex-shrink-0' }, p.label),
      h('span', { class: p.value ? 'text-gray-900 font-medium text-right' : 'text-gray-300 italic text-right' }, p.value || 'Not provided'),
    ])
  },
})

const loading = ref(true)
const family = ref({})
const children = ref([])
const eligibilities = ref([])
const voluntaryWorks = ref([])
const otherInfo = ref([])
const references = ref([])
const declaration = ref(null)

onMounted(async () => {
  try {
    const { data } = await pdsApi.show()
    family.value         = data.family_background ?? {}
    children.value       = data.children ?? []
    eligibilities.value  = data.eligibilities ?? []
    voluntaryWorks.value = data.voluntary_works ?? []
    otherInfo.value      = data.other_info ?? []
    references.value     = data.references ?? []
    declaration.value    = data.declaration ?? null
  } catch {
    // Non-fatal: sections that depend on this stay blank rather than blocking the page.
  } finally {
    loading.value = false
  }
})

const fullName = computed(() =>
  [props.name.first_name, props.name.middle_name, props.name.last_name, props.name.suffix]
    .filter(Boolean).join(' ')
)

function addressLine(prefix) {
  const p = props.personal
  return [p[`${prefix}_house_block_lot`], p[`${prefix}_street`], p[`${prefix}_subdivision`],
    p[`${prefix}_barangay`], p[`${prefix}_city_municipality`], p[`${prefix}_province`], p[`${prefix}_zip_code`]]
    .filter(Boolean).join(', ')
}
const residentialAddress = computed(() => addressLine('res'))
const permanentAddress   = computed(() => addressLine('perm'))

function fullPersonName(obj, prefix) {
  return [obj[`${prefix}_first_name`], obj[`${prefix}_surname`]].filter(Boolean).join(' ')
}

const spouseName = computed(() => {
  const s = [family.value.spouse_first_name, family.value.spouse_surname].filter(Boolean).join(' ')
  return s || (props.personal.civil_status === 'Single' ? 'Not applicable' : '')
})

const declarationsComplete = computed(() => !!props.pdsSections.declarations?.complete)

const namedReferences = computed(() => references.value.filter(r => r.name))

function countLabel(list) {
  return list.length ? `${list.length} recorded` : 'None recorded'
}

function hasDoc(key) {
  return !!props.docPaths[key] || (key === 'pds' && !!props.personal.pds_generated_path)
}
</script>
