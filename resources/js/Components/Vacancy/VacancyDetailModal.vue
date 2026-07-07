<template>
  <Teleport to="body">
    <div ref="modalRef" role="dialog" aria-modal="true" aria-labelledby="vacancy-modal-title"
      class="fixed inset-y-0 z-50 flex items-start justify-center p-0 sm:p-4 overflow-y-auto"
      :class="sidebarOffset ? 'left-0' : 'inset-x-0'"
      :style="sidebarOffset ? { left: sidebarOffset + 'px', width: `calc(100% - ${sidebarOffset}px)` } : {}"
      @keydown.escape="$emit('close')">
      <div class="absolute inset-0 bg-black/60" @click="$emit('close')"></div>

      <div class="relative bg-white rounded-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-4xl my-0 sm:my-6 flex flex-col max-h-full sm:max-h-[90vh]">

        <!-- Header -->
        <div class="p-4 sm:p-6 pb-4 border-b border-gray-100">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
              <span v-if="vacancy.is_anticipated_vacancy"
                class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-100 text-amber-700 mb-1">
                Anticipated Vacancy
              </span>
              <div class="flex items-center gap-2 flex-wrap">
                <h2 id="vacancy-modal-title" class="text-lg sm:text-xl font-bold text-gray-900 break-words">{{ vacancy.position_title }}</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-primary text-white flex-shrink-0">
                  SG-{{ vacancy.salary_grade }}
                </span>
              </div>
              <p class="text-sm text-gray-500 mt-0.5">{{ vacancy.place_of_assignment }}</p>
            </div>
            <button @click="$emit('close')"
              class="flex-shrink-0 p-2 -mt-0.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body (scrollable) -->
        <div class="flex-1 overflow-y-auto">
          <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">

            <!-- Left column: Position Info + Qualification Standards -->
            <div class="p-4 sm:p-6 space-y-6">

              <!-- Position Information -->
              <section>
                <h3 class="text-xs font-semibold text-primary uppercase tracking-wider mb-3">Position Information</h3>
                <dl class="space-y-2.5">
                  <div class="flex gap-3">
                    <dt class="w-28 sm:w-36 flex-shrink-0 text-xs text-gray-400 font-medium pt-0.5">Plantilla Item No.</dt>
                    <dd class="text-sm text-gray-800 break-words min-w-0">{{ vacancy.plantilla_no || '—' }}</dd>
                  </div>
                  <div class="flex gap-3">
                    <dt class="w-28 sm:w-36 flex-shrink-0 text-xs text-gray-400 font-medium pt-0.5">Salary Grade</dt>
                    <dd class="text-sm text-gray-800 break-words min-w-0">SG-{{ vacancy.salary_grade }}</dd>
                  </div>
                  <div v-if="vacancy.monthly_salary" class="flex gap-3">
                    <dt class="w-28 sm:w-36 flex-shrink-0 text-xs text-gray-400 font-medium pt-0.5">Monthly Salary</dt>
                    <dd class="text-sm text-gray-800 break-words min-w-0">₱ {{ formatSalary(vacancy.monthly_salary) }}</dd>
                  </div>
                  <div v-if="vacancy.position_level" class="flex gap-3">
                    <dt class="w-28 sm:w-36 flex-shrink-0 text-xs text-gray-400 font-medium pt-0.5">Position Level</dt>
                    <dd class="text-sm text-gray-800 break-words min-w-0">{{ vacancy.position_level }}</dd>
                  </div>
                  <div class="flex gap-3">
                    <dt class="w-28 sm:w-36 flex-shrink-0 text-xs text-gray-400 font-medium pt-0.5">Place of Assignment</dt>
                    <dd class="text-sm text-gray-800 break-words min-w-0">{{ vacancy.place_of_assignment }}</dd>
                  </div>
                  <div class="flex gap-3">
                    <dt class="w-28 sm:w-36 flex-shrink-0 text-xs text-gray-400 font-medium pt-0.5">Date of Publication</dt>
                    <dd class="text-sm text-gray-800 break-words min-w-0">{{ formatDate(vacancy.published_at) }}</dd>
                  </div>
                  <div class="flex gap-3">
                    <dt class="w-28 sm:w-36 flex-shrink-0 text-xs text-gray-400 font-medium pt-0.5">Application Deadline</dt>
                    <dd class="text-sm font-semibold break-words min-w-0" :class="urgency.level !== 'none' ? 'text-red-600' : 'text-gray-800'">
                      {{ formatDate(vacancy.deadline_at) }}
                      <span v-if="urgency.level !== 'none'"
                        class="ml-1 text-xs font-normal">
                        ({{ daysRemaining === 0 ? 'closes today' : `${daysRemaining}d left` }})
                      </span>
                    </dd>
                  </div>
                </dl>
              </section>

              <!-- Qualification Standards -->
              <section>
                <h3 class="text-xs font-semibold text-primary uppercase tracking-wider mb-3">Qualification Standards</h3>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-xs text-gray-400 font-medium mb-0.5">Education</dt>
                    <dd class="text-sm text-gray-800 break-words">{{ vacancy.education_req || '—' }}</dd>
                  </div>
                  <div>
                    <dt class="text-xs text-gray-400 font-medium mb-0.5">Experience</dt>
                    <dd class="text-sm text-gray-800 break-words">{{ vacancy.experience_req || '—' }}</dd>
                  </div>
                  <div>
                    <dt class="text-xs text-gray-400 font-medium mb-0.5">Training</dt>
                    <dd class="text-sm text-gray-800 break-words">{{ vacancy.training_req || '—' }}</dd>
                  </div>
                  <div>
                    <dt class="text-xs text-gray-400 font-medium mb-0.5">Eligibility</dt>
                    <dd class="text-sm text-gray-800 break-words">{{ vacancy.eligibility_req || '—' }}</dd>
                  </div>
                </dl>
              </section>

            </div>

            <!-- Right column: Competency Requirements -->
            <div class="p-4 sm:p-6">
              <h3 class="text-xs font-semibold text-primary uppercase tracking-wider mb-3">Competency Requirements</h3>

              <div v-if="!vacancy.competencies || vacancy.competencies.length === 0"
                class="text-sm text-gray-400 italic">No competency requirements defined for this position.</div>

              <div v-else class="space-y-4">
                <div v-for="(group, groupName) in groupedCompetencies" :key="groupName">
                  <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ groupName }}</h4>
                  <div class="space-y-2">
                    <div v-for="comp in group" :key="comp.competency_key"
                      class="flex items-start justify-between gap-3 p-2.5 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors group">
                      <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                          <p class="text-sm font-medium text-gray-800 truncate">{{ comp.competency_name }}</p>
                          <div v-if="comp.description" class="relative">
                            <button type="button"
                              @mouseenter="activeTooltip = comp.competency_key"
                              @mouseleave="activeTooltip = null"
                              @click="activeTooltip = activeTooltip === comp.competency_key ? null : comp.competency_key"
                              @focus="activeTooltip = comp.competency_key"
                              @blur="activeTooltip = null"
                              :aria-expanded="activeTooltip === comp.competency_key"
                              :aria-label="`More about ${comp.competency_name}`"
                              class="text-gray-300 hover:text-gray-500 transition-colors">
                              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                              </svg>
                            </button>
                            <div v-if="activeTooltip === comp.competency_key"
                              role="tooltip"
                              class="absolute top-full right-0 mt-1 w-64 bg-gray-900 text-white text-xs rounded-lg p-2.5 z-10 shadow-xl">
                              {{ comp.description }}
                              <div class="absolute bottom-full right-3 w-0 h-0 border-l-4 border-r-4 border-b-4 border-transparent border-b-gray-900"></div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <span :class="levelBadgeClass(comp.competency_level)"
                        class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold">
                        {{ levelLabel(comp.competency_level) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>


            </div>

          </div>
        </div>

        <!-- Footer -->
        <div class="p-4 sm:p-6 pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
          <p v-if="!preview" class="text-xs text-gray-400">
            Make sure you meet the qualifications before applying.
          </p>
          <div v-if="!preview" class="flex gap-3 w-full sm:w-auto">
            <button @click="$emit('close')"
              class="flex-1 sm:flex-none px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
              Cancel
            </button>
            <span v-if="isApplied"
              class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-5 py-2 text-sm font-semibold text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
              Already Applied
            </span>
            <button v-else-if="confirmBeforeApply" @click="showConfirmModal = true"
              class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-5 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-lg transition-colors shadow-sm">
              Proceed to Apply
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
              </svg>
            </button>
            <Link v-else :href="`/vacancies/${vacancy.id}/apply`"
              class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-5 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-lg transition-colors shadow-sm">
              Proceed to Apply
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
              </svg>
            </Link>
          </div>
          <button v-else @click="$emit('close')"
            class="ml-auto px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-dark rounded-lg transition-colors shadow-sm">
            Close
          </button>
        </div>

      </div>
    </div>

    <!-- Confirmation modal -->
    <div v-if="confirmBeforeApply && showConfirmModal" class="fixed inset-y-0 z-[60] flex items-center justify-center p-4"
      :class="sidebarOffset ? 'left-0' : 'inset-x-0'"
      :style="sidebarOffset ? { left: sidebarOffset + 'px', width: `calc(100% - ${sidebarOffset}px)` } : {}">
      <div class="absolute inset-0 bg-black/60" @click="showConfirmModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
        <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
          </svg>
        </div>
        <h3 class="text-base font-bold text-gray-900 mb-1">Before you proceed</h3>
        <p class="text-sm text-gray-500 mb-6">
          Please ensure that the details in your profile are relevant to this position and that all required documents are attached before continuing to the application form.
        </p>
        <div class="flex flex-col gap-2">
          <Link :href="`/vacancies/${vacancy.id}/apply`"
            class="w-full py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg transition-colors">
            Proceed to Apply
          </Link>
          <button @click="showConfirmModal = false"
            class="w-full py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold rounded-lg transition-colors">
            Cancel
          </button>
        </div>
      </div>
    </div>

  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { deadlineUrgency } from '@/utils/dates'
import { useFocusTrap } from '@/composables/useFocusTrap'
import { useSidebarOffset } from '@/composables/useSidebarOffset'

const props = defineProps({
  vacancy:            { type: Object, required: true },
  appliedIds:         { type: Array,  default: () => [] },
  preview:            { type: Boolean, default: false },
  confirmBeforeApply: { type: Boolean, default: false },
})

defineEmits(['close'])

const modalRef = ref(null)
// This component is only ever mounted while the modal should be open (the
// parent gates it with v-if), so the trap is simply "on for this instance".
// Escape is already wired to the existing @keydown.escape → close emit below,
// so let the trap only handle focus, not a second Escape path.
useFocusTrap(ref(true), modalRef, { closeOnEscape: false })

const isApplied = computed(() => props.appliedIds.includes(props.vacancy.id))

const activeTooltip = ref(null)
const showConfirmModal = ref(false)

const { sidebarOffset } = useSidebarOffset()

const groupedCompetencies = computed(() => {
  if (!props.vacancy.competencies?.length) return {}
  const order = ['Core', 'Organizational', 'Leadership', 'Technical']
  const groups = {}
  for (const g of order) {
    const items = props.vacancy.competencies.filter(c => c.competency_group === g)
    if (items.length) groups[g] = items
  }
  return groups
})

const urgency = computed(() => deadlineUrgency(props.vacancy.deadline_at))
const daysRemaining = computed(() => urgency.value.daysRemaining)

function formatDate(dateStr) {
  if (!dateStr) return 'No deadline set'
  return new Date(dateStr).toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric'
  })
}

function formatSalary(amount) {
  return Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })
}

function levelLabel(level) {
  return ['', 'Basic', 'Intermediate', 'Advanced', 'Superior'][level] ?? level
}

function levelBadgeClass(level) {
  return {
    1: 'bg-gray-100 text-gray-600',
    2: 'bg-blue-100 text-blue-700',
    3: 'bg-violet-100 text-violet-700',
    4: 'bg-amber-100 text-amber-700',
  }[level] ?? 'bg-gray-100 text-gray-600'
}
</script>
