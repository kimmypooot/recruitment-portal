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
  draft:        { label: 'Draft',        cls: 'bg-gray-100 text-gray-600' },
  published:    { label: 'Published',    cls: 'bg-green-100 text-green-700' },
  archived:     { label: 'Archived',     cls: 'bg-yellow-100 text-yellow-700' },
  submitted:    { label: 'Submitted',    cls: 'bg-blue-100 text-blue-700' },
  under_review: { label: 'Under Review', cls: 'bg-purple-100 text-purple-700' },
  qualified:    { label: 'Qualified',    cls: 'bg-teal-100 text-teal-700' },
  disqualified: { label: 'Disqualified', cls: 'bg-red-100 text-red-700' },
  shortlisted:  { label: 'Shortlisted',  cls: 'bg-indigo-100 text-indigo-700' },
  appointed:    { label: 'Appointed',    cls: 'bg-green-100 text-green-800' },
}

const entry     = computed(() => map[props.status] ?? { label: props.status, cls: 'bg-gray-100 text-gray-600' })
const label     = computed(() => entry.value.label)
const badgeClass = computed(() => entry.value.cls)
</script>
