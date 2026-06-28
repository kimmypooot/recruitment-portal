<template>
  <footer class="border-t border-gray-200 bg-white px-4 sm:px-6 py-3 flex-shrink-0">
    <div class="flex items-center justify-between gap-2 text-xs text-gray-400 flex-wrap">
      <span>&copy; {{ year }} Civil Service Commission Regional Office VIII. All rights reserved</span>
      <span class="hidden sm:inline">v2.5.0</span>
    </div>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear()
</script>
