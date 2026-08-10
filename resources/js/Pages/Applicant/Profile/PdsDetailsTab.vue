<template>
  <div class="space-y-5">

    <!-- Section I extras -->
    <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <header class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-900">Personal Information</p>
          <p class="text-xs text-gray-500 mt-0.5">Section I — required by CS Form No. 212 but not part of the basic profile</p>
        </div>
      </header>

      <div class="px-6 py-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-5">
          <div class="sm:col-span-1">
            <label :class="labelClass">Place of Birth <span class="text-red-500 normal-case">*</span></label>
            <input v-model="personal.place_of_birth" type="text" placeholder="e.g. Tacloban City" :class="inputClass" />
          </div>
          <div>
            <label :class="labelClass">Height (m)</label>
            <input v-model="personal.height_m" type="number" step="0.01" min="0" max="3" placeholder="1.65" :class="inputClass" />
          </div>
          <div>
            <label :class="labelClass">Weight (kg)</label>
            <input v-model="personal.weight_kg" type="number" step="0.01" min="0" placeholder="60" :class="inputClass" />
          </div>
          <div>
            <label :class="labelClass">Blood Type</label>
            <input v-model="personal.blood_type" type="text" placeholder="e.g. O+" :class="inputClass" />
          </div>
          <div>
            <label :class="labelClass">Telephone No.</label>
            <input v-model="personal.telephone_no" type="text" placeholder="(053) 000-0000" :class="inputClass" />
          </div>
          <div v-if="personal.civil_status === 'Other'">
            <label :class="labelClass">Please specify</label>
            <input v-model="personal.civil_status_other" type="text" :class="inputClass" />
          </div>
        </div>

        <!-- Citizenship -->
        <div class="pt-5 border-t border-gray-100">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">
            Citizenship <span class="text-red-500 normal-case">*</span>
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-5">
            <div>
              <label :class="labelClass">Status</label>
              <select v-model="personal.citizenship" :class="inputClass">
                <option value="">Select…</option>
                <option value="filipino">Filipino</option>
                <option value="dual">Dual Citizenship</option>
              </select>
            </div>
            <div v-if="personal.citizenship === 'dual'">
              <label :class="labelClass">Acquired</label>
              <select v-model="personal.citizenship_basis" :class="inputClass">
                <option value="">Select…</option>
                <option value="by_birth">By birth</option>
                <option value="by_naturalization">By naturalization</option>
              </select>
            </div>
            <div v-if="personal.citizenship === 'dual'">
              <label :class="labelClass">Country</label>
              <input v-model="personal.dual_citizenship_country" type="text" :class="inputClass" />
            </div>
          </div>
        </div>

        <!-- Government IDs -->
        <div class="pt-5 border-t border-gray-100">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Government ID Numbers</p>
          <p class="text-xs text-gray-400 mb-3">Leave blank if you do not have one — the form will print N/A.</p>
          <div class="grid grid-cols-1 sm:grid-cols-4 gap-x-6 gap-y-5">
            <div v-for="field in idFields" :key="field.key">
              <label :class="labelClass">{{ field.label }}</label>
              <input v-model="personal[field.key]" type="text" :class="inputClass" />
            </div>
          </div>
        </div>

        <!-- Addresses -->
        <div class="pt-5 border-t border-gray-100">
          <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
              Residential Address <span class="text-red-500 normal-case">*</span>
            </p>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-5">
            <div v-for="field in addressFields" :key="`res_${field.key}`">
              <label :class="labelClass">{{ field.label }}</label>
              <input v-model="personal[`res_${field.key}`]" type="text" :class="inputClass" />
            </div>
          </div>
        </div>

        <div class="pt-5 border-t border-gray-100">
          <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
              Permanent Address <span class="text-red-500 normal-case">*</span>
            </p>
            <button type="button" @click="copyResidentialAddress"
              class="text-xs font-semibold text-primary hover:text-primary-dark transition-colors">
              Same as residential
            </button>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-5">
            <div v-for="field in addressFields" :key="`perm_${field.key}`">
              <label :class="labelClass">{{ field.label }}</label>
              <input v-model="personal[`perm_${field.key}`]" type="text" :class="inputClass" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section II — Family Background -->
    <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <header class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-900">Family Background</p>
          <p class="text-xs text-gray-500 mt-0.5">Section II — spouse, parents, and children</p>
        </div>
      </header>

      <div class="px-6 py-6 space-y-6">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Spouse</p>
          <p class="text-xs text-gray-400 mb-3">Leave blank if not married.</p>
          <div class="grid grid-cols-1 sm:grid-cols-4 gap-x-6 gap-y-5">
            <div v-for="field in spouseFields" :key="field.key">
              <label :class="labelClass">{{ field.label }}</label>
              <input v-model="family[field.key]" type="text" :class="inputClass" />
            </div>
          </div>
        </div>

        <div class="pt-5 border-t border-gray-100">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">
            Father <span class="text-red-500 normal-case">*</span>
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-4 gap-x-6 gap-y-5">
            <div v-for="field in fatherFields" :key="field.key">
              <label :class="labelClass">{{ field.label }}</label>
              <input v-model="family[field.key]" type="text" :class="inputClass" />
            </div>
          </div>
        </div>

        <div class="pt-5 border-t border-gray-100">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">
            Mother's Maiden Name <span class="text-red-500 normal-case">*</span>
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-5">
            <div v-for="field in motherFields" :key="field.key">
              <label :class="labelClass">{{ field.label }}</label>
              <input v-model="family[field.key]" type="text" :class="inputClass" />
            </div>
          </div>
        </div>

        <!-- Children -->
        <div class="pt-5 border-t border-gray-100">
          <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Children</p>
              <p class="text-xs text-gray-400 mt-0.5">List all children. Leave empty if none.</p>
            </div>
            <button type="button" @click="addChild" :disabled="children.length >= MAX_CHILDREN"
              :class="addButtonClass">+ Add child</button>
          </div>

          <p v-if="!children.length" class="text-sm text-gray-400 italic">No children recorded.</p>

          <ul v-else class="space-y-3">
            <li v-for="child in children" :key="child.id"
              class="grid grid-cols-1 sm:grid-cols-[1fr_180px_auto] gap-3 items-end">
              <div>
                <label :class="labelClass">Full name</label>
                <input v-model="child.full_name" type="text" @change="saveChild(child)" :class="inputClass" />
              </div>
              <div>
                <label :class="labelClass">Date of birth</label>
                <input v-model="child.date_of_birth" type="date" @change="saveChild(child)" :class="inputClass" />
              </div>
              <button type="button" @click="removeItem('children', child)" :class="removeButtonClass">
                Remove
              </button>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Sections IV, VI, VIII — repeating lists -->
    <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <header class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <div class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-900">Eligibility, Voluntary Work &amp; Other Information</p>
          <p class="text-xs text-gray-500 mt-0.5">Sections IV, VI and VIII</p>
        </div>
      </header>

      <div class="px-6 py-6 space-y-8">
        <PdsRepeatingSection
          section="eligibilities"
          title="Civil Service Eligibility"
          description="Section IV, item 27 — include board/bar eligibility under RA 1080."
          add-label="Add eligibility"
          empty-text="No eligibility recorded yet."
          :fields="eligibilityFields"
          :items="eligibilities"
          :max="7"
          @changed="$emit('changed')"
        />

        <div class="pt-6 border-t border-gray-100">
          <PdsRepeatingSection
            section="voluntary-works"
            title="Voluntary Work"
            description="Section VI, item 29 — civic, non-government, people's or voluntary organisations."
            add-label="Add voluntary work"
            empty-text="No voluntary work recorded yet."
            :fields="voluntaryFields"
            :items="voluntaryWorks"
            :max="7"
            @changed="$emit('changed')"
          />
        </div>

        <div v-for="group in otherInfoGroups" :key="group.kind" class="pt-6 border-t border-gray-100">
          <PdsRepeatingSection
            section="other-info"
            :title="group.title"
            :description="group.description"
            :add-label="group.addLabel"
            :empty-text="group.emptyText"
            :fields="otherInfoFields"
            :items="otherInfoFor(group.kind)"
            :defaults="{ kind: group.kind }"
            :max="7"
            @changed="$emit('changed')"
          />
        </div>
      </div>
    </section>

    <!-- Page 4 — Declarations -->
    <PdsDeclarations @changed="$emit('changed')" />

    <!-- Item 41 — References -->
    <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <header class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-sm font-semibold text-gray-900">References</p>
          <p class="text-xs text-gray-500 mt-0.5">
            Item 41 — exactly {{ REQUIRED_REFERENCES }} people, not related to you by consanguinity or affinity
          </p>
        </div>
      </header>

      <div class="px-6 py-6 space-y-4">
        <div v-for="(reference, index) in references" :key="reference.id"
          class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_200px_auto] gap-3 items-end">
          <div>
            <label :class="labelClass">Name {{ index + 1 }}</label>
            <input v-model="reference.name" type="text" placeholder="First name, MI, Surname"
              @change="saveReference(reference)" :class="inputClass" />
          </div>
          <div>
            <label :class="labelClass">Office / residential address</label>
            <input v-model="reference.office_residential_address" type="text"
              @change="saveReference(reference)" :class="inputClass" />
          </div>
          <div>
            <label :class="labelClass">Contact no. and/or email</label>
            <input v-model="reference.contact_no_or_email" type="text"
              @change="saveReference(reference)" :class="inputClass" />
          </div>
          <button type="button" @click="removeItem('references', reference)" :class="removeButtonClass">
            Remove
          </button>
        </div>

        <button v-if="references.length < REQUIRED_REFERENCES" type="button" @click="addReference"
          :class="addButtonClass">
          + Add reference ({{ references.length }} of {{ REQUIRED_REFERENCES }})
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, watch, nextTick, onMounted } from 'vue'
import { pdsApi } from '@/services/api'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'
import PdsRepeatingSection from './PdsRepeatingSection.vue'
import PdsDeclarations from './PdsDeclarations.vue'

