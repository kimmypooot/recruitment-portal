<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="$emit('close')"></div>

      <!-- Modal shell: fixed height so tabs scroll, not the modal itself -->
      <form @submit.prevent="submitVacancy"
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col"
        style="max-height: 88vh;">

        <!-- ── Header ── -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
          <div>
            <h3 class="text-base font-semibold text-gray-900">
              {{ isEditing ? 'Edit Vacancy' : 'New Vacancy' }}
            </h3>
            <p v-if="form.position_title" class="text-xs text-gray-400 mt-0.5 truncate max-w-sm">
              {{ form.position_title }}
            </p>
          </div>
          <button type="button" @click="$emit('close')" aria-label="Close modal"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors flex-shrink-0">
            <Icon name="xmark" size="5" />
          </button>
        </div>

        <!-- ── Tab bar ── -->
        <div class="flex border-b border-gray-100 px-6 flex-shrink-0 bg-gray-50/50">
          <button v-for="tab in activeTabs" :key="tab.id" type="button" @click="modalTab = tab.id"
            class="relative px-4 py-3 text-sm font-medium transition-colors -mb-px"
            :class="modalTab === tab.id
              ? 'text-primary border-b-2 border-primary'
              : 'text-gray-500 hover:text-gray-800'">
            <span class="flex items-center gap-1.5">
              {{ tab.label }}
              <span v-if="tab.id === 'competencies' && draftAssignments.length"
                class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-primary text-white text-[9px] font-bold">
                {{ draftAssignments.length }}
              </span>
            </span>
          </button>
        </div>

        <!-- ── Tab content (scrollable) ── -->
        <div class="flex-1 overflow-y-auto">

          <!-- Tab 1: Position Info -->
          <div v-if="modalTab === 'position'" class="p-6">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Position Title <span class="text-red-500">*</span></label>
                <input v-model="form.position_title" required type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plantilla Item No. <span class="text-red-500">*</span></label>
                <input v-model="form.plantilla_no" required type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Salary Grade <span class="text-red-500">*</span></label>
                <select v-model="form.salary_grade" required
                  class="w-full px-3 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
                  <option value="">Select SG</option>
                  <option v-for="n in 33" :key="n" :value="n">SG-{{ n }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Salary (₱)</label>
                <input v-model="form.monthly_salary" type="number" min="0" step="0.01"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Position Level <span class="text-red-500">*</span></label>
                <select v-model="form.position_level" required
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none">
                  <option value="" disabled>Select position level</option>
                  <option value="Supervisory">Supervisory</option>
                  <option value="Technical or Non-Supervisory">Technical or Non-Supervisory</option>
                  <option value="Administrative Support">Administrative Support</option>
                  <option value="Skills, Trades and Craft">Skills, Trades and Craft</option>
                </select>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Place of Assignment <span class="text-red-500">*</span></label>
                <select v-model="form.place_of_assignment" required
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
                  <option value="" disabled>Select place of assignment</option>
                  <optgroup label="CSC Field Offices">
                    <option v-for="office in CSC_FIELD_OFFICES" :key="office" :value="office">{{ office }}</option>
                  </optgroup>
                  <optgroup label="Regional Support Units (RSUs)">
                    <option v-for="unit in REGIONAL_SUPPORT_UNITS" :key="unit" :value="unit">{{ unit }}</option>
                  </optgroup>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Application Deadline <span class="text-red-500">*</span></label>
                <input v-model="form.deadline_at" required type="date"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none" />
              </div>
              <div class="flex items-center gap-2.5 pt-7">
                <input v-model="form.is_anticipated_vacancy" id="anticipated" type="checkbox"
                  class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
                <label for="anticipated" class="text-sm text-gray-700 cursor-pointer">Anticipated Vacancy</label>
              </div>
            </div>
          </div>

          <!-- Tab 2: Qualification Standards -->
          <div v-else-if="modalTab === 'qualifications'" class="p-6 space-y-4">
            <p class="text-xs text-gray-400">
              Describe the minimum qualification standards required for this position per CSC guidelines.
            </p>
            <div v-for="field in requirementFields" :key="field.key">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ field.label }} <span class="text-red-500">*</span>
              </label>
              <textarea v-model="form[field.key]" required rows="3"
                :placeholder="field.placeholder"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none resize-none"></textarea>
            </div>
          </div>

          <!-- Tab 3: Competencies (edit only) -->
          <div v-else-if="modalTab === 'competencies'" class="grid grid-cols-2 divide-x divide-gray-100" style="min-height: 400px;">

            <!-- Left: master list -->
            <div class="flex flex-col overflow-hidden" style="max-height: 56vh;">
              <div class="p-3 border-b border-gray-100 flex-shrink-0">
                <div class="relative">
                  <Icon name="search" size="3.5" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400" />
                  <input v-model="compSearch" type="text" placeholder="Search competencies…"
                    class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none" />
                </div>
              </div>
              <div class="p-4 overflow-y-auto flex-1">
              <p class="text-xs text-gray-400 mb-3">Click a competency to toggle it. Assigned ones are highlighted.</p>
              <div v-for="groupName in groupOrder" :key="groupName" class="mb-4"
                v-show="filteredCompetenciesByGroup[groupName]?.length">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ groupName }}</span>
                  <span class="flex-1 h-px bg-gray-100"></span>
                </div>
                <div class="space-y-1">
                  <div v-for="comp in filteredCompetenciesByGroup[groupName]" :key="comp.competency_key"
                    @click="toggleCompetency(comp)"
                    :class="[
                      'flex items-center gap-2.5 px-2.5 py-2 rounded-lg cursor-pointer transition-all text-sm select-none',
                      isAssigned(comp.competency_key)
                        ? 'bg-primary/8 border border-primary/25 text-gray-900 font-medium'
                        : 'hover:bg-gray-50 border border-transparent text-gray-600'
                    ]">
                    <div :class="isAssigned(comp.competency_key) ? 'bg-primary' : 'bg-gray-200'"
                      class="w-4 h-4 rounded flex-shrink-0 flex items-center justify-center transition-colors">
                      <Icon v-if="isAssigned(comp.competency_key)" name="check" size="3" class="text-white" />
                    </div>
                    <span class="truncate leading-tight">{{ comp.competency_name }}</span>
                  </div>
                </div>
              </div>
              <p v-if="compSearch && !Object.values(filteredCompetenciesByGroup).some(g => g.length)"
                class="text-xs text-gray-400 text-center py-6">No competencies match "{{ compSearch }}"</p>
              </div>
            </div>

            <!-- Right: assigned list -->
            <div class="flex flex-col p-5">
              <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-800">
                  Assigned
                  <span class="ml-1 text-xs font-normal text-gray-400">({{ draftAssignments.length }})</span>
                </p>
              </div>

              <div v-if="!draftAssignments.length"
                class="flex-1 flex flex-col items-center justify-center text-center py-10">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                  <Icon name="document" size="5" class="text-gray-300" />
                </div>
                <p class="text-sm text-gray-400 font-medium">None assigned yet</p>
                <p class="text-xs text-gray-300 mt-0.5">Select from the list on the left.</p>
              </div>

              <div v-else class="flex-1 overflow-y-auto space-y-1.5" style="max-height: 50vh;">
                <div v-for="item in draftAssignments" :key="item.competency_key"
                  class="group flex items-center gap-2 px-2.5 py-2 rounded-lg border border-gray-100 bg-gray-50/80 hover:bg-white hover:border-gray-200 transition-colors">
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-800 truncate leading-tight">{{ item.competency_name }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ item.competency_group }}</p>
                  </div>
                  <select v-model="item.level"
                    class="text-xs border border-gray-200 rounded-md px-1.5 pr-7 py-1 bg-white text-gray-700 focus:ring-1 focus:ring-primary focus:outline-none flex-shrink-0">
                    <option :value="1">Basic</option>
                    <option :value="2">Intermediate</option>
                    <option :value="3">Advanced</option>
                    <option :value="4">Superior</option>
                  </select>
                  <button type="button" @click="removeAssignment(item.competency_key)" aria-label="Remove competency"
                    class="p-1 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors flex-shrink-0 opacity-0 group-hover:opacity-100">
                    <Icon name="xmark" size="3.5" />
                  </button>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- ── Footer ── -->
        <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl flex-shrink-0">
          <button type="button" @click="$emit('close')"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
            {{ modalTab === 'competencies' ? 'Close' : 'Cancel' }}
          </button>

          <div class="flex gap-2">
            <button v-if="modalTab !== 'position'" type="button"
              @click="modalTab = modalTab === 'competencies' ? 'qualifications' : 'position'"
              class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
              ← Back
            </button>

            <button v-if="modalTab === 'position'" type="button" @click="modalTab = 'qualifications'"
              class="px-4 py-2 text-sm bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-colors">
              Next: Qualifications →
            </button>

            <template v-else-if="modalTab === 'qualifications'">
              <button type="submit" :disabled="saving"
                class="px-4 py-2 text-sm bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark disabled:opacity-60 transition-colors">
                <span v-if="saving" class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  Saving…
                </span>
                <span v-else>{{ isEditing ? 'Save Changes' : 'Create Vacancy' }}</span>
              </button>
            </template>

            <button v-else-if="modalTab === 'competencies'" type="button"
              @click="saveCompetencies" :disabled="compSaving"
              class="px-4 py-2 text-sm bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark disabled:opacity-60 transition-colors">
              <span v-if="compSaving" class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Saving…
              </span>
              <span v-else>Save Competencies</span>
            </button>
          </div>
        </div>

      </form>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import axios from 'axios'
