<template>
  <PublicLayout>

    <Head>
      <title>Sign In — CSC RO VIII - Recruitment Portal</title>
      <meta name="description" content="Sign in to your CSC RO VIII - Recruitment Portal account to manage applications, check exam schedules, and track your career progress." />
    </Head>

    <!-- Hero (compact) -->
    <section class="relative text-white overflow-hidden"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(30,37,112,0.88) 0%, rgba(42,51,143,0.85) 50%, rgba(26,31,94,0.90) 100%);"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl">
          <p class="text-white/60 text-sm font-medium uppercase tracking-wider mb-3">Applicant Portal</p>
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">Welcome Back</h1>
          <p class="text-white/80 text-base leading-relaxed">
            Sign in to track your application status and manage your profile.
          </p>
        </div>
      </div>
      <div class="relative h-10 overflow-hidden z-10">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <!-- Form -->
    <section class="max-w-[32rem] mx-auto px-4 sm:px-6 py-10">

      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">

        <!-- Google OAuth -->
        <a
          href="/auth/google"
          class="flex items-center justify-center gap-3 w-full py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors mb-6">
          <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Continue with Google
        </a>

        <!-- Divider -->
        <div class="relative mb-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
          </div>
          <div class="relative flex justify-center">
            <span class="bg-white px-3 text-xs text-gray-400">or sign in with email</span>
          </div>
        </div>

        <!-- Error banner -->
        <div v-if="error" class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span v-html="error"></span>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              placeholder="you@example.com"
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                placeholder="••••••••"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition pr-10" />
              <button type="button" @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.remember"
                type="checkbox"
                class="w-4 h-4 rounded border-gray-300 text-[#2a338f] focus:ring-[#2a338f] accent-[#2a338f]" />
              <span class="text-sm text-gray-600">Remember me</span>
            </label>
            <Link href="/forgot-password" class="text-sm text-[#2a338f] font-medium hover:underline">Forgot password?</Link>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white font-semibold text-sm rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
            {{ loading ? 'Signing in…' : 'Sign in' }}
          </button>

        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
          Don't have an account?
          <Link href="/register" class="text-[#2a338f] font-medium hover:underline">Create one</Link>
        </p>

      </div>

    </section>

  </PublicLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const form = reactive({ email: '', password: '', remember: false })
const loading = ref(false)
const error   = ref('')
const attemptCount = ref(0)
const showPassword = ref(false)

async function submit() {
  loading.value = true
  error.value   = ''
  try {
    const { data } = await axios.post('/api/login', form)
    attemptCount.value = 0
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))
    const role = data.user?.role

    if (['admin', 'hr-manager', 'hr-officer'].includes(role)) {
      router.visit('/admin/dashboard')
    } else if (['hrmpsb-member', 'hrmpsb-secretariat', 'appointing-authority'].includes(role)) {
      router.visit('/hrmpsb/dashboard')
    } else {
      // Applicant: check profile completion
      try {
        const { data: profileData } = await axios.get('/api/profile', {
          headers: { Authorization: `Bearer ${data.token}` }
        })
        if (profileData.is_complete) {
          router.visit('/applicant/dashboard')
        } else {
          router.visit('/applicant/complete-profile')
        }
      } catch {
        router.visit('/applicant/complete-profile')
      }
    }
  } catch (err) {
    attemptCount.value++
    const status = err.response?.status
    if (status === 429) {
      error.value = 'Too many login attempts. Please wait a moment before trying again.'
    } else if (status === 401 && attemptCount.value >= 3) {
      error.value = 'Invalid email or password. Having trouble? <a href="/forgot-password" class="underline font-medium">Reset your password</a>.'
    } else {
      error.value = err.response?.data?.message ?? 'Invalid email or password.'
    }
  } finally {
    loading.value = false
  }
}
</script>
