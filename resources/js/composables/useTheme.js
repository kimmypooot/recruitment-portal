import { ref, computed } from 'vue'

// 'light' | 'dark' | 'system'. Stored per browser; the inline script in app.blade.php applies it before first paint.
const KEY = 'theme'
const mode = ref('system')
const systemDark = ref(false)
let ready = false

function readStored() {
  try {
    const v = localStorage.getItem(KEY)
    return v === 'light' || v === 'dark' ? v : 'system'
  } catch {
    return 'system'
  }
}

const isDark = computed(() => mode.value === 'dark' || (mode.value === 'system' && systemDark.value))

function apply() {
  const root = document.documentElement
  root.classList.toggle('dark', isDark.value)
  root.dataset.theme = isDark.value ? 'dark' : 'light'
}

function init() {
  if (ready || typeof window === 'undefined') return
  ready = true
  mode.value = readStored()
  const mq = window.matchMedia('(prefers-color-scheme: dark)')
  systemDark.value = mq.matches
  mq.addEventListener?.('change', (e) => { systemDark.value = e.matches; apply() })
  apply()
}

function setMode(next) {
  mode.value = next
  try {
    next === 'system' ? localStorage.removeItem(KEY) : localStorage.setItem(KEY, next)
  } catch { /* private mode: still works for this session */ }
  apply()
}

// Light → dark → system → light …
function cycle() {
  setMode(mode.value === 'light' ? 'dark' : mode.value === 'dark' ? 'system' : 'light')
}

export function useTheme() {
  init()
  return { mode, isDark, setMode, cycle }
}