import Icon from '@/Components/UI/Icon.vue'
import { useToast } from '@/composables/useToast'
import { CSC_FIELD_OFFICES, REGIONAL_SUPPORT_UNITS } from '@/constants/officesOfAssignment'

const props = defineProps({
  open: { type: Boolean, default: false },
  vacancy: { type: Object, default: null },
})
const emit = defineEmits(['close', 'saved'])

const toast = useToast()

const isEditing = computed(() => !!props.vacancy)
const saving    = ref(false)
const modalTab  = ref('position')

const blankForm = {
  position_title: '', plantilla_no: '', salary_grade: '',
  monthly_salary: '', position_level: '', is_anticipated_vacancy: false,
  place_of_assignment: '', deadline_at: '',
  education_req: '', experience_req: '', training_req: '', eligibility_req: '',
}
const form = reactive({ ...blankForm })

const requirementFields = [
  { key: 'education_req',   label: 'Education',   placeholder: 'e.g. Bachelor\'s Degree in any field' },
  { key: 'experience_req',  label: 'Experience',  placeholder: 'e.g. 1 year of relevant experience' },
  { key: 'training_req',    label: 'Training',    placeholder: 'e.g. 4 hours of relevant training' },
  { key: 'eligibility_req', label: 'Eligibility', placeholder: 'e.g. Career Service (Professional) / Second Level Eligibility' },
]

