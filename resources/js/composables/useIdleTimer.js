import { ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'

const IDLE_TIMEOUT = 30 * 60 * 1000
const WARNING_BEFORE = 5 * 60 * 1000

export function useIdleTimer(timeout = IDLE_TIMEOUT) {
  const isIdle = ref(false)
  const { warning } = useToast()
  let timer = null
  let warningTimer = null

  function clearAll() {
    if (timer) clearTimeout(timer)
    if (warningTimer) clearTimeout(warningTimer)
    timer = null
    warningTimer = null
  }

  function logout() {
    isIdle.value = true
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    localStorage.removeItem('auth_token_created_at')
    router.visit('/login')
  }

  function resetTimer() {
    clearAll()
    warningTimer = setTimeout(() => {
      warning('Your session will expire due to inactivity. Move your mouse or press a key to stay signed in.')
    }, timeout - WARNING_BEFORE)
    timer = setTimeout(logout, timeout)
  }

  const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart']

  function start() {
    resetTimer()
    events.forEach(event => window.addEventListener(event, resetTimer))
  }

  function stop() {
    clearAll()
    events.forEach(event => window.removeEventListener(event, resetTimer))
  }

  onMounted(() => start())
  onBeforeUnmount(() => stop())

  return { isIdle }
}
