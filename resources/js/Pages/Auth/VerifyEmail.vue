<template>
  <PublicLayout>

    <Head>
      <title>Verify Email — CSC RO VIII - Recruitment Portal</title>
    </Head>

    <section class="relative text-white overflow-hidden"
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
      <div class="relative h-10 overflow-hidden z-10">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <section class="max-w-md mx-auto px-4 sm:px-6 py-10">
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">

        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>

        <h2 class="text-lg font-semibold text-gray-900 mb-2">Verify your email address</h2>
        <p class="text-sm text-gray-500 mb-6">
          We sent a verification link to your email address. Click the link to activate your account.
        </p>

        <div v-if="page.props.flash?.message" class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">
          {{ page.props.flash.message }}
        </div>

        <div v-if="verificationSent" class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">
          A new verification link has been sent to your email.
        </div>

        <button
          @click="resend"
          :disabled="sending"
          class="w-full py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white font-semibold text-sm rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed mb-3">
          {{ sending ? 'Sending…' : 'Resend Verification Email' }}
        </button>

        <button @click="logout" class="w-full py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
          Sign out
        </button>

      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const page = usePage()
const sending = ref(false)
const verificationSent = ref(false)

function resend() {
  sending.value = true
  router.post('/email/verification-notification', {}, {
    preserveScroll: true,
    onSuccess: () => { verificationSent.value = true },
    onFinish: () => { sending.value = false },
  })
}

function logout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  router.visit('/login')
}
</script>