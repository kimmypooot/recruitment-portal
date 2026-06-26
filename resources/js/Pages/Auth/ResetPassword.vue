<template>
  <PublicLayout>

    <Head>
      <title>Reset Password — CSC RO VIII - Recruitment Portal</title>
    </Head>

    <section class="relative text-white overflow-hidden"
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
      <div class="relative h-10 overflow-hidden z-10">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <section class="max-w-md mx-auto px-4 sm:px-6 py-10">
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">

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
            <label class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
            <input
              v-model="form.password"
              type="password"
              autocomplete="new-password"
              required
              placeholder="At least 8 characters"
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm new password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              placeholder="Repeat your password"
              class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
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

async function submit() {
  loading.value = true
  router.post('/reset-password', { ...form, token: props.token }, {
    onFinish: () => { loading.value = false },
  })
}
</script>