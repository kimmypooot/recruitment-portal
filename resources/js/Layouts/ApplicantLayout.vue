<template>
  <div class="min-h-screen bg-gray-100 transition-all duration-500" :class="sidebarCollapsed ? '' : 'lg:pl-64'">

    <!-- Backdrop (mobile) -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden" />

    <!-- Sidebar -->
    <aside
      :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:-translate-x-full' : 'lg:translate-x-0']"
      class="fixed inset-y-0 left-0 z-50 w-64 text-white flex flex-col transition-transform duration-200"
      style="background-color: #2a338f;">

      <!-- Logo -->
      <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
        <img src="/images/csc-logo.png" alt="CSC Logo" class="h-9 w-9 object-contain flex-shrink-0"
          onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
        <div class="w-9 h-9 rounded-lg bg-white/15 items-center justify-center flex-shrink-0 hidden">
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
          </svg>
        </div>
        <div class="leading-tight">
          <p class="text-sm font-bold text-white">CSC RO VIII</p>
          <p class="text-xs text-white/60">Recruitment Portal</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-4">
        <div v-for="group in navGroups" :key="group.label">
          <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-3 mb-1.5">
            {{ group.label }}
          </p>
          <div class="space-y-0.5">
            <template v-for="item in group.items" :key="item.href">
              <span
                v-if="item.disabled && !profileComplete"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/30 cursor-not-allowed">
                <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
                </svg>
                {{ item.label }}
              </span>
              <Link
                v-else
                :href="item.href"
                @click="sidebarOpen = false"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                :class="isActive(item.href)
                  ? 'bg-white/15 text-white'
                  : 'text-white/75 hover:bg-white/10 hover:text-white'">
                <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
                </svg>
                {{ item.label }}
              </Link>
            </template>
          </div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="px-3 py-4 border-t border-white/10">
        <button @click="showLogoutModal = true" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10 hover:text-white transition-colors">
          <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          Sign out
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <div class="flex flex-col min-h-screen">

      <!-- Top bar -->
      <header class="sticky top-0 z-40 bg-white border-b border-gray-200 px-4 sm:px-6 h-16 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
          <button @click="toggleSidebar" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          <h1 class="text-base font-semibold text-gray-900">Applicant Portal</h1>
        </div>

        <div class="flex items-center gap-2">
          <NotificationBell />
          <div class="relative" ref="dropdownRef">
            <button @click="dropdownOpen = !dropdownOpen"
              class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
              <div class="relative w-8 h-8 rounded-full bg-[#2a338f] flex items-center justify-center text-white text-xs font-bold flex-shrink-0 overflow-hidden">
                <span>{{ userInitial }}</span>
                <img :src="`/profile/photo?token=${authToken}`"
                  class="absolute inset-0 w-full h-full object-cover"
                  @error="e => e.target.style.display = 'none'"
                  alt="" />
              </div>
              <div class="hidden sm:block text-left">
                <p class="text-sm font-semibold text-gray-800 leading-none">{{ userName }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Applicant</p>
              </div>
              <svg class="w-4 h-4 text-gray-400 hidden sm:block transition-transform flex-shrink-0"
                :class="dropdownOpen ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
              </svg>
            </button>

            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95">
              <div v-if="dropdownOpen"
                class="absolute right-0 mt-1 w-40 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50">
                <div class="py-1">
                  <button @click="dropdownOpen = false; showLogoutModal = true"
                    class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign out
                  </button>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 pb-14">
        <slot />
      </main>

    </div>

    <AppFooter :sidebar-collapsed="sidebarCollapsed" />

    <!-- Back to top button -->
    <button v-if="showBackToTop" @click="scrollToTop"
      class="fixed bottom-6 right-6 z-40 w-10 h-10 rounded-full bg-[#2a338f] text-white shadow-lg hover:bg-[#1e2570] flex items-center justify-center transition-all duration-200">
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/>
      </svg>
    </button>

    <!-- Logout confirmation modal -->
    <Teleport to="body">
      <div v-if="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showLogoutModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
          <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-gray-900 mb-1">Sign out</h3>
          <p class="text-sm text-gray-500 mb-6">Are you sure you want to sign out of your account?</p>
          <div class="flex gap-3">
            <button @click="showLogoutModal = false"
              class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
              Cancel
            </button>
            <button @click="confirmLogout"
              class="flex-1 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-semibold">
              Sign out
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Sign-out preload overlay -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
                  leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showSignOutPreload" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-hidden"
          style="background: linear-gradient(135deg, #f0eef9 0%, #e8eafa 50%, #fdeef0 100%);">
          <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-20 -left-20 w-72 h-72 rounded-full opacity-20"
              style="background: radial-gradient(circle, #2a338f 0%, transparent 70%); animation: float 8s ease-in-out infinite;"></div>
            <div class="absolute -bottom-16 -right-16 w-96 h-96 rounded-full opacity-15"
              style="background: radial-gradient(circle, #ec1c2d 0%, transparent 70%); animation: float 10s ease-in-out infinite reverse;"></div>
          </div>
          <div class="relative bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/40 p-10 text-center max-w-sm w-full mx-4">
            <div class="relative w-28 h-28 mx-auto mb-6">
              <svg class="absolute inset-0 w-28 h-28 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="#e5e7eb" stroke-width="2.5"/>
                <circle cx="12" cy="12" r="10" stroke="#2a338f" stroke-width="2.5"
                  stroke-linecap="round" stroke-dasharray="62.832" stroke-dashoffset="20"/>
              </svg>
              <svg class="absolute inset-2 w-[96px] h-[96px] animate-spin" style="animation-duration:2s;animation-direction:reverse;" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="8" stroke="#e5e7eb" stroke-width="1.5"/>
                <circle cx="12" cy="12" r="8" stroke="#ec1c2d" stroke-width="1.5"
                  stroke-linecap="round" stroke-dasharray="50.265" stroke-dashoffset="15"/>
              </svg>
              <img src="/images/csc-logo.png" alt="CSC"
                class="absolute w-12 h-12 rounded-full bg-white shadow-sm object-contain p-1.5"
                style="top:50%;left:50%;transform:translate(-50%,-50%);"
                @error="e => e.target.style.display='none'" />
            </div>
            <p class="text-xl font-semibold mb-1" style="color:#2a338f;">Signing you out</p>
            <p class="text-gray-500 text-sm">See you next time!</p>
            <div class="flex justify-center gap-1.5 mt-6">
              <span v-for="i in 3" :key="i" class="w-2 h-2 rounded-full transition-all duration-300"
                :style="{ backgroundColor: soDot === i - 1 ? '#ec1c2d' : '#2a338f',
                          transform: soDot === i - 1 ? 'scale(1.25)' : 'scale(1)' }"></span>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<style scoped>
