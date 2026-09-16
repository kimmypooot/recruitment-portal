<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-7">
    <button type="button" @click="expanded = !expanded"
      class="w-full flex items-center gap-3 px-4 sm:px-6 py-4 text-left hover:bg-gray-50/60 transition-colors">
      <span class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center"
        :class="canGenerate ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600'">
        <svg v-if="canGenerate" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
      </span>

      <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-gray-900">
          Personal Data Sheet readiness
          <span class="font-normal text-gray-400">— CS Form No. 212</span>
        </p>
        <p class="mt-0.5 text-xs" :class="canGenerate ? 'text-green-600' : 'text-amber-600'">
          <template v-if="loading">Checking your PDS…</template>
          <template v-else-if="canGenerate">All required sections are complete — ready to generate on the Documents tab.</template>
          <template v-else>{{ blockingSections.length }} required section{{ blockingSections.length === 1 ? '' : 's' }} still need{{ blockingSections.length === 1 ? 's' : '' }} attention.</template>
        </p>
      </div>

      <span class="hidden sm:inline text-xs font-medium text-gray-400">{{ completeCount }} of {{ totalCount }} sections</span>
      <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="expanded ? 'rotate-180' : ''"
        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <div v-show="expanded && !loading" class="border-t border-gray-100 px-4 sm:px-6 py-4">
      <p class="text-xs text-gray-500 mb-3">
        This is a separate, stricter checklist from the profile-completion bar above — it tracks everything
        the printed CS Form 212 needs, including sections that live on the "PDS Details" tab.
      </p>

      <ul class="space-y-2">
        <li v-for="section in orderedSections" :key="section.key"
          class="flex items-start gap-3 rounded-lg px-3 py-2.5"
          :class="rowClass(section)">
          <span class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center"
            :class="badgeClass(section)">
            <svg v-if="section.complete" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span v-else class="text-[10px] font-bold leading-none">!</span>
          </span>

          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-sm font-medium text-gray-800">{{ shortLabel(section.key) }}</span>
              <span v-if="section.optional" class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Optional</span>
            </div>
            <p v-if="!section.complete" class="mt-0.5 text-xs" :class="section.optional ? 'text-gray-400' : 'text-amber-700'">
              {{ describeMissing(section.key, section) }}
            </p>
          </div>

          <button v-if="!section.complete" type="button" @click="$emit('go-to', targetForSection(section.key, section.missing))"
            class="flex-shrink-0 text-xs font-semibold text-primary hover:underline whitespace-nowrap">
            Complete now
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import {
  orderedSectionEntries, SECTION_SHORT_LABELS, describeMissing, targetForSection,
  SECTION_LABELS,
} from '@/utils/pdsSections'

const props = defineProps({
  sections: { type: Object, default: () => ({}) },
  canGenerate: { type: Boolean, default: false },
  loading: { type: Boolean, default: true },
})

defineEmits(['go-to'])

// Collapsed by default once the PDS is ready, so a returning applicant with a
// finished form isn't greeted by a wall of checkmarks; open automatically the
// first time something needs attention.
const expanded = ref(false)
watch(() => props.loading, (loading) => {
  if (!loading && !props.canGenerate) expanded.value = true
}, { immediate: true })

const orderedSections = computed(() => orderedSectionEntries(props.sections))
const totalCount    = computed(() => Object.keys(SECTION_LABELS).length)
const completeCount = computed(() => orderedSections.value.filter(s => s.complete).length)
const blockingSections = computed(() => orderedSections.value.filter(s => !s.complete && !s.optional))

function shortLabel(key) {
  return SECTION_SHORT_LABELS[key] ?? key
}

function rowClass(section) {
  if (section.complete) return 'bg-green-50/60'
  if (section.optional) return 'bg-gray-50'
  return 'bg-amber-50'
}

function badgeClass(section) {
  if (section.complete) return 'bg-green-100 text-green-700'
  if (section.optional) return 'bg-gray-200 text-gray-500'
  return 'bg-amber-100 text-amber-700'
}
</script>
