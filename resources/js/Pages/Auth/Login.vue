<template>
  <AuthLayout
    headline="Welcome back"
    tagline="Sign in to track your application status and manage your profile."
    :benefits="[
      'Real-time updates on every stage of your application',
      'One profile you keep across every vacancy you apply for',
      'Download your generated CS Forms anytime',
    ]">

    <Head>
      <title>Sign In</title>
      <meta name="description" content="Sign in to your CSC RO VIII - Recruitment Portal account to manage applications, check exam schedules, and track your career progress." />
    </Head>

    <div>

      <h2 class="text-2xl font-semibold tracking-tight text-primary sm:text-3xl">Sign in to your account</h2>
      <p class="mt-2 text-sm text-gray-500">Welcome back — enter your details to continue.</p>

        <!-- Flash banner (success) -->
        <div v-if="page.props.flash?.message" role="alert" class="mt-6 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700 flex items-center gap-2">
          <Icon name="check" size="4" class="flex-shrink-0" />
          <span>{{ page.props.flash.message }}</span>
        </div>

        <!-- Error banner -->
        <div v-if="error" role="alert" class="mt-6 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-center gap-2">
          <Icon name="alert" size="4" class="flex-shrink-0" />
          <span>
            {{ error }}
            <template v-if="showResetHint">
              Having trouble? <a href="/forgot-password" class="underline font-medium">Reset your password</a>.
            </template>
          </span>
        </div>

        <!-- Google OAuth -->
        <a
          href="/auth/google"
          class="relative mt-8 flex items-center justify-center gap-3 w-full py-3 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:border-primary/40 hover:bg-primary-light transition-colors">
          <span class="absolute -top-2.5 right-4 rounded-full bg-primary px-2 py-0.5 text-[10px] font-semibold tracking-wide text-white uppercase shadow-sm">
            Recommended
          </span>
          <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Continue with Google
        </a>

        <!-- Divider -->
        <div class="my-6 flex items-center gap-4" aria-hidden="true">
          <span class="h-px flex-1 bg-gray-200"></span>
          <span class="text-xs font-medium tracking-wide text-gray-400 uppercase">or</span>
          <span class="h-px flex-1 bg-gray-200"></span>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

          <BaseInput
            v-model="form.email"
            type="email"
            label="Email address"
            autocomplete="email"
            required
            autofocus
            placeholder="you@example.com" />

          <BaseInput
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            label="Password"
            autocomplete="current-password"
            required
            placeholder="••••••••">
            <template #suffix>
              <button type="button" @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                class="text-gray-400 hover:text-gray-600">
                <Icon v-if="!showPassword" name="eye" size="4" aria-hidden="true" />
                <Icon v-else name="eyeOff" size="4" aria-hidden="true" />
              </button>
            </template>
          </BaseInput>

          <div class="flex flex-wrap items-center justify-between gap-3">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="form.remember"
                type="checkbox"
                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary accent-primary" />
              <span class="text-sm text-gray-600">Remember me</span>
            </label>
            <Link href="/forgot-password" class="text-sm text-primary font-medium hover:underline">Forgot password?</Link>
          </div>

          <BaseButton type="submit" size="lg" block :loading="loading">
            {{ loading ? 'Signing in…' : 'Sign in' }}
          </BaseButton>

        </form>

        <p class="mt-8 text-center text-sm text-gray-500">
          Don't have an account?
          <Link href="/register" class="font-medium text-primary hover:underline">Create one</Link>
        </p>

        <p class="mt-6 border-t border-gray-200 pt-6 text-center text-xs leading-relaxed text-gray-400">
          By signing in you agree to the
          <Link href="/terms-of-service" class="font-medium text-primary hover:underline">Terms of Service</Link>
          and
          <Link href="/privacy-policy" class="font-medium text-primary hover:underline">Privacy Policy</Link>.
        </p>

    </div>

  </AuthLayout>

  <!-- Post-login preload overlay -->
  <AuthSplashOverlay
    :visible="showPreload"
    :title="preloadWelcome ? 'Login Successful' : 'Signing you in'"
    :subtitle="preloadWelcome ? (preloadName ? `Welcome back, ${preloadName}` : 'Welcome back') : preloadSubtitle"
    :note="preloadWelcome ? preloadSubtitle : ''"
  />
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import Icon from '@/Components/UI/Icon.vue'
import BaseInput from '@/Components/UI/BaseInput.vue'
import BaseButton from '@/Components/UI/BaseButton.vue'
import AuthSplashOverlay from '@/Components/UI/AuthSplashOverlay.vue'
import { useAuthStore } from '@/stores/auth'
import { navigateTo } from '@/utils/navigate'

