<template>
  <div
    v-if="lastPage > 1"
    class="mt-10 flex items-center justify-between flex-wrap gap-3"
  >
    <p class="text-sm text-gray-500">
      Showing
      <span class="font-medium text-gray-700">{{ from }}</span>–<span class="font-medium text-gray-700">{{ to }}</span>
      of <span class="font-medium text-gray-700">{{ total }}</span>
    </p>

    <div class="flex items-center gap-1">
      <button
        :disabled="currentPage === 1"
        @click="$emit('page-change', currentPage - 1)"
        class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        aria-label="Previous page"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
      </button>

      <template v-for="page in visiblePages" :key="page">
        <span v-if="page === '...'" class="px-2 text-gray-400 text-sm" aria-hidden="true">…</span>
        <button
          v-else
          @click="$emit('page-change', page)"
          :aria-current="page === currentPage ? 'page' : undefined"
          :aria-label="`Page ${page}`"
          :class="[
            'w-9 h-9 rounded-lg text-sm font-medium transition-colors',
            page === currentPage
              ? 'bg-primary text-white shadow-sm'
              : 'text-gray-700 hover:bg-gray-100'
          ]"
        >
          {{ page }}
        </button>
      </template>

      <button
        :disabled="currentPage === lastPage"
        @click="$emit('page-change', currentPage + 1)"
        class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        aria-label="Next page"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
  total: { type: Number, default: 0 },
  from: { type: Number, default: 0 },
  to: { type: Number, default: 0 },
})

defineEmits(['page-change'])

const visiblePages = computed(() => {
  const { currentPage: cur, lastPage: last } = props
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
  if (cur <= 4)  return [1, 2, 3, 4, 5, '...', last]
  if (cur >= last - 3) return [1, '...', last - 4, last - 3, last - 2, last - 1, last]
  return [1, '...', cur - 1, cur, cur + 1, '...', last]
})
</script>
