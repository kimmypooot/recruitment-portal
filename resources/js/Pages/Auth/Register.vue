<template>
  <AuthLayout
    headline="Create your account"
    tagline="Apply for government positions at the Civil Service Commission Regional Office VIII."
    :benefits="[
      'One profile you reuse for every vacancy you apply to',
      'Track your application status from submission to decision',
      'Generate and download your CS Forms in a few clicks',
    ]">

    <Head>
      <title>Register</title>
      <meta name="description" content="Create a CSC RO VIII - Recruitment Portal account to apply for government career positions at the Civil Service Commission Regional Office VIII." />
    </Head>

    <div>

      <h2 class="text-2xl font-semibold tracking-tight text-primary sm:text-3xl">Create your account</h2>
      <p class="mt-2 text-sm text-gray-500">A few details and you're ready to apply.</p>

        <!-- Error banner -->
        <div v-if="errors.general" role="alert" class="mt-6 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-center gap-2">
          <Icon name="alert" size="4" class="flex-shrink-0" />
          {{ errors.general }}
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

          <!-- Last Name + First Name -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
              <input
                v-model="form.last_name"
                type="text"
                required
                placeholder="Dela Cruz"
                :aria-describedby="errors.last_name ? 'error-last_name' : undefined"
                class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition"
                :class="errors.last_name ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
              <p v-if="errors.last_name" id="error-last_name" class="mt-1 text-xs text-red-600">{{ errors.last_name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
              <input
                v-model="form.first_name"
                type="text"
                required
                placeholder="Juan"
                :aria-describedby="errors.first_name ? 'error-first_name' : undefined"
                class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition"
                :class="errors.first_name ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
              <p v-if="errors.first_name" id="error-first_name" class="mt-1 text-xs text-red-600">{{ errors.first_name }}</p>
            </div>
          </div>

          <!-- Middle Name + Suffix -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Middle Name</label>
              <input
                v-model="form.middle_name"
                type="text"
                placeholder="Santos (optional)"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Suffix</label>
              <select
                v-model="form.suffix"
                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition bg-white">
                <option value="">None</option>
                <option>Jr.</option>
                <option>Sr.</option>
                <option>II</option>
                <option>III</option>
                <option>IV</option>
              </select>
            </div>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address <span class="text-red-500">*</span></label>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              placeholder="you@example.com"
              :aria-describedby="errors.email ? 'error-email' : undefined"
              class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition"
              :class="errors.email ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
            <p v-if="errors.email" id="error-email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                placeholder="At least 8 characters"
                :aria-describedby="errors.password ? 'error-password' : undefined"
                class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition pr-10"
                :class="errors.password ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
              <button type="button" @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <Icon v-if="!showPassword" name="eye" size="4" aria-hidden="true" />
                <Icon v-else name="eyeOff" size="4" aria-hidden="true" />
              </button>
            </div>
            <p v-if="errors.password" id="error-password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>

            <PasswordRequirements :password="form.password" />
          </div>

          <!-- Confirm Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password <span class="text-red-500">*</span></label>
            <div class="relative">
              <input
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                placeholder="Repeat your password"
                :aria-describedby="errors.password_confirmation ? 'error-password_confirmation' : undefined"
                class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition pr-10"
                :class="errors.password_confirmation ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
              <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <Icon v-if="!showConfirmPassword" name="eye" size="4" aria-hidden="true" />
                <Icon v-else name="eyeOff" size="4" aria-hidden="true" />
              </button>
            </div>
            <p v-if="errors.password_confirmation" id="error-password_confirmation" class="mt-1 text-xs text-red-600">{{ errors.password_confirmation }}</p>
            <p v-if="form.password_confirmation && form.password !== form.password_confirmation" class="mt-1 text-xs text-red-500">
              Passwords do not match
            </p>
          </div>

          <!-- Privacy policy consent -->
          <div>
            <label class="flex items-start gap-2.5 cursor-pointer">
              <input
                v-model="form.privacy_consent"
                type="checkbox"
                :aria-describedby="errors.privacy ? 'error-privacy' : undefined"
                class="mt-0.5 w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary accent-primary" />
              <span class="text-sm text-gray-600 leading-relaxed">
                I have read and agree to the
                <a href="/privacy-policy" target="_blank" class="text-primary font-medium hover:underline">Data Privacy Policy</a>
                and
                <a href="/terms-of-service" target="_blank" class="text-primary font-medium hover:underline">Terms of Service</a>
                of the Civil Service Commission. <span class="text-red-500">*</span>
              </span>
            </label>
            <p v-if="errors.privacy" id="error-privacy" class="mt-1 text-xs text-red-600">{{ errors.privacy }}</p>
          </div>

          <button
            type="submit"
            :disabled="loading || !form.privacy_consent"
            class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-semibold text-base rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
            {{ loading ? 'Creating account…' : 'Create account' }}
          </button>

        </form>

        <p class="mt-8 text-center text-sm text-gray-500">
          Already have an account?
          <Link href="/login" class="font-medium text-primary hover:underline">Sign in</Link>
        </p>

        <p class="mt-6 border-t border-gray-200 pt-6 text-center text-xs leading-relaxed text-gray-400">
          By creating an account you agree to the CSC
          <a href="/privacy-policy" target="_blank" class="font-medium text-primary hover:underline">Data Privacy Policy</a>
          and
          <a href="/terms-of-service" target="_blank" class="font-medium text-primary hover:underline">Terms of Service</a>.
        </p>

    </div>

  </AuthLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import Icon from '@/Components/UI/Icon.vue'
import { useAuthStore } from '@/stores/auth'
import PasswordRequirements from '@/Components/UI/PasswordRequirements.vue'

const PRIVACY_POLICY_VERSION = '1.0'

const form = reactive({
  last_name:             '',
  first_name:            '',
  middle_name:           '',
  suffix:                '',
  email:                 '',
  password:              '',
  password_confirmation: '',
  privacy_consent:       false,
})

const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const errors  = reactive({
  last_name: '', first_name: '', email: '',
  password: '', password_confirmation: '', privacy: '', general: '',
})

function clearErrors() {
  Object.keys(errors).forEach(k => (errors[k] = ''))
}

async function submit() {
  clearErrors()
  if (!form.privacy_consent) {
    errors.privacy = 'You must agree to the Data Privacy Policy to continue.'
    return
  }
  loading.value = true
  try {
    const { data } = await axios.post('/api/register', {
      ...form,
      privacy_policy_version: PRIVACY_POLICY_VERSION,
    })
    useAuthStore().setAuth(data.token, data.user)
    router.visit('/email/verify')
  } catch (err) {
    const status = err.response?.status
    if (status === 429) {
      errors.general = 'Too many registration attempts. Please try again later.'
    } else {
      const resp = err.response?.data
      if (resp?.errors) {
        Object.assign(errors, resp.errors)
      } else {
        errors.general = resp?.message ?? 'Registration failed. Please try again.'
      }
    }
  } finally {
    loading.value = false
  }
}
</script>