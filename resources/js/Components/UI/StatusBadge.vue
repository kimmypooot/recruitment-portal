<template>
  <span :class="badgeClass" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, default: 'draft' }
})

const map = {
  // Vacancy
  draft:          { label: 'Draft',          cls: 'bg-gray-100 text-gray-600' },
  published:      { label: 'Published',      cls: 'bg-green-100 text-green-700' },
  closed:         { label: 'Closed',         cls: 'bg-gray-100 text-gray-500' },
  filled:         { label: 'Filled',         cls: 'bg-teal-100 text-teal-700' },
  archived:       { label: 'Archived',       cls: 'bg-yellow-100 text-yellow-700' },
  // Application
  submitted:      { label: 'Submitted',      cls: 'bg-[#2a338f]/10 text-[#2a338f]' },
  under_review:   { label: 'Under Review',   cls: 'bg-purple-100 text-purple-700' },
  screened:       { label: 'Screened',       cls: 'bg-sky-100 text-sky-700' },
  qualified:      { label: 'Qualified',      cls: 'bg-teal-100 text-teal-700' },
  disqualified:   { label: 'Disqualified',   cls: 'bg-red-100 text-red-700' },
  exam_scheduled: { label: 'Exam Scheduled', cls: 'bg-orange-100 text-orange-700' },
  shortlisted:    { label: 'Shortlisted',    cls: 'bg-indigo-100 text-indigo-700' },
  for_interview:  { label: 'For Interview',  cls: 'bg-violet-100 text-violet-700' },
  recommended:    { label: 'Recommended',    cls: 'bg-lime-100 text-lime-700' },
  appointed:      { label: 'Appointed',      cls: 'bg-green-100 text-green-800' },
  completed:      { label: 'Completed',      cls: 'bg-green-100 text-green-700' },
  withdrawn:      { label: 'Withdrawn',      cls: 'bg-gray-200 text-gray-500' },
  interviewed:    { label: 'Interviewed',    cls: 'bg-cyan-100 text-cyan-700' },
}

const entry     = computed(() => map[props.status] ?? { label: props.status, cls: 'bg-gray-100 text-gray-600' })
const label     = computed(() => entry.value.label)
const badgeClass = computed(() => entry.value.cls)
</script>