const activeTabs = computed(() => {
  const base = [
    { id: 'position',       label: 'Position Info' },
    { id: 'qualifications', label: 'Qualification Standards' },
  ]
  if (isEditing.value) base.push({ id: 'competencies', label: 'Competencies' })
  return base
})

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

// ── Competency state ─────────────────────────────────────────────────────────
const groupOrder       = ['Core', 'Organizational', 'Leadership', 'Technical']
const allCompetencies  = ref([])
const draftAssignments = ref([])
const compSaving       = ref(false)
const compSearch       = ref('')

const competenciesByGroup = computed(() => {
  const map = {}
  for (const g of groupOrder) {
    map[g] = allCompetencies.value.filter(c => c.competency_group === g)
  }
  return map
})

const filteredCompetenciesByGroup = computed(() => {
  const q = compSearch.value.toLowerCase().trim()
  if (!q) return competenciesByGroup.value
  const map = {}
  for (const g of groupOrder) {
    map[g] = (competenciesByGroup.value[g] ?? []).filter(c =>
      c.competency_name.toLowerCase().includes(q)
    )
  }
  return map
})

function isAssigned(key) {
  return draftAssignments.value.some(d => d.competency_key === key)
}

function toggleCompetency(comp) {
  if (isAssigned(comp.competency_key)) {
    removeAssignment(comp.competency_key)
  } else {
    draftAssignments.value.push({
      competency_key:   comp.competency_key,
      competency_name:  comp.competency_name,
      competency_group: comp.competency_group,
      level:            1,
    })
  }
}

