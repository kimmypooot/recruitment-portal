<template>
  <!-- Loading skeleton -->
  <div v-if="loading" class="h-40 bg-white rounded-2xl border border-gray-200 animate-pulse" />

  <!-- Banner -->
  <div v-else-if="vacancy" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <!-- Accent bar -->
    <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #2a338f 0%, #2980b9 100%)" />

    <div class="px-6 py-5">
      <!-- Stage badge + vacancy status -->
      <div class="flex items-start justify-between gap-3 flex-wrap">
        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
          <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Recruitment Stage {{ stage }} — {{ stageLabel }}
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0"
          :class="statusClass">
          <svg class="w-2 h-2" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
          {{ capitalize(vacancy.status) }}
        </span>
      </div>

      <!-- Position title -->
      <h1 class="mt-3 text-xl font-bold text-gray-900 leading-snug">{{ vacancy.position_title }}</h1>

      <!-- Meta chips -->
      <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Plantilla Item No.</p>
          <p class="mt-1 text-sm font-semibold text-gray-800 font-mono truncate">{{ vacancy.plantilla_no ?? '—' }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Salary Grade</p>
          <p class="mt-1 text-sm font-semibold text-gray-800">SG-{{ vacancy.salary_grade }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Publication Date</p>
          <p class="mt-1 text-sm font-semibold text-gray-800">{{ formatDate(vacancy.published_at) }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Place of Assignment</p>
          <p class="mt-1 text-sm font-semibold text-gray-800 truncate">{{ vacancy.place_of_assignment ?? '—' }}</p>
        </div>
      </div>

      <!-- Stage-specific content (passing threshold, notes, etc.) -->
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  vacancy:    { type: Object,  default: null },
  stage:      { type: Number,  required: true },
  stageLabel: { type: String,  required: true },
  loading:    { type: Boolean, default: false },
})

const statusClass = computed(() => {
  const map = {
    published: 'bg-green-50 text-green-700 border border-green-200',
    closed:    'bg-gray-100 text-gray-600 border border-gray-200',
    filled:    'bg-blue-50 text-blue-700 border border-blue-200',
    draft:     'bg-amber-50 text-amber-700 border border-amber-200',
  }
  return map[props.vacancy?.status] ?? 'bg-gray-100 text-gray-500 border border-gray-200'
})

function capitalize(str) {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' })
}
</script>
