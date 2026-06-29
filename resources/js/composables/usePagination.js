import { computed } from 'vue'

export function usePagination(pagination) {
  const visiblePages = computed(() => {
    const { current_page: cur, last_page: last } = pagination.value
    if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
    if (cur <= 4)  return [1, 2, 3, 4, 5, '...', last]
    if (cur >= last - 3) return [1, '...', last - 4, last - 3, last - 2, last - 1, last]
    return [1, '...', cur - 1, cur, cur + 1, '...', last]
  })

  function goToPage(page, fetchFn) {
    pagination.value.current_page = page
    fetchFn()
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }

  return { visiblePages, goToPage }
}
