import { defineStore } from 'pinia'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const page = usePage()

  const user = computed(() => page.props.auth?.user ?? null)
  const role = computed(() => user.value?.role ?? null)

  const isLoggedIn = computed(() => !!user.value)
  const isApplicant = computed(() => role.value === 'applicant')
  const isHr = computed(() => ['hr-officer', 'hr-manager', 'admin'].includes(role.value))
  const isHrManager = computed(() => ['hr-manager', 'admin'].includes(role.value))
  const isAdmin = computed(() => role.value === 'admin')
  const isHrmpsb = computed(() => [
    'hrmpsb-member', 'hrmpsb-secretariat', 'admin', 'hr-manager',
  ].includes(role.value))
  const isSecretariat = computed(() => role.value === 'hrmpsb-secretariat')
  const isAppointingAuthority = computed(() => role.value === 'appointing-authority')

  function hasRole(...roles) {
    return roles.includes(role.value)
  }

  // Token is stored in localStorage for API calls (Sanctum token auth)
  const token = computed(() => localStorage.getItem('auth_token'))

  return {
    user, role, token, isLoggedIn,
    isApplicant, isHr, isHrManager, isAdmin,
    isHrmpsb, isSecretariat, isAppointingAuthority,
    hasRole,
  }
})