@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33%       { transform: translate(30px, -30px) scale(1.05); }
  66%       { transform: translate(-20px, 20px) scale(0.95); }
}
</style>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import NotificationBell from '@/Components/UI/NotificationBell.vue'
import AppFooter from '@/Components/UI/AppFooter.vue'
import { useIdleTimer } from '@/composables/useIdleTimer'
import { navigateTo } from '@/utils/navigate'

useIdleTimer()

const sidebarOpen       = ref(false)
const sidebarCollapsed  = ref(false)
const dropdownOpen      = ref(false)
const dropdownRef       = ref(null)
const showBackToTop     = ref(false)
const showLogoutModal     = ref(false)
const showSignOutPreload  = ref(false)
const soDot               = ref(0)
let soTimer = null
const page              = usePage()
const authToken      = ref('')
const authUser       = ref({})

const profileComplete = ref(localStorage.getItem('profile_complete') === 'true')

function refreshProfileStatus() {
  profileComplete.value = localStorage.getItem('profile_complete') === 'true'
}

const userName    = computed(() => authUser.value?.name ?? 'Applicant')
const userInitial = computed(() => (authUser.value?.name ?? 'A')[0].toUpperCase())

function disabledHint(item) {
  if (item.label === 'My Profile') return false
  return !profileComplete.value
}

const navGroups = [
  {
    label: 'Overview',
    items: [
      {
        label: 'Dashboard',
        href:  '/applicant/dashboard',
        icon:  'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zm0 9.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zm0 9.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
        disabled: true,
      },
    ],
  },
  {
    label: 'Applications',
    items: [
      {
        label: 'My Applications',
        href:  '/applicant/applications',
        icon:  'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        disabled: true,
      },
    ],
  },
  {
    label: 'Profile',
    items: [
      {
        label: 'My Profile',
        href:  '/applicant/complete-profile',
        icon:  'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z',
        disabled: false,
      },
    ],
  },
]

function isActive(href) {
  return page.url.startsWith(href)
}

function toggleSidebar() {
  if (window.innerWidth >= 1024) {
    sidebarCollapsed.value = !sidebarCollapsed.value
  } else {
    sidebarOpen.value = !sidebarOpen.value
  }
}

function handleClickOutside(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    dropdownOpen.value = false
  }
}

function handleScroll() {
  showBackToTop.value = window.scrollY > 300
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function confirmLogout() {
  showLogoutModal.value    = false
  dropdownOpen.value       = false
  sidebarOpen.value        = false
  showSignOutPreload.value = true
  soTimer = setInterval(() => { soDot.value = (soDot.value + 1) % 3 }, 400)

  // API call and minimum display time run in parallel
  await Promise.allSettled([
    axios.post('/api/logout', {}, { headers: { Authorization: `Bearer ${authToken.value}` } }),
    new Promise(r => setTimeout(r, 900)),
  ])

  clearInterval(soTimer)
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  navigateTo('/login')
}

onMounted(() => {
  authToken.value = localStorage.getItem('auth_token') ?? ''
  authUser.value  = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
  refreshProfileStatus()
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('storage', refreshProfileStatus)
  window.addEventListener('profile-complete-changed', refreshProfileStatus)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('storage', refreshProfileStatus)
  window.removeEventListener('profile-complete-changed', refreshProfileStatus)
})
</script>