const props = defineProps({
  // Section I fields live on the shared profile object so the page's existing
  // save bar persists them alongside the rest of the profile.
  personal: { type: Object, required: true },
})

const emit = defineEmits(['changed'])

const toast = useToast()
const { confirm } = useConfirm()

const REQUIRED_REFERENCES = 3
const MAX_CHILDREN = 12

const labelClass = 'block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5'
const inputClass = 'w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition'
const addButtonClass = 'inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-dashed border-gray-300 text-xs font-semibold text-gray-600 hover:border-primary hover:text-primary transition-colors disabled:opacity-40 disabled:cursor-not-allowed'
const removeButtonClass = 'px-3 py-2.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors'

const idFields = [
  { key: 'gsis_id_no', label: 'GSIS ID' },
  { key: 'pagibig_id_no', label: 'Pag-IBIG ID' },
  { key: 'philhealth_no', label: 'PhilHealth' },
  { key: 'sss_no', label: 'SSS' },
  { key: 'tin_no', label: 'TIN' },
  { key: 'umid_id_no', label: 'UMID ID' },
  { key: 'philsys_number', label: 'PhilSys (PSN)' },
  { key: 'agency_employee_no', label: 'Agency Employee No.' },
]

const addressFields = [
  { key: 'house_block_lot', label: 'House / Block / Lot No.' },
  { key: 'street', label: 'Street' },
  { key: 'subdivision', label: 'Subdivision / Village' },
  { key: 'barangay', label: 'Barangay' },
  { key: 'city_municipality', label: 'City / Municipality' },
  { key: 'province', label: 'Province' },
  { key: 'zip_code', label: 'ZIP Code' },
]

