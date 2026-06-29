import { onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'

const TOKEN_DURATION = 2 * 60 * 60 * 1000
const WARNING_BEFORE = 10 * 60 * 1000
const CHECK_INTERVAL = 30 * 1000

export function useSessionExpiry() {
  const { warning } = useToast()
  let interval = null
  let warned = false

  function getTokenCreatedAt() {
    const stored = localStorage.getItem('auth_token_created_at')
    if (stored) return parseInt(stored, 10)
    if (localStorage.getItem('auth_token')) {
      const now = Date.now()
      localStorage.setItem('auth_token_created_at', String(now))
      return now
    }
    return null
  }

  function checkExpiry() {
    const createdAt = getTokenCreatedAt()
    if (!createdAt) return

    const elapsed = Date.now() - createdAt
    const remaining = TOKEN_DURATION - elapsed

    if (remaining <= 0) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      localStorage.removeItem('auth_token_created_at')
      router.visit('/login')
      return
    }

    if (remaining <= WARNING_BEFORE && !warned) {
      warned = true
      warning('Your session will expire in less than 10 minutes. Please save your work.')
    }
  }

  function start() {
    if (getTokenCreatedAt()) {
      checkExpiry()
      interval = setInterval(checkExpiry, CHECK_INTERVAL)
    }
  }

  function stop() {
    if (interval) {
      clearInterval(interval)
      interval = null
    }
  }

  onMounted(() => start())
  onBeforeUnmount(() => stop())

  return { start, stop }
}
