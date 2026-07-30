import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('auth_token') ?? '')
  const user = ref(JSON.parse(localStorage.getItem('auth_user') ?? 'null'))
  const profileComplete = ref(localStorage.getItem('profile_complete') === 'true')
  const remember = ref(localStorage.getItem('auth_remember') === '1')

  const isLoggedIn = computed(() => !!token.value)
  const role = computed(() => user.value?.role ?? null)
  const isApplicant = computed(() => role.value === 'applicant')
  const isAdmin = computed(() => role.value === 'admin')
  const isHrmpsb = computed(() => role.value === 'hrmpsb')
  const fullName = computed(() => user.value?.full_name ?? '')
  const email = computed(() => user.value?.email ?? '')
  const userInitial = computed(() => (fullName.value || 'U')[0].toUpperCase())

  function hasRole(...roles) {
    return roles.includes(role.value)
  }

  function setAuth(authToken, authUser, authRemember = false) {
    token.value = authToken
    user.value = authUser
    remember.value = authRemember
    profileComplete.value = false
    localStorage.setItem('auth_token', authToken)
    localStorage.setItem('auth_user', JSON.stringify(authUser))
    localStorage.setItem('auth_token_created_at', String(Date.now()))
    localStorage.setItem('auth_remember', authRemember ? '1' : '0')
    localStorage.setItem('profile_complete', 'false')
  }

  function updateUser(partial) {
    if (!user.value) return
    user.value = { ...user.value, ...partial }
    localStorage.setItem('auth_user', JSON.stringify(user.value))
    window.dispatchEvent(new CustomEvent('auth-user-updated'))
  }

  function setProfileComplete(val) {
    profileComplete.value = val
    localStorage.setItem('profile_complete', String(val))
    window.dispatchEvent(new CustomEvent('auth-user-updated'))
  }

  function refreshFromStorage() {
    token.value = localStorage.getItem('auth_token') ?? ''
    user.value = JSON.parse(localStorage.getItem('auth_user') ?? 'null')
    profileComplete.value = localStorage.getItem('profile_complete') === 'true'
    remember.value = localStorage.getItem('auth_remember') === '1'
  }

  function clear() {
    token.value = ''
    user.value = null
    profileComplete.value = false
    remember.value = false
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    localStorage.removeItem('auth_token_created_at')
    localStorage.removeItem('auth_remember')
    localStorage.removeItem('profile_complete')
    window.dispatchEvent(new CustomEvent('auth-user-updated'))
  }

  return {
    token, user, profileComplete, remember,
    isLoggedIn, role, isApplicant, isAdmin, isHrmpsb,
    fullName, email, userInitial,
    hasRole, setAuth, updateUser, setProfileComplete, refreshFromStorage, clear,
  }
})
