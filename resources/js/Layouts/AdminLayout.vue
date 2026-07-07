<template>
  <div v-if="authorized">
    <a href="#main-content"
      class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-primary focus:text-white focus:rounded-lg focus:text-sm focus:font-semibold focus:outline-none">
      Skip to content
    </a>

  <div class="min-h-screen bg-gray-100 transition-all duration-500" :class="sidebarCollapsed ? '' : 'lg:pl-64'">

    <!-- Sidebar -->
    <aside
      :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:-translate-x-full' : 'lg:translate-x-0']"
      class="fixed inset-y-0 left-0 z-50 w-64 text-white flex flex-col transition-transform duration-200 bg-primary">

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
          <p class="text-xs text-white/60">Admin Portal</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav flex-1 px-3 py-4 overflow-y-auto space-y-4">
        <div v-for="group in navGroups" :key="group.label">
          <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-3 mb-1.5">
            {{ group.label }}
          </p>
          <div class="space-y-0.5">
            <Link
              v-for="item in group.items" :key="item.href"
              :href="item.href"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
              :class="isActive(item.href)
                ? 'bg-white/15 text-white'
                : 'text-white/75 hover:bg-white/10 hover:text-white'">
              <Icon :name="item.icon" size="4" class="flex-shrink-0" />
              {{ item.label }}
            </Link>
          </div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="px-3 py-4 border-t border-white/10">
        <button v-if="canSwitchToHrmpsb" @click="showWorkspaceSwitch = true" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10 hover:text-white transition-colors mb-1">
          <Icon name="user" size="4" class="flex-shrink-0" />
          Switch to HRMPSB
        </button>
        <button @click="logout" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10 hover:text-white transition-colors mt-0.5">
          <Icon name="logout" size="4" class="flex-shrink-0" />
          Sign out
        </button>
      </div>
    </aside>

    <!-- Backdrop (mobile) -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden" />

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

      <!-- Top bar -->
      <header class="sticky top-0 z-40 bg-white border-b border-gray-200 px-4 sm:px-6 h-16 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3 min-w-0">
          <button @click="toggleSidebar" aria-label="Toggle sidebar" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 flex-shrink-0">
            <Icon name="menu" class="w-5 h-5" />
          </button>
          <h1 class="text-base font-semibold text-gray-900">{{ title }}</h1>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative" ref="dropdownRef">
            <button @click="dropdownOpen = !dropdownOpen"
              class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
              <div class="relative w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-xs font-bold flex-shrink-0 overflow-hidden">
                <div v-if="avatarLoading" class="absolute inset-0 flex items-center justify-center">
                  <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                </div>
                <span v-if="avatarError">{{ userInitial }}</span>
                <img v-if="authToken" :src="`/profile/photo?token=${authToken}&_=${avatarVersion}`"
                  class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300"
                  :class="avatarLoaded ? 'opacity-100' : 'opacity-0'"
                  @load="e => { if (e.target.naturalWidth === 1 && e.target.naturalHeight === 1) { avatarLoading = false; avatarError = true } else { avatarLoaded = true; avatarLoading = false; avatarError = false } }"
                  @error="e => { e.target.style.display = 'none'; avatarLoading = false; avatarError = true }"
                  alt="" />
              </div>
              <div class="hidden sm:block text-left">
                <p class="text-sm font-semibold text-gray-800 leading-none">{{ userName }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ roleLabel(authUser.role) }}</p>
              </div>
              <Icon name="chevronDown" class="w-4 h-4 text-gray-400 hidden sm:block transition-transform flex-shrink-0"
                :class="dropdownOpen ? 'rotate-180' : ''" />
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
                  <button @click="logout"
                    class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <Icon name="logout" size="4" class="flex-shrink-0" />
                    Sign out
                  </button>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main id="main-content" class="flex-1 p-4 sm:p-6" tabindex="-1">
        <div class="mx-auto w-full max-w-7xl">
          <slot />
        </div>
      </main>

      <AppFooter />

    </div>

    <!-- Logout confirmation modal -->
    <BaseModal :show="showLogoutModal" title="Sign out" max-width="max-w-sm" @close="showLogoutModal = false">
      <div class="text-center">
          <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
            <Icon name="logout" class="w-6 h-6 text-red-500" />
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
    </BaseModal>

    <WorkspaceSwitcher :show="showWorkspaceSwitch" target="hrmpsb" />

    <!-- Sign-out preload overlay -->
    <AuthSplashOverlay :visible="showSignOutPreload">
      <p class="text-xl font-semibold mb-1 text-primary">Signing you out</p>
      <p class="text-gray-500 text-sm">See you next time!</p>
    </AuthSplashOverlay>

  </div>
  </div>

  <div v-else class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-10 h-10 border-4 border-gray-200 border-t-primary rounded-full animate-spin"></div>
  </div>
</template>

<style scoped>
.sidebar-nav::-webkit-scrollbar { width: 5px; }
.sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }
.sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }
.sidebar-nav { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.15) transparent; }
</style>

<script setup>
import { ref, computed, watch, provide, onMounted, onBeforeUnmount } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import BaseModal from '@/Components/UI/BaseModal.vue'
import Icon from '@/Components/UI/Icon.vue'
import { roleLabel } from '@/utils/roleLabel'
import { navigateTo } from '@/utils/navigate'
import AppFooter from '@/Components/UI/AppFooter.vue'
import WorkspaceSwitcher from '@/Components/UI/WorkspaceSwitcher.vue'
import AuthSplashOverlay from '@/Components/UI/AuthSplashOverlay.vue'
import { useIdleTimer } from '@/composables/useIdleTimer'

