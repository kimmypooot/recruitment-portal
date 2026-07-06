import { watch, onBeforeUnmount } from 'vue'

export function useFocusTrap(showRef, rootRef, { closeOnEscape = true } = {}) {
  let previouslyFocused = null

  function getFocusableElements() {
    if (!rootRef.value) return []
    const selectors = [
      'a[href]', 'button:not([disabled])', 'textarea:not([disabled])',
      'input:not([disabled])', 'select:not([disabled])',
      '[tabindex]:not([tabindex="-1"])',
    ]
    return Array.from(rootRef.value.querySelectorAll(selectors.join(',')))
  }

  function trapFocus(e) {
    const elements = getFocusableElements()
    if (!elements.length) return
    const first = elements[0]
    const last = elements[elements.length - 1]
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault()
      last.focus()
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault()
      first.focus()
    }
  }

  function handleKeydown(e) {
    if (closeOnEscape && e.key === 'Escape' && showRef.value) {
      showRef.value = false
    }
    if (e.key === 'Tab') {
      trapFocus(e)
    }
  }

  function open() {
    previouslyFocused = document.activeElement
    document.addEventListener('keydown', handleKeydown)
    requestAnimationFrame(() => {
      const elements = getFocusableElements()
      if (elements.length) elements[0].focus()
    })
  }

  function close() {
    document.removeEventListener('keydown', handleKeydown)
    if (previouslyFocused && previouslyFocused.focus) {
      previouslyFocused.focus()
    }
    previouslyFocused = null
  }

  // Reacts to `visible` toggling on an already-mounted modal (e.g. a
  // component that stays mounted and flips a ref) as well as the more common
  // case of a `v-if`-gated modal that mounts already-open.
  watch(showRef, (isOpen, wasOpen) => {
    if (isOpen && !wasOpen) open()
    else if (!isOpen && wasOpen) close()
  }, { immediate: true })

  onBeforeUnmount(() => {
    if (showRef.value) close()
  })

  return { open, close }
}
