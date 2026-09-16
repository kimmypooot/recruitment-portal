<template>
  <AuthLayout
    headline="Set a new password"
    tagline="Choose a new password for your account."
    :benefits="[
      'Pick something you have not used here before',
      'At least 8 characters, mixing upper and lower case with a number',
      'You stay signed out on other devices until you sign in again',
    ]">

    <Head>
      <title>Reset Password</title>
    </Head>

    <div>

      <h2 class="text-2xl font-semibold tracking-tight text-primary sm:text-3xl">Set a new password</h2>
      <p class="mt-2 text-sm text-gray-500">Choose a strong password you haven't used before.</p>

      <div v-if="page.props.flash?.message" class="mt-6 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ page.props.flash.message }}
      </div>

      <div v-if="page.props.errors?.email" class="mt-6 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ page.props.errors.email }}
      </div>

      <form @submit.prevent="submit" class="mt-8 space-y-5">
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
              class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition pr-10"
              :class="errors.password ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
            <button type="button" @click="showPassword = !showPassword"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <Icon v-if="!showPassword" name="eye" size="4" aria-hidden="true" />
              <Icon v-else name="eyeOff" size="4" aria-hidden="true" />
            </button>
          </div>
          <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>

          <PasswordRequirements :password="form.password" />
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
              class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition pr-10"
              :class="errors.password_confirmation ? 'border-red-400 bg-red-50' : 'border-gray-300'" />
            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
              :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <Icon v-if="!showConfirmPassword" name="eye" size="4" aria-hidden="true" />
              <Icon v-else name="eyeOff" size="4" aria-hidden="true" />
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
          class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-semibold text-base rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
          {{ loading ? 'Resetting…' : 'Reset password' }}
        </button>
      </form>

      <p class="mt-8 text-center text-sm text-gray-500">
        Remember your password?
        <Link href="/login" class="font-medium text-primary hover:underline">Sign in</Link>
      </p>

    </div>

  </AuthLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import Icon from '@/Components/UI/Icon.vue'
import PasswordRequirements from '@/Components/UI/PasswordRequirements.vue'
import { unmetPasswordRequirements } from '@/utils/password'

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

  // Client-side validation — collect every failing rule instead of letting
  // each check silently overwrite the previous one's message.
  let hasError = false
  const unmet = unmetPasswordRequirements(form.password)
  if (unmet.length) {
    errors.password = `Password must contain ${unmet.join(', ')}.`
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