const page  = usePage()
const form  = reactive({ email: '', password: '', remember: false })

// Only ever redirect back to a same-site relative path (never a full URL)
// to avoid turning `next` into an open-redirect vector.
function safeNext() {
  const raw = new URLSearchParams(window.location.search).get('next')
  if (raw && raw.startsWith('/') && !raw.startsWith('//')) return raw
  return null
}
const loading       = ref(false)
const error         = ref('')
const showResetHint = ref(false)
const attemptCount  = ref(0)
const showPassword  = ref(false)

// Preload overlay state
const showPreload    = ref(false)
const preloadWelcome = ref(false)
const preloadName    = ref('')
const preloadSubtitle = ref('Please wait a moment…')

async function submit() {
  loading.value = true
  error.value   = ''

  // Show preloader immediately — don't block on the API call
  showPreload.value = true

  try {
    // API call and minimum loading-state display run in parallel
    const [{ data }] = await Promise.all([
      axios.post('/api/login', form),
      new Promise(r => setTimeout(r, 400)),
    ])

    attemptCount.value = 0
    useAuthStore().setAuth(data.token, data.user, form.remember)
    const role = data.user?.role

    preloadName.value    = data.user?.first_name ?? ''
    preloadWelcome.value = true

    // Welcome display and destination resolution run in parallel
    const welcomeDelay = new Promise(r => setTimeout(r, 700))
    const next = safeNext()
    if (role === 'admin') {
      await welcomeDelay
      navigateTo(next ?? '/admin/dashboard')
    } else if (role === 'hrmpsb') {
      const token = data.token
      try {
        const { data: roleData } = await axios.get('/api/hrmpsb/my-role', {
          headers: { Authorization: `Bearer ${token}` }
        })
        if (roleData.composition?.hrmpsb_role === 'appointing-authority') {
          preloadSubtitle.value = 'Preparing Appointing Authority dashboard…'
          await welcomeDelay
          navigateTo(next ?? '/appointing-authority/dashboard')
        } else {
          await welcomeDelay
          navigateTo(next ?? '/hrmpsb/dashboard')
        }
      } catch {
        await welcomeDelay
        navigateTo(next ?? '/hrmpsb/dashboard')
      }
    } else {
      // Profile check races against the welcome display time
      const [profileResult] = await Promise.allSettled([
        axios.get('/api/profile', { headers: { Authorization: `Bearer ${data.token}` } }),
        welcomeDelay,
      ])
      const isComplete = profileResult.status === 'fulfilled' && profileResult.value.data.is_complete
      // A next destination only makes sense once the profile is complete —
      // otherwise send them to finish their profile first, same as today.
      const dest = isComplete ? (next ?? '/applicant/dashboard') : '/applicant/complete-profile'
      navigateTo(dest)
    }
  } catch (err) {
    showPreload.value    = false
    preloadWelcome.value = false
    attemptCount.value++
    const status = err.response?.status
    showResetHint.value = false
    if (status === 429) {
      error.value = 'Too many login attempts. Please wait a moment before trying again.'
    } else if (status === 401 && attemptCount.value >= 3) {
      error.value = 'Invalid email or password.'
      showResetHint.value = true
    } else {
      error.value = err.response?.data?.message ?? 'Invalid email or password.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.pfade-enter-active, .pfade-leave-active { transition: opacity 0.4s ease, transform 0.4s ease; }
.pfade-enter-from { opacity: 0; transform: translateY(8px); }
.pfade-leave-to   { opacity: 0; transform: translateY(-8px); }
</style>
