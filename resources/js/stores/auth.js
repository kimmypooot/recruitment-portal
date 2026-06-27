import { defineStore } from 'pinia'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const page = usePage()

  const user = computed(() => page.props.auth?.user ?? null)
  const role = computed(() => user.value?.role ?? null)

  const isLoggedIn   = computed(() => !!user.value)
  const isApplicant  = computed(() => role.value === 'applicant')
  const isAdmin      = computed(() => role.value === 'admin')
  const isHrmpsb    = computed(() => role.value === 'hrmpsb')

  function hasRole(...roles) {
    return roles.includes(role.value)
  }

  const token = computed(() => localStorage.getItem('auth_token'))

  return {
    user, role, token, isLoggedIn,
    isApplicant, isAdmin, isHrmpsb,
    hasRole,
  }
})
