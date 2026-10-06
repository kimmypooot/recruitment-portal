<template>
  <div class="relative flex items-center justify-center min-h-screen overflow-hidden"
    style="background: linear-gradient(135deg, #f0eef9 0%, #e8eafa 50%, #fdeef0 100%);">
    <AmbientBlobs />

    <div class="relative bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/40 p-10 text-center max-w-sm w-full mx-4">
      <div v-if="status === 'processing'" key="processing">
        <BrandRings />
        <div class="space-y-2">
          <p class="text-xl font-semibold text-primary">Opening Recruitment Portal</p>
          <p class="text-gray-500 text-sm">Signing you in from IMIS…</p>
        </div>
        <PulsingDots />
      </div>

      <div v-else key="error" class="space-y-5">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-50 ring-4 ring-red-100">
          <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <p class="text-lg font-semibold text-gray-900 mb-1">Sign-in failed</p>
          <p class="text-gray-500 text-sm max-w-xs mx-auto leading-relaxed">{{ errorMessage }}</p>
        </div>
        <a :href="`${appUrl}/login`"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm hover:shadow-md">
          Back to Sign In
        </a>
      </div>

      <p class="mt-8 text-xs text-gray-400 tracking-wide">CSC RO VIII - Recruitment Portal</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { navigateTo } from '@/utils/navigate'
import AmbientBlobs from '@/Components/UI/AmbientBlobs.vue'
import BrandRings from '@/Components/UI/BrandRings.vue'
import PulsingDots from '@/Components/UI/PulsingDots.vue'

// Where the IMIS portal sends HR staff and board members. The splash is up from
// the first paint; the one-time token is posted to the server while it stays,
// and the person lands on their own dashboard (or sees why they could not sign in).

const appUrl = window.location.origin
const status = ref('processing')
const errorMessage = ref('')

const fail = (message) => {
  status.value = 'error'
  errorMessage.value = message
}

onMounted(async () => {
  const token = new URLSearchParams(window.location.search).get('sso_token')

  // The token only needs reading once. Strip it from the address bar straight
  // away so it does not linger in history or a referrer.
  window.history.replaceState({}, document.title, window.location.pathname)

  if (!token) {
    fail('No sign-in token was received. Please open the Recruitment Portal from IMIS again.')
    return
  }

  let data
  try {
    ;({ data } = await api.post('/sso', { sso_token: token }))
  } catch (e) {
    fail(e.response?.data?.message || 'IMIS could not be reached. Please try again.')
    return
  }

  const auth = useAuthStore()
  auth.setAuth(data.token, data.user, false)

  if (data.user.role === 'admin') {
    navigateTo(`${appUrl}/admin/dashboard`)
    return
  }

  // Board members: the Appointing Authority has their own dashboard.
  try {
    const { data: roleData } = await api.get('/hrmpsb/my-role', {
      headers: { Authorization: `Bearer ${data.token}` },
    })
    navigateTo(roleData.composition?.hrmpsb_role === 'appointing-authority'
      ? `${appUrl}/appointing-authority/dashboard`
      : `${appUrl}/hrmpsb/dashboard`)
  } catch {
    navigateTo(`${appUrl}/hrmpsb/dashboard`)
  }
})
</script>
