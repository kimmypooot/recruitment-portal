<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
      <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" :d="icon"/>
      </svg>
    </div>
    <p class="text-sm font-semibold text-gray-900 mb-1">{{ title }}</p>
    <p class="text-xs text-gray-400 max-w-sm">{{ description }}</p>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'No data found' },
  description: { type: String, default: '' },
  icon: { type: String, default: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
})
</script>