function removeAssignment(key) {
  draftAssignments.value = draftAssignments.value.filter(d => d.competency_key !== key)
}

async function loadAllCompetencies() {
  if (allCompetencies.value.length) return
  try {
    const { data } = await axios.get('/api/competencies', { headers: authHeaders() })
    allCompetencies.value = data.data ?? []
  } catch {
    allCompetencies.value = []
  }
}

async function loadVacancyCompetencies(vacancyId) {
  draftAssignments.value = []
  compSearch.value = ''
  try {
    const { data } = await axios.get(`/api/admin/competencies/vacancy/${vacancyId}`, { headers: authHeaders() })
    draftAssignments.value = (data.data ?? []).map(vc => ({
      competency_key:   vc.competency_key,
      competency_name:  vc.competency_name,
      competency_group: vc.competency_group,
      level:            vc.competency_level,
    }))
  } catch (e) {
    console.error('Failed to load competencies', e)
  }
}

async function saveCompetencies() {
  compSaving.value = true
  try {
    await axios.post(`/api/admin/competencies/vacancy/${props.vacancy.id}/sync`, {
      competencies: draftAssignments.value.map(d => ({
        competency_key: d.competency_key,
        level: d.level,
      })),
    }, { headers: authHeaders() })
    toast.success('Competencies saved.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to save competencies.')
  } finally {
    compSaving.value = false
  }
}

// ── Open / populate ────────────────────────────────────────────────────────
watch(() => props.open, async (isOpen) => {
  if (!isOpen) return
  modalTab.value = 'position'
  await loadAllCompetencies()

  if (props.vacancy) {
    Object.assign(form, {
      position_title:         props.vacancy.position_title ?? '',
      plantilla_no:           props.vacancy.plantilla_no ?? '',
      salary_grade:           props.vacancy.salary_grade ?? '',
      monthly_salary:         props.vacancy.monthly_salary ?? '',
      position_level:         props.vacancy.position_level ?? '',
      is_anticipated_vacancy: props.vacancy.is_anticipated_vacancy ?? false,
      place_of_assignment:    props.vacancy.place_of_assignment ?? '',
      deadline_at:            props.vacancy.deadline_at ? props.vacancy.deadline_at.slice(0, 10) : '',
      education_req:          props.vacancy.education_req ?? '',
      experience_req:         props.vacancy.experience_req ?? '',
      training_req:           props.vacancy.training_req ?? '',
      eligibility_req:        props.vacancy.eligibility_req ?? '',
    })
    await loadVacancyCompetencies(props.vacancy.id)
  } else {
    Object.assign(form, { ...blankForm })
  }
})

async function submitVacancy() {
  saving.value = true
  try {
    if (isEditing.value) {
      await axios.put(`/api/vacancies/${props.vacancy.id}`, form, { headers: authHeaders() })
      toast.success('Vacancy updated successfully.')
    } else {
      await axios.post('/api/vacancies', form, { headers: authHeaders() })
      toast.success('Vacancy created successfully.')
    }
    emit('saved')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to save vacancy.')
  } finally {
    saving.value = false
  }
}
</script>
