<template>
  <button type="button" aria-label="Search pages and vacancies (Ctrl K)"
    class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors p-2 sm:py-1.5 sm:pl-3 sm:pr-2"
    @click="openPalette">
    <Icon name="search" size="4" class="flex-shrink-0" />
    <span class="hidden sm:inline text-sm pr-6">Search…</span>
    <kbd class="hidden sm:inline text-[10px] font-semibold border border-gray-200 bg-white rounded px-1.5 py-0.5">{{ isMac ? '⌘' : 'Ctrl' }} K</kbd>
  </button>
</template>

<script setup>
import Icon from '@/Components/UI/Icon.vue'

const isMac = typeof navigator !== 'undefined' && /Mac|iPhone|iPad/.test(navigator.platform)
const openPalette = () => window.dispatchEvent(new Event('open-command-palette'))
</script>