const spouseFields = [
  { key: 'spouse_surname', label: 'Surname' },
  { key: 'spouse_first_name', label: 'First name' },
  { key: 'spouse_middle_name', label: 'Middle name' },
  { key: 'spouse_name_extension', label: 'Name extension' },
  { key: 'spouse_occupation', label: 'Occupation' },
  { key: 'spouse_employer', label: 'Employer / business name' },
  { key: 'spouse_business_address', label: 'Business address' },
  { key: 'spouse_telephone', label: 'Telephone no.' },
]

const fatherFields = [
  { key: 'father_surname', label: 'Surname' },
  { key: 'father_first_name', label: 'First name' },
  { key: 'father_middle_name', label: 'Middle name' },
  { key: 'father_name_extension', label: 'Name extension' },
]

const motherFields = [
  { key: 'mother_maiden_surname', label: 'Surname' },
  { key: 'mother_maiden_first_name', label: 'First name' },
  { key: 'mother_maiden_middle_name', label: 'Middle name' },
]

const eligibilityFields = [
  { key: 'eligibility_name', label: 'Eligibility', required: true, placeholderValue: 'New eligibility' },
  { key: 'rating', label: 'Rating (if applicable)' },
  { key: 'examination_date', label: 'Date of exam / conferment', type: 'date' },
  { key: 'examination_place', label: 'Place of exam / conferment' },
  { key: 'license_number', label: 'License number' },
  { key: 'license_valid_until', label: 'Valid until', type: 'date' },
]

const voluntaryFields = [
  { key: 'organization_name_address', label: 'Name & address of organisation', required: true, placeholderValue: 'New organisation' },
  { key: 'date_from', label: 'From', type: 'date' },
  { key: 'date_to', label: 'To', type: 'date' },
  { key: 'number_of_hours', label: 'No. of hours', type: 'number', step: '1' },
  { key: 'position_nature_of_work', label: 'Position / nature of work' },
]

