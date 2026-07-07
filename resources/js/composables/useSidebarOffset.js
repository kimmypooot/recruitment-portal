import { ref, computed, inject, onMounted, onBeforeUnmount } from 'vue'

const SIDEBAR_WIDTH = 256

export function useSidebarOffset() {
  const sidebarCollapsed = inject('sidebarCollapsed', ref(true))
  const isLg = ref(window.innerWidth >= 1024)

  const sidebarOffset = computed(() => {
    if (isLg.value && !sidebarCollapsed.value) return SIDEBAR_WIDTH
    return 0
  })

  function updateViewport() { isLg.value = window.innerWidth >= 1024 }
  onMounted(() => window.addEventListener('resize', updateViewport))
  onBeforeUnmount(() => window.removeEventListener('resize', updateViewport))

  return { sidebarOffset }
}
