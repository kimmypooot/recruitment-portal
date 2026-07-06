<template>
  <PublicLayout>

    <Head>
      <title>Verify Email</title>
    </Head>

    <section class="relative text-white overflow-hidden min-h-[240px]"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(30,37,112,0.88) 0%, rgba(42,51,143,0.85) 50%, rgba(26,31,94,0.90) 100%);"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl">
          <p class="text-white/60 text-sm font-medium uppercase tracking-wider mb-3">Email Verification</p>
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">Verify Your Email</h1>
          <p class="text-white/80 text-base leading-relaxed">
            Please verify your email address to continue using all features.
          </p>
        </div>
      </div>
      <div class="relative h-10 overflow-hidden z-10 pointer-events-none">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB" aria-hidden="true">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <section class="max-w-md mx-auto px-4 sm:px-6 py-10">
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">

        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>

        <template v-if="isVerified">
          <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h2 class="text-lg font-semibold text-gray-900 mb-2">Email verified</h2>
          <p class="text-sm text-gray-500 mb-6">
            Your email address has been verified. You can now proceed with your application.
          </p>
          <Link :href="authUser.email ? '/applicant/dashboard' : '/login'"
            class="inline-block w-full py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold text-sm rounded-lg shadow-sm transition-colors text-center mb-3">
            {{ authUser.email ? 'Go to Dashboard' : 'Sign In' }}
          </Link>
        </template>

        <template v-else>
          <h2 class="text-lg font-semibold text-gray-900 mb-2">Verify your email address</h2>
          <p class="text-sm text-gray-500 mb-6">
            We sent a 6-digit verification code to your email address. Enter it below to activate your account,
            or click the link in the email instead.
          </p>

          <div v-if="page.props.flash?.message" class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">
            {{ page.props.flash.message }}
          </div>

          <div v-if="verificationSent" class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">
            A new verification code has been sent to your email.
          </div>

          <div v-if="codeError" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            {{ codeError }}
          </div>

          <form @submit.prevent="submitCode" class="mb-3">
            <input
              v-model="code"
              type="text"
              inputmode="numeric"
              pattern="[0-9]*"
              maxlength="6"
              placeholder="000000"
              class="w-full text-center tracking-[0.5em] text-lg font-semibold py-2.5 mb-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
            <button
              type="submit"
              :disabled="verifying || code.length !== 6"
              class="w-full py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold text-sm rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
              {{ verifying ? 'Verifying…' : 'Verify Code' }}
            </button>
          </form>

          <button
            @click="resend"
            :disabled="sending"
            class="w-full py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold text-sm rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed mb-3">
            {{ sending ? 'Sending…' : 'Resend Code / Link' }}
          </button>

          <button @click="logout" class="w-full py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            Sign out
          </button>
        </template>

      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import api from '@/services/api.js'

const page = usePage()
const sending = ref(false)
const verifying = ref(false)
const verificationSent = ref(false)
const codeError = ref('')
const code = ref('')
const authUser = ref(JSON.parse(localStorage.getItem('auth_user') ?? '{}'))
const arrivedViaVerifiedLink = new URLSearchParams(window.location.search).get('verified') === '1'
const isVerified = computed(() => !!authUser.value.email_verified_at || arrivedViaVerifiedLink)

onMounted(async () => {
  // Refresh the cached user in the same browser tab/device so localStorage
  // reflects verification without forcing a re-login.
  if (localStorage.getItem('auth_token')) {
    try {
      const { data } = await api.get('/me')
      authUser.value = data.user
      localStorage.setItem('auth_user', JSON.stringify(data.user))
    } catch {
      // not logged in on this device, or token expired — ignore
    }
  }
})

async function resend() {
  sending.value = true
  verificationSent.value = false
  try {
    await api.post('/email/verification-notification')
    verificationSent.value = true
  } catch {
    // silently fail — the user can retry
  } finally {
    sending.value = false
  }
}

async function submitCode() {
  verifying.value = true
  codeError.value = ''
  try {
    const { data } = await api.post('/email/verify-code', { code: code.value })
    authUser.value = data.user
    localStorage.setItem('auth_user', JSON.stringify(data.user))
  } catch (err) {
    codeError.value = err.response?.data?.message ?? 'Something went wrong. Please try again.'
  } finally {
    verifying.value = false
  }
}

function logout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  router.visit('/login')
}
</script>