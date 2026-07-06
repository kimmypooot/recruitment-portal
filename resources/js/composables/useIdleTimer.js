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
    localStorage.removeItem('auth_remember')
    router.visit('/login')
  }

  // "Remember me" and Google OAuth logins get a long-lived (30-day) token —
  // forcing them out after 30 idle minutes anyway defeats the point of
  // "remember me". Skip the idle-driven logout for those sessions.
  function isRememberedSession() {
    return localStorage.getItem('auth_remember') === '1'
  }

  function resetTimer() {
    clearAll()
    if (isRememberedSession()) return
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
