<template>
  <AuthLayout
    headline="Just one more step"
    tagline="Please verify your email address to continue using all features."
    :benefits="[
      'Keeps your application updates going to the right inbox',
      'Required before you can submit an application',
      'Takes less than a minute',
    ]">

    <Head>
      <title>Verify Email</title>
    </Head>

    <div class="text-center">

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
        <h2 class="text-2xl font-semibold tracking-tight text-primary sm:text-3xl mb-2">Email verified</h2>
        <p class="text-sm text-gray-500 mb-8">
          Your email address has been verified. You can now proceed with your application.
        </p>
        <Link :href="authUser.email ? '/applicant/dashboard' : '/login'"
          class="inline-block w-full py-3 bg-primary hover:bg-primary-dark text-white font-semibold text-base rounded-lg shadow-sm transition-colors text-center">
          {{ authUser.email ? 'Go to Dashboard' : 'Sign In' }}
        </Link>
      </template>

      <template v-else>
        <h2 class="text-2xl font-semibold tracking-tight text-primary sm:text-3xl mb-2">Verify your email address</h2>
        <p class="text-sm text-gray-500 mb-8">
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

        <form @submit.prevent="submitCode" class="mb-4">
          <input
            v-model="code"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="6"
            placeholder="000000"
            class="w-full text-center tracking-[0.5em] text-lg font-semibold py-3 mb-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
          <button
            type="submit"
            :disabled="verifying || code.length !== 6"
            class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-semibold text-base rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
            {{ verifying ? 'Verifying…' : 'Verify Code' }}
          </button>
        </form>

        <button
          @click="resend"
          :disabled="sending"
          class="w-full py-3 border border-gray-300 text-gray-700 hover:border-primary/40 hover:bg-primary-light font-semibold text-sm rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed mb-3">
          {{ sending ? 'Sending…' : 'Resend Code / Link' }}
        </button>

        <button @click="logout" class="w-full py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
          Sign out
        </button>
      </template>

    </div>

  </AuthLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
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
