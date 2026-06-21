<template>
  <div class="min-h-screen flex flex-col bg-gray-50">

    <!-- Navbar (minimal) -->
    <header class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <Link href="/" class="flex items-center gap-3 group">
          <div class="w-9 h-9 rounded-lg bg-blue-700 flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
          </div>
          <div class="leading-tight">
            <p class="text-sm font-bold text-gray-900">CSC Regional Office</p>
            <p class="text-xs text-gray-500">Recruitment Portal</p>
          </div>
        </Link>
        <Link href="/login" class="text-sm text-blue-700 font-medium hover:underline">Sign in</Link>
      </div>
    </header>

    <!-- Form -->
    <main class="flex-1 flex items-center justify-center px-4 py-12">
      <div class="w-full max-w-lg">

        <div class="text-center mb-8">
          <h1 class="text-2xl font-bold text-gray-900">Create your account</h1>
          <p class="text-sm text-gray-500 mt-1">Apply for government positions at CSC Regional Office</p>
        </div>

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
              <span class="bg-white px-3 text-xs text-gray-400">or register with email</span>
            </div>
          </div>

          <!-- Error banner -->
          <div v-if="errors.general" class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ errors.general }}
          </div>

          <form @submit.prevent="submit" class="space-y-5">

            <!-- Last Name + First Name -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                <input
                  v-model="form.last_name"
                  type="text"
                  required
                  placeholder="Dela Cruz"
                  class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition"
                  :class="errors.last_name ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
                <p v-if="errors.last_name" class="mt-1 text-xs text-red-600">{{ errors.last_name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                <input
                  v-model="form.first_name"
                  type="text"
                  required
                  placeholder="Juan"
                  class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition"
                  :class="errors.first_name ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
                <p v-if="errors.first_name" class="mt-1 text-xs text-red-600">{{ errors.first_name }}</p>
              </div>
            </div>

            <!-- Middle Name + Suffix -->
            <div class="grid grid-cols-3 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Middle Name</label>
                <input
                  v-model="form.middle_name"
                  type="text"
                  placeholder="Santos (optional)"
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Suffix</label>
                <select
                  v-model="form.suffix"
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition bg-white">
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
                class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition"
                :class="errors.email ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
              <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
              <input
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                required
                placeholder="At least 8 characters"
                class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition"
                :class="errors.password ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
              <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password <span class="text-red-500">*</span></label>
              <input
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                placeholder="Repeat your password"
                class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition"
                :class="errors.password_confirmation ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
              <p v-if="errors.password_confirmation" class="mt-1 text-xs text-red-600">{{ errors.password_confirmation }}</p>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-semibold text-sm rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
              {{ loading ? 'Creating account…' : 'Create account' }}
            </button>

          </form>

          <p class="mt-6 text-center text-sm text-gray-500">
            Already have an account?
            <Link href="/login" class="text-blue-700 font-medium hover:underline">Sign in</Link>
          </p>

        </div>

        <p class="mt-4 text-center text-xs text-gray-400">
          By creating an account you agree to the CSC data privacy policy.
        </p>

      </div>
    </main>

  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'

const form = reactive({
  last_name:             '',
  first_name:            '',
  middle_name:           '',
  suffix:                '',
  email:                 '',
  password:              '',
  password_confirmation: '',
})

const loading = ref(false)
const errors  = reactive({
  last_name: '', first_name: '', email: '',
  password: '', password_confirmation: '', general: '',
})

function clearErrors() {
  Object.keys(errors).forEach(k => (errors[k] = ''))
}

async function submit() {
  clearErrors()
  loading.value = true
  try {
    const { data } = await axios.post('/api/register', form)
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))
    router.visit('/applicant/complete-profile')
  } catch (err) {
    const resp = err.response?.data
    if (resp?.errors) {
      Object.assign(errors, resp.errors)
    } else {
      errors.general = resp?.message ?? 'Registration failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>
