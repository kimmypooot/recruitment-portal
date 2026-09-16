<template>
  <AuthLayout
    headline="We'll help you back in"
    tagline="Enter your email and we'll send you a password reset link."
    :benefits="[
      'Reset your password in a couple of minutes',
      'No need to remember your old one',
      'Your application data stays exactly as you left it',
    ]">

    <Head>
      <title>Forgot Password</title>
    </Head>

    <div>

      <h2 class="text-2xl font-semibold tracking-tight text-primary sm:text-3xl">Reset your password</h2>
      <p class="mt-2 text-sm text-gray-500">We'll email you a link to choose a new one.</p>

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

      <template v-if="submitted">
        <div class="mt-8 text-center">
          <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Check your email</h3>
          <p class="text-sm text-gray-500 mb-3">
            If your email is registered, you will receive a password reset link shortly.
          </p>
          <button @click="submitted = false"
            class="text-sm text-primary font-medium hover:underline">
            Send again
          </button>
        </div>
      </template>

      <form v-else @submit.prevent="submit" class="mt-8 space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
          <input
            v-model="form.email"
            type="email"
            required
            autofocus
            placeholder="you@example.com"
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition" />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3 bg-primary hover:bg-primary-dark text-white font-semibold text-base rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
          {{ loading ? 'Sending…' : 'Send reset link' }}
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

const page = usePage()
const form = reactive({ email: '' })
const loading = ref(false)
const submitted = ref(false)

async function submit() {
  loading.value = true
  router.post('/forgot-password', form, {
    preserveScroll: true,
    onSuccess: () => { submitted.value = true },
    onFinish: () => { loading.value = false },
  })
}
</script>
