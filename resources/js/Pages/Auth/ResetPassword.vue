<template>
  <PublicLayout>

    <Head>
      <title>Reset Password</title>
    </Head>

    <section class="relative text-white overflow-hidden min-h-[240px]"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(30,37,112,0.88) 0%, rgba(42,51,143,0.85) 50%, rgba(26,31,94,0.90) 100%);"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl">
          <p class="text-white/60 text-sm font-medium uppercase tracking-wider mb-3">Account Recovery</p>
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">Set New Password</h1>
          <p class="text-white/80 text-base leading-relaxed">
            Choose a new password for your account.
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
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">

        <div v-if="page.props.flash?.message" class="mb-5 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700 flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ page.props.flash.message }}
        </div>

        <div v-if="page.props.errors?.email" class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ page.props.errors.email }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
            <input
              v-model="form.email"
              type="email"
              readonly
              class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm text-gray-500 bg-gray-50 cursor-not-allowed" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">New password <span class="text-red-500">*</span></label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                placeholder="At least 8 characters"
                class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition pr-10"
                :class="errors.password ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
              <button type="button" @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>

            <!-- Password requirements -->
            <div class="mt-2 space-y-1">
              <p class="text-xs" :class="form.password.length >= 8 ? 'text-green-600' : 'text-gray-400'">
                <span v-html="form.password.length >= 8 ? '&#10003;' : '&#9679;'"></span> At least 8 characters
              </p>
              <p class="text-xs" :class="/[A-Z]/.test(form.password) ? 'text-green-600' : 'text-gray-400'">
                <span v-html="/[A-Z]/.test(form.password) ? '&#10003;' : '&#9679;'"></span> One uppercase letter
              </p>
              <p class="text-xs" :class="/[a-z]/.test(form.password) ? 'text-green-600' : 'text-gray-400'">
                <span v-html="/[a-z]/.test(form.password) ? '&#10003;' : '&#9679;'"></span> One lowercase letter
              </p>
              <p class="text-xs" :class="/[0-9]/.test(form.password) ? 'text-green-600' : 'text-gray-400'">
                <span v-html="/[0-9]/.test(form.password) ? '&#10003;' : '&#9679;'"></span> One number
              </p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm new password <span class="text-red-500">*</span></label>
            <div class="relative">
              <input
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                placeholder="Repeat your password"
                class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition pr-10"
                :class="errors.password_confirmation ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
              <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg v-if="!showConfirmPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
            <p v-if="errors.password_confirmation" class="mt-1 text-xs text-red-600">{{ errors.password_confirmation }}</p>
            <p v-if="form.password_confirmation && form.password !== form.password_confirmation" class="mt-1 text-xs text-red-500">
              Passwords do not match
            </p>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white font-semibold text-sm rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
            {{ loading ? 'Resetting…' : 'Reset Password' }}
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
          Remember your password?
          <Link href="/login" class="text-[#2a338f] font-medium hover:underline">Sign in</Link>
        </p>

      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const page = usePage()
const props = defineProps({
  token: { type: String, required: true },
  email: { type: String, default: '' },
})

const form = reactive({
  email: props.email,
  password: '',
  password_confirmation: '',
})
const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const errors = reactive({
  password: '',
  password_confirmation: '',
})

function clearErrors() {
  Object.keys(errors).forEach(k => (errors[k] = ''))
}

async function submit() {
  clearErrors()

  // Client-side validation
  let hasError = false
  if (form.password.length < 8) {
    errors.password = 'Password must be at least 8 characters.'
    hasError = true
  }
  if (!/[A-Z]/.test(form.password)) {
    errors.password = 'Password must contain at least one uppercase letter.'
    hasError = true
  }
  if (!/[a-z]/.test(form.password)) {
    errors.password = 'Password must contain at least one lowercase letter.'
    hasError = true
  }
  if (!/[0-9]/.test(form.password)) {
    errors.password = 'Password must contain at least one number.'
    hasError = true
  }
  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match.'
    hasError = true
  }
  if (hasError) return

  loading.value = true
  router.post('/reset-password', { ...form, token: props.token }, {
    onFinish: () => { loading.value = false },
  })
}
</script>