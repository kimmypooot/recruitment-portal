<template>
  <footer class="fixed bottom-0 left-0 right-0 z-30 border-t border-gray-200 bg-white px-4 sm:px-6 py-3"
    :class="sidebarCollapsed ? 'lg:left-0' : 'lg:left-64'">
    <div class="flex items-center justify-between gap-2 text-xs text-gray-400 flex-wrap">
      <span>&copy; {{ year }} Civil Service Commission Regional Office VIII. All rights reserved</span>
      <span>v1.0</span>
    </div>
  </footer>
</template>

<script setup>
defineProps({ sidebarCollapsed: Boolean })
const year = new Date().getFullYear()
</script>
