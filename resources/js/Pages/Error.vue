<template>
  <PublicLayout>
    <Head>
      <title>{{ title }}</title>
      <meta name="description" :content="title" />
    </Head>

    <div class="min-h-[70vh] flex items-center justify-center px-4 py-20">
      <div class="text-center max-w-lg">

        <!-- Status code graphic -->
        <div class="relative mb-8">
          <p class="text-[8rem] sm:text-[10rem] font-black leading-none select-none"
            :class="status === 404 ? 'text-primary/10' : 'text-accent/10'">
            {{ status }}
          </p>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center"
              :class="statusIconBg">
              <Icon :name="iconName" :size="10" :class="iconColor" />
            </div>
          </div>
        </div>

        <!-- Title -->
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">{{ title }}</h1>

        <!-- Description -->
        <p class="text-sm sm:text-base text-gray-500 leading-relaxed mb-8">{{ description }}</p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
          <Link href="/"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
            <Icon name="chevronLeft" :size="4" />
            Back to Home
          </Link>
          <button v-if="status === 429 || status === 500" @click="retry"
            class="inline-flex items-center gap-2 px-6 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold rounded-lg transition-colors">
            <Icon name="refresh" :size="4" />
            Try Again
          </button>
          <button v-if="status === 419" @click="refreshPage"
            class="inline-flex items-center gap-2 px-6 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold rounded-lg transition-colors">
            <Icon name="refresh" :size="4" />
            Refresh Page
          </button>
          <Link v-if="status === 403" href="/login"
            class="inline-flex items-center gap-2 px-6 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold rounded-lg transition-colors">
            <Icon name="logout" :size="4" />
            Sign In
          </Link>
        </div>

        <!-- Decorative links for 404 -->
        <div v-if="status === 404" class="mt-10">
          <p class="text-xs text-gray-400 mb-3">Try these pages instead:</p>
          <div class="flex flex-wrap justify-center gap-2">
            <Link href="/" class="px-3 py-1.5 text-xs font-medium text-primary bg-primary-light rounded-lg hover:bg-primary/20 transition-colors">
              Open Vacancies
            </Link>
            <Link href="/how-to-apply" class="px-3 py-1.5 text-xs font-medium text-primary bg-primary-light rounded-lg hover:bg-primary/20 transition-colors">
              How to Apply
            </Link>
            <Link href="/about" class="px-3 py-1.5 text-xs font-medium text-primary bg-primary-light rounded-lg hover:bg-primary/20 transition-colors">
              About CSC
            </Link>
            <Link href="/login" class="px-3 py-1.5 text-xs font-medium text-primary bg-primary-light rounded-lg hover:bg-primary/20 transition-colors">
              Sign In
            </Link>
          </div>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import Icon from '@/Components/UI/Icon.vue'

const props = defineProps({
  status: { type: Number, default: 404 },
})

const pages = {
  400: {
    title: 'Bad Request',
    description: 'The request could not be processed due to invalid syntax. Please check your input and try again.',
    iconName: 'alert',
    iconColor: 'text-accent',
    iconBg: 'bg-accent/10',
  },
  403: {
    title: 'Access Denied',
    description: 'You don\'t have permission to access this page. If you believe this is an error, please contact the system administrator.',
    iconName: 'lock',
    iconColor: 'text-accent',
    iconBg: 'bg-accent/10',
  },
  404: {
    title: 'Page Not Found',
    description: 'We couldn\'t find the page you\'re looking for. It may have been moved, renamed, or removed. Please check the URL or navigate using the links below.',
    iconName: 'search',
    iconColor: 'text-primary',
    iconBg: 'bg-primary/10',
  },
  419: {
    title: 'Session Expired',
    description: 'Your session has expired due to inactivity. Please refresh the page and sign in again to continue.',
    iconName: 'clock',
    iconColor: 'text-amber-600',
    iconBg: 'bg-amber-50',
  },
  429: {
    title: 'Too Many Requests',
    description: 'You\'ve made too many requests in a short period. Please wait a moment before trying again.',
    iconName: 'alert',
    iconColor: 'text-accent',
    iconBg: 'bg-accent/10',
  },
  500: {
    title: 'Server Error',
    description: 'Something went wrong on our end. Our team has been notified and is working on a fix. Please try again in a few minutes.',
    iconName: 'alert',
    iconColor: 'text-accent',
    iconBg: 'bg-accent/10',
  },
  503: {
    title: 'Service Unavailable',
    description: 'The system is currently undergoing maintenance. Please check back shortly. We apologize for the inconvenience.',
    iconName: 'clock',
    iconColor: 'text-primary',
    iconBg: 'bg-primary/10',
  },
}

const page = computed(() => pages[props.status] ?? pages[500])
const title = computed(() => page.value.title)
const description = computed(() => page.value.description)
const iconName = computed(() => page.value.iconName)
const iconColor = computed(() => page.value.iconColor)
const iconBg = computed(() => page.value.iconBg)

function retry() {
  window.location.reload()
}

function refreshPage() {
  window.location.reload()
}
</script>
