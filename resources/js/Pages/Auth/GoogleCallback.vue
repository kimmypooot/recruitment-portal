<template>
  <div class="relative flex items-center justify-center min-h-screen overflow-hidden"
    style="background: linear-gradient(135deg, #f0eef9 0%, #e8eafa 50%, #fdeef0 100%);">
    <!-- Animated background particles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-20 -left-20 w-72 h-72 rounded-full opacity-20"
        style="background: radial-gradient(circle, #2a338f 0%, transparent 70%); animation: float 8s ease-in-out infinite;"></div>
      <div class="absolute -bottom-16 -right-16 w-96 h-96 rounded-full opacity-15"
        style="background: radial-gradient(circle, #ec1c2d 0%, transparent 70%); animation: float 10s ease-in-out infinite reverse;"></div>
      <div class="absolute top-1/3 right-1/4 w-48 h-48 rounded-full opacity-10"
        style="background: radial-gradient(circle, #2a338f 0%, transparent 70%); animation: float 12s ease-in-out infinite 2s;"></div>
    </div>

    <!-- Card -->
    <div
      class="relative bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/40 p-10 text-center max-w-sm w-full mx-4 transition-all duration-500"
      :class="status === 'processing' ? 'scale-100 opacity-100' : 'scale-100 opacity-100'">
      <!-- Processing state -->
      <transition name="fade" mode="out-in">
        <div v-if="status === 'processing'" key="processing" class="space-y-6">
          <!-- Animated rings -->
          <div class="relative w-28 h-28 mx-auto">
            <svg class="absolute inset-0 w-28 h-28 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="#e5e7eb" stroke-width="2.5"/>
              <circle cx="12" cy="12" r="10" stroke="url(#gradient)" stroke-width="2.5"
                stroke-linecap="round" stroke-dasharray="62.832" stroke-dashoffset="20"
                class="animate-[spin_1.5s_linear_infinite]"/>
            </svg>
            <svg class="absolute inset-2 w-[96px] h-[96px] animate-spin" style="animation-duration: 2s; animation-direction: reverse;" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="8" stroke="#e5e7eb" stroke-width="1.5"/>
              <circle cx="12" cy="12" r="8" stroke="#ec1c2d" stroke-width="1.5"
                stroke-linecap="round" stroke-dasharray="50.265" stroke-dashoffset="15" opacity="0.6"/>
            </svg>
            <img src="/images/csc-logo.png" alt="CSC"
              class="absolute w-12 h-12 rounded-full bg-white shadow-sm object-contain p-1.5"
              style="top: 50%; left: 50%; transform: translate(-50%, -50%);"
              @error="e => e.target.style.display = 'none'" />
            <defs>
              <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#2a338f"/>
                <stop offset="100%" stop-color="#ec1c2d"/>
              </linearGradient>
            </defs>
          </div>

          <transition name="fade" mode="out-in">
            <div v-if="showWelcome" key="welcome" class="space-y-2">
              <p class="text-sm font-medium tracking-wide uppercase" style="color: #ec1c2d;">Welcome back</p>
              <p class="text-2xl font-bold text-gray-900">{{ userName }}</p>
              <p class="text-gray-500 text-sm">{{ statusText }}</p>
            </div>
            <div v-else key="loading" class="space-y-2">
              <p class="text-xl font-semibold" style="color: #2a338f;">Signing you in</p>
              <p class="text-gray-500 text-sm">Please wait a moment…</p>
            </div>
          </transition>

          <!-- Pulsing dots -->
          <div class="flex justify-center gap-1.5">
            <span class="w-2 h-2 rounded-full transition-all duration-300" :class="dotClass(0)" :style="dotStyle(0)"></span>
            <span class="w-2 h-2 rounded-full transition-all duration-300" :class="dotClass(1)" :style="dotStyle(1)"></span>
            <span class="w-2 h-2 rounded-full transition-all duration-300" :class="dotClass(2)" :style="dotStyle(2)"></span>
          </div>
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
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#2a338f] text-white text-sm font-semibold rounded-xl hover:bg-[#1e2570] transition-all shadow-sm hover:shadow-md">
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
      </transition>

      <!-- Footer branding -->
      <p class="mt-8 text-xs text-gray-400 tracking-wide">CSC RO VIII Recruitment Portal</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const appUrl = window.location.origin

const status = ref('processing')
const statusText = ref('Please wait a moment…')
const errorMessage = ref('')
const userName = ref('')
const showWelcome = ref(false)
const dotIndex = ref(0)

const errorMap = {
  email_exists:        'This email is already registered. Please sign in with your password, then link Google in your Profile settings.',
  auth_failed:         'Google authentication failed. Please try again.',
  link_user_not_found: 'User not found. Please sign in and try again.',
  link_already_taken:  'This Google account is already linked to another user.',
  link_failed:         'Failed to link your Google account. Please try again.',
  already_linked:      'Your account is already linked to a Google account.',
}

function dotClass(index) {
  const dots = [
    { bg: '#2a338f', active: '#ec1c2d' },
    { bg: '#2a338f', active: '#ec1c2d' },
    { bg: '#2a338f', active: '#ec1c2d' },
  ]
  const isActive = dotIndex.value === index
  const color = isActive ? dots[index].active : dots[index].bg
  const scale = isActive ? 'scale-125' : 'scale-100'
  return `${scale}`
}
function dotStyle(index) {
  const dots = [
    { bg: '#2a338f', active: '#ec1c2d' },
    { bg: '#2a338f', active: '#ec1c2d' },
    { bg: '#2a338f', active: '#ec1c2d' },
  ]
  const isActive = dotIndex.value === index
  return { backgroundColor: isActive ? dots[index].active : dots[index].bg }
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
    setTimeout(() => { window.location.href = `${appUrl}/applicant/complete-profile` }, 1500)
    return
  }

  if (token) {
    localStorage.setItem('auth_token', token)
    const user = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
    const firstName = user.name?.split(' ')[0] ?? ''
    userName.value = firstName ? `${firstName}!` : '!'
    statusText.value = 'Signing you in…'
    showWelcome.value = true

    // Animate dots
    setInterval(() => { dotIndex.value = (dotIndex.value + 1) % 3 }, 400)

    // Brief pause to show the welcome message
    await new Promise(r => setTimeout(r, 1200))

    if (user.role === 'applicant') {
      try {
        const { data } = await api.get('/profile', {
          headers: { Authorization: `Bearer ${token}` }
        })
        window.location.href = data.is_complete
          ? `${appUrl}/applicant/dashboard`
          : `${appUrl}/applicant/complete-profile`
      } catch {
        window.location.href = `${appUrl}/applicant/dashboard`
      }
    } else {
      window.location.href = `${appUrl}/applicant/dashboard`
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
