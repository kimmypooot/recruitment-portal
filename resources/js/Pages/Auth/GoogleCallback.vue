<template>
  <div class="relative flex items-center justify-center min-h-screen overflow-hidden"
    style="background: linear-gradient(135deg, #f0eef9 0%, #e8eafa 50%, #fdeef0 100%);">
    <AmbientBlobs />

    <!-- Card -->
    <div class="relative bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/40 p-10 text-center max-w-sm w-full mx-4">
      <!-- Processing state -->
        <div v-if="status === 'processing'" key="processing">
          <BrandRings />

          <transition name="fade" mode="out-in">
            <div v-if="showWelcome" key="welcome" class="space-y-2">
              <p class="text-sm font-medium tracking-wide uppercase text-accent">Welcome back</p>
              <p class="text-2xl font-bold text-gray-900">{{ userName }}</p>
              <p class="text-gray-500 text-sm">{{ statusText }}</p>
            </div>
            <div v-else key="loading" class="space-y-2">
              <p class="text-xl font-semibold text-primary">Signing you in</p>
              <p class="text-gray-500 text-sm">Please wait a moment…</p>
            </div>
          </transition>

          <PulsingDots />
        </div>

        <!-- Error state -->
        <div v-else-if="status === 'error'" key="error" class="space-y-5">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-50 ring-4 ring-red-100">
            <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-lg font-semibold text-gray-900 mb-1">Authentication Failed</p>
            <p class="text-gray-500 text-sm max-w-xs mx-auto leading-relaxed">{{ errorMessage }}</p>
          </div>
          <a :href="`${appUrl}/login`"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Sign In
          </a>
        </div>

        <!-- Link success state -->
        <div v-else-if="status === 'link_success'" key="link_success" class="space-y-5">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 ring-4 ring-green-100">
            <svg class="w-7 h-7 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div>
            <p class="text-lg font-semibold text-gray-900">Account Linked!</p>
            <p class="text-gray-500 text-sm mt-1">Your Google account has been connected successfully.</p>
          </div>
          <div class="flex justify-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-bounce" style="animation-delay: 0s;"></span>
            <span class="w-2 h-2 rounded-full bg-green-500 animate-bounce" style="animation-delay: 0.15s;"></span>
            <span class="w-2 h-2 rounded-full bg-green-500 animate-bounce" style="animation-delay: 0.3s;"></span>
          </div>
        </div>

      <!-- Footer branding -->
      <p class="mt-8 text-xs text-gray-400 tracking-wide">CSC RO VIII - Recruitment Portal</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { navigateTo } from '@/utils/navigate'
import AmbientBlobs from '@/Components/UI/AmbientBlobs.vue'
import BrandRings from '@/Components/UI/BrandRings.vue'
import PulsingDots from '@/Components/UI/PulsingDots.vue'

const appUrl = window.location.origin

const status = ref('processing')
const statusText = ref('Please wait a moment…')
const errorMessage = ref('')
const userName = ref('')
const showWelcome = ref(false)

const errorMap = {
  email_exists:        'This email is already registered. Please sign in with your password, then link Google in your Profile settings.',
  name_exists:         'An account with this name already exists. Please sign in or contact support if you need help recovering your account.',
  auth_failed:         'Google authentication failed. Please try again.',
  link_user_not_found: 'User not found. Please sign in and try again.',
  link_already_taken:  'This Google account is already linked to another user.',
  link_failed:         'Failed to link your Google account. Please try again.',
  already_linked:      'Your account is already linked to a Google account.',
}

onMounted(async () => {
  const params = new URLSearchParams(window.location.search)
  const token = params.get('token')
  const userData = params.get('user')
  const error = params.get('error')
  const linkSuccess = params.get('link_success')

  if (userData) {
    try {
      const user = JSON.parse(atob(userData))
      localStorage.setItem('auth_user', JSON.stringify(user))
    } catch {}
  }

  if (error) {
    status.value = 'error'
    errorMessage.value = errorMap[error] || 'An error occurred during authentication.'
    return
  }

  if (linkSuccess) {
    status.value = 'link_success'
    setTimeout(() => navigateTo(`${appUrl}/applicant/complete-profile`), 1500)
    return
  }

  if (token) {
    localStorage.setItem('auth_token', token)
    localStorage.setItem('auth_token_created_at', String(Date.now()))
    // Google OAuth tokens never expire server-side (see AuthController) — treat
    // them the same as a "remember me" login for idle-timeout purposes.
    localStorage.setItem('auth_remember', '1')
    const user = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
    const firstName = user.first_name ?? ''
    userName.value = firstName ? `${firstName}!` : '!'
    statusText.value = 'Signing you in…'
    showWelcome.value = true

    // Brief pause to show the welcome message
    await new Promise(r => setTimeout(r, 1200))

    if (user.role === 'admin') {
      navigateTo(`${appUrl}/admin/dashboard`)
    } else if (user.role === 'hrmpsb') {
      try {
        const { data: roleData } = await api.get('/hrmpsb/my-role', {
          headers: { Authorization: `Bearer ${token}` }
        })
        if (roleData.composition?.hrmpsb_role === 'appointing-authority') {
          navigateTo(`${appUrl}/appointing-authority/dashboard`)
        } else {
          navigateTo(`${appUrl}/hrmpsb/dashboard`)
        }
      } catch {
        navigateTo(`${appUrl}/hrmpsb/dashboard`)
      }
    } else {
      try {
        const { data } = await api.get('/profile', {
          headers: { Authorization: `Bearer ${token}` }
        })
        navigateTo(data.is_complete ? `${appUrl}/applicant/dashboard` : `${appUrl}/applicant/complete-profile`)
      } catch {
        navigateTo(`${appUrl}/applicant/dashboard`)
      }
    }
    return
  }

  status.value = 'error'
  errorMessage.value = 'No authentication data received. Please try again.'
})
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.05); }
  66% { transform: translate(-20px, 20px) scale(0.95); }
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