useIdleTimer()

defineProps({ title: { type: String, default: 'Dashboard' } })

const sidebarOpen       = ref(false)
const sidebarCollapsed  = ref(localStorage.getItem('sidebar_collapsed') === 'true')
provide('sidebarCollapsed', sidebarCollapsed)
const dropdownOpen      = ref(false)
const dropdownRef       = ref(null)
const showLogoutModal     = ref(false)
const showWorkspaceSwitch = ref(false)
const showSignOutPreload  = ref(false)
const page              = usePage()
const authToken      = ref('')
const authUser       = ref({})
const avatarLoaded   = ref(false)
const avatarLoading  = ref(true)
const avatarError    = ref(false)
const avatarVersion  = ref(Date.now())
watch(authToken, (token) => {
  if (token) { avatarLoading.value = true; avatarLoaded.value = false; avatarError.value = false }
})

function refreshAvatar() {
  avatarVersion.value = Date.now()
  avatarLoading.value = true
  avatarLoaded.value  = false
  avatarError.value   = false
}
const myRole         = ref(null)
const authorized     = ref(false)

const userName    = computed(() => authUser.value?.full_name ?? 'Admin')
const userEmail   = computed(() => authUser.value?.email ?? '')
const userInitial = computed(() => (authUser.value?.full_name ?? 'A')[0].toUpperCase())
const canSwitchToHrmpsb = computed(() => {
  const role = authUser.value?.role
  return role === 'admin' || role === 'hrmpsb'
})

function toggleSidebar() {
  if (window.innerWidth >= 1024) {
    sidebarCollapsed.value = !sidebarCollapsed.value
    localStorage.setItem('sidebar_collapsed', sidebarCollapsed.value)
  } else {
    sidebarOpen.value = !sidebarOpen.value
  }
}

function handleClickOutside(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    dropdownOpen.value = false
  }
}

function handleKeydown(e) {
  if (e.key === 'Escape') {
    dropdownOpen.value = false
    showLogoutModal.value = false
    showWorkspaceSwitch.value = false
  }
}

// Mirrors the backend's User::canAccessAdminModule() — admin, or an hrmpsb
// user with a secretariat/hr-chief designation. Everyone else (including
// applicants) is redirected before any admin content or nav renders.
onMounted(async () => {
  authToken.value = localStorage.getItem('auth_token') ?? ''
  authUser.value  = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
  window.addEventListener('auth-avatar-updated', refreshAvatar)

  if (!authToken.value) {
    navigateTo('/login')
    return
  }

  const role = authUser.value?.role

  if (role === 'admin') {
    authorized.value = true
    return
  }

  if (role === 'hrmpsb') {
    try {
      const { data } = await axios.get('/api/hrmpsb/my-role', {
        headers: { Authorization: `Bearer ${authToken.value}` }
      })
      myRole.value = data.composition
    } catch {
      // Board role not available — treated as unauthorized below
    }

    if (myRole.value && ['secretariat', 'hr-chief'].includes(myRole.value.hrmpsb_role)) {
      authorized.value = true
      return
    }

    navigateTo('/hrmpsb/dashboard')
    return
  }

  navigateTo(role === 'applicant' ? '/applicant/dashboard' : '/login')
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
  window.removeEventListener('auth-avatar-updated', refreshAvatar)
})

const navGroups = computed(() => {
  const role = authUser.value?.role
  const allGroups = [
    {
      label: 'Overview',
      items: [
        { label: 'Dashboard', href: '/admin/dashboard', icon: 'grid' },
      ],
    },
    {
      label: 'Recruitment',
      items: [
        { label: 'Vacancies', href: '/admin/vacancies', icon: 'briefcase' },
        { label: 'Applications', href: '/admin/applications', icon: 'document' },
        { label: 'Feedbacks', href: '/admin/feedbacks', icon: 'star' },
        { label: 'HRMPSB', href: '/admin/hrmpsb', icon: 'user' },
      ],
    },
    {
      label: 'Administration',
      items: [
        { label: 'Users', href: '/admin/users', icon: 'shield' },
        { label: 'Reports', href: '/admin/reports', icon: 'document' },
      ],
    },
    {
      label: 'Configuration',
      items: [
        { label: 'Competencies', href: '/admin/competencies', icon: 'check' },
        { label: 'Email Templates', href: '/admin/email-templates', icon: 'mail' },
      ],
    },
    {
      label: 'Records',
      items: [
        { label: 'Audit Logs', href: '/admin/audit-logs', icon: 'clock' },
      ],
    },
  ]

  return allGroups
})

function isActive(href) {
  return page.url.startsWith(href)
}

function logout() {
  sidebarOpen.value = false
  showLogoutModal.value = true
}

async function confirmLogout() {
  showLogoutModal.value    = false
  showSignOutPreload.value = true

  // API call and minimum display time run in parallel
  await Promise.allSettled([
    axios.post('/api/logout', {}, { headers: { Authorization: `Bearer ${authToken.value}` } }),
    new Promise(r => setTimeout(r, 900)),
  ])

  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  localStorage.removeItem('auth_remember')
  navigateTo('/login')
}
</script>
