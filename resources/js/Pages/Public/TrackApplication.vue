<template>
  <PublicLayout>

    <Head>
      <title>Track My Application</title>
      <meta name="description" content="Check the status of your CSC RO VIII job application using your reference code and email — no login required." />
    </Head>

    <!-- Hero (compact, matches Login.vue) -->
    <section class="relative text-white overflow-hidden min-h-[220px]"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(30,37,112,0.88) 0%, rgba(42,51,143,0.85) 50%, rgba(26,31,94,0.90) 100%);"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl">
          <p class="text-white/60 text-sm font-medium uppercase tracking-wider mb-3">Applicant Services</p>
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">Track My Application</h1>
          <p class="text-white/80 text-base leading-relaxed">
            Enter your reference code and the email you applied with — no account or password needed.
          </p>
        </div>
      </div>
      <div class="relative h-10 overflow-hidden z-10 pointer-events-none">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB" aria-hidden="true">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <section class="max-w-[36rem] mx-auto px-4 sm:px-6 py-10">
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">

        <!-- Lookup form -->
        <form v-if="!result" @submit.prevent="lookup" class="space-y-5">
          <BaseInput
            v-model="form.reference_code"
            label="Reference code"
            placeholder="CSC-XXXXXXXX"
            hint="Sent to your email when you submitted your application."
            required
            autofocus
            class="uppercase placeholder:normal-case" />

          <BaseInput
            v-model="form.email"
            type="email"
            label="Email address"
            placeholder="you@example.com"
            hint="The email on the account you applied with."
            required />

          <div v-if="error" role="alert" class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-center gap-2">
            <Icon name="alert" :size="4" class="flex-shrink-0" />
            <span>{{ error }}</span>
          </div>

          <BaseButton type="submit" block :loading="loading">
            {{ loading ? 'Looking up…' : 'Check status' }}
          </BaseButton>

          <p class="text-center text-sm text-gray-500">
            Have an account? <Link href="/login" class="text-primary font-medium hover:underline">Sign in</Link> for full application details.
          </p>
        </form>

        <!-- Result -->
        <div v-else class="space-y-6">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ result.reference_code }}</p>
              <h2 class="text-lg font-bold text-gray-900">{{ result.vacancy.position_title ?? 'Position' }}</h2>
              <p class="text-sm text-gray-500 mt-0.5">{{ result.vacancy.place_of_assignment }}</p>
            </div>
            <StatusBadge :status="result.status" />
          </div>

          <div class="grid grid-cols-2 gap-4 bg-gray-50 rounded-xl p-4 text-sm">
            <div>
              <p class="text-xs text-gray-400 uppercase tracking-wide">Submitted</p>
              <p class="text-gray-900 font-medium mt-0.5">{{ formatDate(result.submitted_at) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 uppercase tracking-wide">Last updated</p>
              <p class="text-gray-900 font-medium mt-0.5">{{ formatDate(result.last_updated_at) }}</p>
            </div>
          </div>

          <p class="text-xs text-gray-400 leading-relaxed">
            This is a summary view. For full details, documents, and notifications,
            <Link href="/login" class="text-primary font-medium hover:underline">sign in to your account</Link>.
          </p>

          <BaseButton variant="secondary" block @click="reset">Check another application</BaseButton>
        </div>

      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
/**
 * Passwordless status lookup (docs/strategic-feature-scoping.md). Mirrors
 * Login.vue's layout conventions but talks to the public /api/track
 * endpoint instead — no account, no bearer token.
 */
import { ref, reactive } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import Icon from '@/Components/UI/Icon.vue'
import BaseInput from '@/Components/UI/BaseInput.vue'
import BaseButton from '@/Components/UI/BaseButton.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import { formatDate } from '@/utils/dates'

const form = reactive({ reference_code: '', email: '' })
const loading = ref(false)
const error = ref('')
const result = ref(null)

async function lookup() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await axios.post('/api/track', {
      reference_code: form.reference_code.trim(),
      email: form.email.trim(),
    })
    result.value = data
  } catch (err) {
    const status = err.response?.status
    error.value = status === 429
      ? 'Too many attempts. Please wait a moment before trying again.'
      : (err.response?.data?.message ?? 'Something went wrong. Please try again.')
  } finally {
    loading.value = false
  }
}

function reset() {
  result.value = null
  form.reference_code = ''
  form.email = ''
}
</script>
