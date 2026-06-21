<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">

    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
      <div class="h-1 w-full bg-[#ec1c2d]"></div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 flex-shrink-0">
          <img src="/images/csc-logo.png" alt="CSC Logo" class="h-9 w-9 object-contain flex-shrink-0"
            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
          <div class="w-9 h-9 rounded-lg bg-[#2a338f] items-center justify-center flex-shrink-0 hidden">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
          </div>
          <div class="leading-tight hidden sm:block">
            <p class="text-sm font-bold text-gray-900">CSC Regional Office</p>
            <p class="text-xs text-gray-500">Recruitment Portal</p>
          </div>
        </Link>

        <!-- Desktop nav links -->
        <nav class="hidden md:flex items-center gap-1 flex-1 justify-center">
          <Link v-for="link in navLinks" :key="link.href" :href="link.href"
            class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="isActive(link.href)
              ? 'bg-[#2a338f]/10 text-[#2a338f]'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="link.icon"/>
            </svg>
            {{ link.label }}
          </Link>
        </nav>

        <!-- Right: user button + mobile hamburger -->
        <div class="flex items-center gap-2 flex-shrink-0">

          <!-- User dropdown -->
          <div class="relative" ref="dropdownRef">
            <button @click="dropdownOpen = !dropdownOpen"
              class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
              <!-- Avatar -->
              <div class="relative w-8 h-8 rounded-full bg-[#2a338f] flex items-center justify-center flex-shrink-0 overflow-hidden">
                <span class="text-xs font-bold text-white select-none">{{ userInitial }}</span>
                <img :src="`/profile/photo?token=${authToken}`"
                  class="absolute inset-0 w-full h-full object-cover"
                  @error="e => e.target.style.display = 'none'"
                  alt="Profile" />
              </div>
              <div class="hidden sm:block text-left">
                <p class="text-sm font-semibold text-gray-800 leading-none max-w-[120px] truncate">{{ userName }}</p>
                <p class="text-xs text-gray-400 mt-0.5 max-w-[120px] truncate">Applicant</p>
              </div>
              <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0"
                :class="dropdownOpen ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
              </svg>
            </button>

            <!-- Dropdown -->
            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95">
              <div v-if="dropdownOpen"
                class="absolute right-0 mt-1 w-56 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50">

                <!-- User info header -->
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
                  <div class="relative w-9 h-9 rounded-full bg-[#2a338f] flex items-center justify-center flex-shrink-0 overflow-hidden">
                    <span class="text-sm font-bold text-white">{{ userInitial }}</span>
                    <img :src="`/profile/photo?token=${authToken}`"
                      class="absolute inset-0 w-full h-full object-cover"
                      @error="e => e.target.style.display = 'none'"
                      alt="Profile" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ userName }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ userEmail }}</p>
                  </div>
                </div>

                <!-- Mobile-only nav links (hidden on md+) -->
                <div class="md:hidden border-b border-gray-100 py-1">
                  <Link v-for="link in navLinks" :key="link.href" :href="link.href"
                    @click="dropdownOpen = false"
                    class="flex items-center gap-2.5 px-4 py-2 text-sm transition-colors"
                    :class="isActive(link.href)
                      ? 'text-[#2a338f] font-semibold bg-[#2a338f]/5'
                      : 'text-gray-700 hover:bg-gray-50'">
                    <svg class="w-4 h-4 flex-shrink-0" :class="isActive(link.href) ? 'text-[#2a338f]' : 'text-gray-400'"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" :d="link.icon"/>
                    </svg>
                    {{ link.label }}
                  </Link>
                </div>

                <!-- Sign out -->
                <div class="py-1">
                  <button @click="logout"
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
      </div>
    </header>

    <!-- Page content -->
    <main class="flex-1">
      <slot />
    </main>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page        = usePage()
const dropdownRef = ref(null)
const dropdownOpen = ref(false)
const authToken   = ref('')

const authUser = ref({})

const userName    = computed(() => authUser.value?.name ?? 'Applicant')
const userEmail   = computed(() => authUser.value?.email ?? '')
const userInitial = computed(() => (authUser.value?.name ?? 'A')[0].toUpperCase())

const navLinks = [
  {
    href: '/applicant/dashboard',
    label: 'Dashboard',
    icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  },
  {
    href: '/applicant/applications',
    label: 'My Applications',
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  },
  {
    href: '/applicant/complete-profile',
    label: 'My Profile',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
  },
]

function isActive(href) {
  if (href === '/') return page.url === '/'
  return page.url === href || page.url.startsWith(href + '?') || page.url.startsWith(href + '/')
}

function handleClickOutside(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    dropdownOpen.value = false
  }
}

function logout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  dropdownOpen.value = false
  router.visit('/login')
}

onMounted(() => {
  const token = localStorage.getItem('auth_token') ?? ''
  const user  = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
  authToken.value = token
  authUser.value  = user
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
