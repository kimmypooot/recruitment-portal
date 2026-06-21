<template>
  <article class="bg-white rounded-xl border border-gray-200 hover:border-[#2a338f]/40 hover:shadow-md transition-all duration-200 flex flex-col">

    <!-- Card header -->
    <div class="p-5 flex-1">
      <!-- SG badge + status -->
      <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-[#2a338f] text-white">
            SG-{{ vacancy.salary_grade }}
          </span>
          <StatusBadge :status="vacancy.status" />
        </div>
        <!-- Deadline countdown -->
        <span v-if="daysRemaining !== null && daysRemaining <= 5 && daysRemaining >= 0"
          :class="daysRemaining <= 2 ? 'text-red-600' : 'text-amber-600'"
          class="inline-flex items-center gap-1 text-xs font-semibold">
          <span class="w-1.5 h-1.5 rounded-full animate-pulse"
            :class="daysRemaining <= 2 ? 'bg-red-500' : 'bg-amber-400'"></span>
          {{ daysRemaining === 0 ? 'Closes today' : `${daysRemaining}d left` }}
        </span>
      </div>

      <!-- Position title -->
      <h3 class="text-base font-semibold text-gray-900 leading-snug mb-1 line-clamp-2">
        {{ vacancy.position_title }}
      </h3>

      <!-- Office / assignment -->
      <p class="text-sm text-gray-500 mb-4 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        {{ vacancy.place_of_assignment }}
      </p>

      <!-- Key requirements (collapsed) -->
      <dl class="space-y-1.5">
        <div class="flex gap-2 text-xs">
          <dt class="w-24 flex-shrink-0 text-gray-400 font-medium">Education</dt>
          <dd class="text-gray-600 line-clamp-1">{{ vacancy.education_req }}</dd>
        </div>
        <div class="flex gap-2 text-xs">
          <dt class="w-24 flex-shrink-0 text-gray-400 font-medium">Experience</dt>
          <dd class="text-gray-600 line-clamp-1">{{ vacancy.experience_req }}</dd>
        </div>
        <div class="flex gap-2 text-xs">
          <dt class="w-24 flex-shrink-0 text-gray-400 font-medium">Eligibility</dt>
          <dd class="text-gray-600 line-clamp-1">{{ vacancy.eligibility_req }}</dd>
        </div>
      </dl>
    </div>

    <!-- Card footer -->
    <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between gap-3 bg-gray-50 rounded-b-xl">
      <div>
        <p class="text-xs text-gray-400">Deadline</p>
        <p class="text-xs font-semibold" :class="isUrgent ? 'text-red-600' : 'text-gray-700'">
          {{ formatDate(vacancy.deadline_at) }}
        </p>
      </div>
      <Link
        :href="`/vacancies/${vacancy.id}/apply`"
        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#2a338f] hover:bg-[#1e2570] rounded-lg transition-colors shadow-sm">
        View & Apply
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
        </svg>
      </Link>
    </div>

  </article>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

const props = defineProps({
  vacancy: { type: Object, required: true }
})

const daysRemaining = computed(() => {
  if (!props.vacancy.deadline_at) return null
  const ms = new Date(props.vacancy.deadline_at) - new Date()
  return ms < 0 ? -1 : Math.ceil(ms / (1000 * 60 * 60 * 24))
})

const isUrgent = computed(() => daysRemaining.value !== null && daysRemaining.value >= 0 && daysRemaining.value <= 5)

function formatDate(dateStr) {
  if (!dateStr) return 'No deadline set'
  return new Date(dateStr).toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric'
  })
}
</script>