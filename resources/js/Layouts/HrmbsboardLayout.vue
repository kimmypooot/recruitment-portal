<template>
  <div class="min-h-screen flex bg-gray-100">

    <!-- Sidebar -->
    <aside
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      class="fixed inset-y-0 left-0 z-50 w-64 text-white flex flex-col transition-transform duration-200 lg:relative lg:translate-x-0 lg:flex-shrink-0"
      style="background-color: #1a5276;">

      <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
        <img src="/images/csc-logo.png" alt="CSC Logo" class="h-9 w-9 object-contain flex-shrink-0"
          onerror="this.style.display='none'" />
        <div class="leading-tight">
          <p class="text-sm font-bold text-white">CSC Regional Office</p>
          <p class="text-xs text-white/60">HRMPSB Portal</p>
        </div>
      </div>

      <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <p class="text-xs font-semibold text-white/50 uppercase tracking-wider px-3 mb-2 mt-1">Evaluation</p>
        <Link
          v-for="item in navItems" :key="item.href"
          :href="item.href"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
          :class="isActive(item.href) ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white'">
          <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
          </svg>
          {{ item.label }}
        </Link>
      </nav>

      <div class="px-3 py-4 border-t border-white/10">
        <button @click="logout" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10 hover:text-white transition-colors">
          <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          Sign out
        </button>
      </div>
    </aside>

    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden" />

    <div class="flex-1 flex flex-col min-w-0">
      <header class="bg-white border-b border-gray-200 px-4 sm:px-6 h-16 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
          <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          <h1 class="text-base font-semibold text-gray-900">{{ props2.title }}</h1>
        </div>
      </header>

      <main class="flex-1 overflow-auto p-4 sm:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props2 = defineProps({
  title: { type: String, default: 'HRMPSB' },
  vacancyId: { type: Number, default: null },
})

const sidebarOpen = ref(false)
const page = usePage()

const navItems = computed(() => {
  const base = [
    {
      label: 'My Assignments',
      href: '/hrmpsb/dashboard',
      icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
  ]

  if (props2.vacancyId) {
    const v = props2.vacancyId
    base.push(
      { label: 'QS Evaluation',  href: `/hrmpsb/qs-evaluation/${v}`,  icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
      { label: 'QS Results',     href: `/hrmpsb/qs-results/${v}`,     icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
      { label: 'Exam Results',   href: `/hrmpsb/exam-results/${v}`,   icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
      { label: 'BEI Rating',     href: `/hrmpsb/bei-rating/${v}`,     icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' },
      { label: 'Deliberation',   href: `/hrmpsb/deliberation/${v}`,   icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    )
  }

  return base
})

function isActive(href) {
  return page.url.startsWith(href)
}

function logout() {
  localStorage.removeItem('auth_token')
  router.visit('/login')
}
</script>
