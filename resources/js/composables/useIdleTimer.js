import { ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

const IDLE_TIMEOUT = 30 * 60 * 1000 // 30 minutes

export function useIdleTimer(timeout = IDLE_TIMEOUT) {
  const isIdle = ref(false)
  let timer = null

  function resetTimer() {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
      isIdle.value = true
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      router.visit('/login')
    }, timeout)
  }

  const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart']

  function start() {
    resetTimer()
    events.forEach(event => window.addEventListener(event, resetTimer))
  }

  function stop() {
    if (timer) clearTimeout(timer)
    events.forEach(event => window.removeEventListener(event, resetTimer))
  }

  onMounted(() => start())
  onBeforeUnmount(() => stop())

  return { isIdle }
}