const otherInfoFields = [
  { key: 'value', label: 'Entry', required: true, placeholderValue: 'New entry' },
]

/**
 * Section VIII prints three separate columns that share one table, so each is
 * rendered as its own list filtered by `kind`.
 */
const otherInfoGroups = [
  {
    kind: 'special_skill_hobby', title: 'Special Skills and Hobbies',
    description: 'Item 31.', addLabel: 'Add skill or hobby', emptyText: 'No skills or hobbies recorded yet.',
  },
  {
    kind: 'non_academic_distinction', title: 'Non-Academic Distinctions / Recognition',
    description: 'Item 32. Write in full.', addLabel: 'Add distinction', emptyText: 'No distinctions recorded yet.',
  },
  {
    kind: 'membership', title: 'Membership in Association / Organization',
    description: 'Item 33. Write in full.', addLabel: 'Add membership', emptyText: 'No memberships recorded yet.',
  },
]

const family        = reactive({})
const children      = ref([])
const references    = ref([])
const eligibilities = ref([])
const voluntaryWorks = ref([])
const otherInfo     = ref([])

const otherInfoFor = (kind) => otherInfo.value.filter(row => row.kind === kind)

/**
 * Family background has no save button of its own, so it autosaves on a debounce.
 * `loading` suppresses the watcher while load() populates the form, otherwise
 * hydrating the fields would immediately POST them straight back.
 */
const loading = ref(true)
let familyTimer = null

watch(family, () => {
  if (loading.value) return
  clearTimeout(familyTimer)
  familyTimer = setTimeout(saveFamily, 800)
})

async function load() {
  loading.value = true
  try {
    const { data } = await pdsApi.show()

    Object.assign(family, data.family_background ?? {})
    children.value       = (data.children ?? []).map(normaliseDates)
    references.value     = data.references ?? []
    eligibilities.value  = data.eligibilities ?? []
    voluntaryWorks.value = data.voluntary_works ?? []
    otherInfo.value      = data.other_info ?? []
  } catch {
    toast.error('Could not load your PDS details.')
  } finally {
    // Let the assignments above settle before the watcher goes live.
    await nextTick()
    loading.value = false
  }
}

/** <input type="date"> needs a bare yyyy-mm-dd, not an ISO timestamp. */
function normaliseDates(child) {
  return { ...child, date_of_birth: child.date_of_birth ? String(child.date_of_birth).substring(0, 10) : '' }
}

async function saveFamily() {
  try {
    await pdsApi.updateFamilyBackground(family)
    emit('changed')
  } catch {
    toast.error('Could not save family background.')
  }
}

async function addChild() {
  try {
    const { data } = await pdsApi.addItem('children', { full_name: 'New child' })
    children.value.push(normaliseDates(data))
  } catch {
    toast.error('Could not add a child row.')
  }
}

async function saveChild(child) {
  if (!child.full_name?.trim()) return

  try {
    await pdsApi.updateItem('children', child.id, {
      full_name: child.full_name,
      date_of_birth: child.date_of_birth || null,
    })
    emit('changed')
  } catch {
    toast.error('Could not save that child.')
  }
}

async function addReference() {
  try {
    const { data } = await pdsApi.addItem('references', { name: '' })
    references.value.push(data)
  } catch {
    toast.error('Could not add a reference.')
  }
}

async function saveReference(reference) {
  try {
    await pdsApi.updateItem('references', reference.id, {
      name: reference.name || '',
      office_residential_address: reference.office_residential_address || null,
      contact_no_or_email: reference.contact_no_or_email || null,
    })
    emit('changed')
  } catch {
    toast.error('Could not save that reference.')
  }
}

/**
 * Templates auto-unwrap refs, so the list is resolved here by name rather than
 * being passed in — a passed-in ref would arrive as a plain array.
 */
async function removeItem(section, item) {
  if (! await confirm('Remove this entry?')) return

  const list = section === 'children' ? children : references

  try {
    await pdsApi.deleteItem(section, item.id)
    list.value = list.value.filter(row => row.id !== item.id)
    emit('changed')
  } catch {
    toast.error('Could not remove that entry.')
  }
}

function copyResidentialAddress() {
  addressFields.forEach(({ key }) => {
    props.personal[`perm_${key}`] = props.personal[`res_${key}`] ?? ''
  })
}

onMounted(load)

defineExpose({ reload: load })
</script>
