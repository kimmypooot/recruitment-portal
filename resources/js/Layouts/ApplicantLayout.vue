<template>
  <div>
    <a href="#main-content"
      class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-primary focus:text-white focus:rounded-lg focus:text-sm focus:font-semibold focus:outline-none">
      Skip to content
    </a>

  <div class="min-h-screen bg-gray-100 transition-all duration-500" :class="sidebarCollapsed ? '' : 'lg:pl-64'">

    <!-- Backdrop (mobile) -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden" />

    <!-- Sidebar -->
    <aside
      :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:-translate-x-full' : 'lg:translate-x-0']"
      class="fixed inset-y-0 left-0 z-[60] w-64 text-white flex flex-col transition-transform duration-200 bg-primary">

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
                <Icon :name="item.icon" size="4" class="flex-shrink-0" />
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
                <Icon :name="item.icon" size="4" class="flex-shrink-0" />
                {{ item.label }}
              </Link>
            </template>
          </div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="px-3 py-4 border-t border-white/10">
        <button @click="sidebarOpen = false; showLogoutModal = true" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10 hover:text-white transition-colors">
          <Icon name="logout" size="4" class="flex-shrink-0" />
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
            <Icon name="menu" class="w-5 h-5" />
          </button>
          <h1 class="text-base font-semibold text-gray-900">Applicant Portal</h1>
        </div>

        <div class="flex items-center gap-2">
          <NotificationBell />
          <div class="relative" ref="dropdownRef">
            <button @click="dropdownOpen = !dropdownOpen"
              class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
              <div class="relative w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-xs font-bold flex-shrink-0 overflow-hidden">
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
                class="absolute right-0 mt-1 w-48 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50">
                <div class="py-1">
                  <button @click="dropdownOpen = false; showChangePasswordModal = true"
                    class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <Icon name="lock" size="4" class="flex-shrink-0 text-gray-400" />
                    Change Password
                  </button>
                  <hr class="my-1 border-gray-100" />
                  <button @click="dropdownOpen = false; showLogoutModal = true"
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
      <main id="main-content" class="flex-1" tabindex="-1">
        <slot />
      </main>

      <AppFooter />

    </div>

    <!-- Back to top button -->
    <button v-if="showBackToTop" @click="scrollToTop"
      class="fixed bottom-6 right-6 z-[60] w-10 h-10 rounded-full bg-primary text-white shadow-lg hover:bg-primary-dark flex items-center justify-center transition-all duration-200">
      <Icon name="chevronUp" class="w-5 h-5" />
    </button>

    <!-- Change Password modal -->
    <Teleport to="body">
      <div v-if="showChangePasswordModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="closeChangePassword"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
              <Icon name="lock" class="w-5 h-5 text-primary" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900">{{ isGoogleOnly ? 'Set a Password' : 'Change Password' }}</h3>
              <p class="text-xs text-gray-500">Min. 8 characters, uppercase, lowercase & number</p>
            </div>
          </div>

          <div v-if="isGoogleOnly" class="mb-4 flex items-start gap-2 px-3 py-2.5 rounded-lg bg-blue-50 border border-blue-200">
            <Icon name="info" class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" />
            <p class="text-xs text-blue-700">Your account was created via Google. Set a password to also sign in with your email and password.</p>
          </div>

          <div class="space-y-4">
            <div v-if="!isGoogleOnly">
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Current Password</label>
              <div class="relative">
                <input v-model="cpForm.current_password" :type="showCurrent ? 'text' : 'password'" placeholder="Enter current password"
                  class="w-full pl-3 pr-10 py-2.5 rounded-lg border text-sm focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition"
                  :class="cpErrors.current_password ? 'border-red-400' : 'border-gray-300'" />
                <button type="button" @click="showCurrent = !showCurrent"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                  <Icon v-if="showCurrent" name="eyeOff" size="4" />
                  <Icon v-else name="eye" size="4" />
                </button>
              </div>
              <p v-if="cpErrors.current_password" class="mt-1 text-xs text-red-500">{{ cpErrors.current_password }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">New Password</label>
              <div class="relative">
                <input v-model="cpForm.password" :type="showNew ? 'text' : 'password'" placeholder="Enter new password"
                  class="w-full pl-3 pr-10 py-2.5 rounded-lg border text-sm focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition"
                  :class="cpErrors.password ? 'border-red-400' : 'border-gray-300'" />
                <button type="button" @click="showNew = !showNew"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                  <Icon v-if="showNew" name="eyeOff" size="4" />
                  <Icon v-else name="eye" size="4" />
                </button>
              </div>
              <p v-if="cpErrors.password" class="mt-1 text-xs text-red-500">{{ cpErrors.password }}</p>

              <!-- Password requirements -->
              <div class="mt-2 space-y-1" aria-live="polite">
                <p v-for="req in passwordRequirements" :key="req.label"
                  class="flex items-center gap-1.5 text-xs transition-colors"
                  :class="req.met ? 'text-green-600' : 'text-gray-400'">
                  <Icon v-if="req.met" name="check" size="3.5" class="flex-shrink-0" />
                  <svg v-else class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                  </svg>
                  {{ req.label }}
                </p>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Confirm New Password</label>
              <div class="relative">
                <input v-model="cpForm.password_confirmation" :type="showConfirm ? 'text' : 'password'" placeholder="Repeat new password"
                  class="w-full pl-3 pr-10 py-2.5 rounded-lg border text-sm focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition"
                  :class="cpErrors.password_confirmation ? 'border-red-400' : 'border-gray-300'" />
                <button type="button" @click="showConfirm = !showConfirm"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                  <Icon v-if="showConfirm" name="eyeOff" size="4" />
                  <Icon v-else name="eye" size="4" />
                </button>
              </div>
              <p v-if="cpErrors.password_confirmation" class="mt-1 text-xs text-red-500">{{ cpErrors.password_confirmation }}</p>
            </div>
          </div>

          <p v-if="cpSuccess" role="alert" class="mt-4 text-sm text-green-600 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
            {{ cpSuccess }}
          </p>
          <p v-if="cpGeneralError" role="alert" class="mt-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
            {{ cpGeneralError }}
          </p>

          <div class="flex gap-3 mt-6">
            <button @click="closeChangePassword"
              class="flex-1 py-2.5 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
              Cancel
            </button>
            <button @click="submitChangePassword" :disabled="cpSaving"
              class="flex-1 py-2.5 text-sm bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors font-semibold disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
              <svg v-if="cpSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ cpSaving ? 'Saving…' : 'Update Password' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Logout confirmation modal -->
    <Teleport to="body">
      <div v-if="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showLogoutModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
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
import { ref, computed, provide, onMounted, onBeforeUnmount } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import Icon from '@/Components/UI/Icon.vue'
import NotificationBell from '@/Components/UI/NotificationBell.vue'
import AppFooter from '@/Components/UI/AppFooter.vue'
import { useIdleTimer } from '@/composables/useIdleTimer'
import { navigateTo } from '@/utils/navigate'

useIdleTimer()

const sidebarOpen       = ref(false)
const sidebarCollapsed  = ref(localStorage.getItem('sidebar_collapsed') === 'true')

provide('sidebarCollapsed', sidebarCollapsed)
const dropdownOpen      = ref(false)
const dropdownRef       = ref(null)
const showBackToTop     = ref(false)
const showLogoutModal         = ref(false)
const showChangePasswordModal = ref(false)
const showSignOutPreload  = ref(false)
const soDot               = ref(0)
let soTimer = null

const cpForm = ref({ current_password: '', password: '', password_confirmation: '' })
const cpErrors = ref({})
const cpGeneralError = ref('')
const cpSuccess = ref('')
const cpSaving = ref(false)
const showCurrent = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)

const passwordRequirements = computed(() => {
  const p = cpForm.value.password
  return [
    { label: 'At least 8 characters',      met: p.length >= 8 },
    { label: 'At least one uppercase letter', met: /[A-Z]/.test(p) },
    { label: 'At least one lowercase letter', met: /[a-z]/.test(p) },
    { label: 'At least one number',          met: /[0-9]/.test(p) },
  ]
})

function closeChangePassword() {
  showChangePasswordModal.value = false
  cpForm.value = { current_password: '', password: '', password_confirmation: '' }
  cpErrors.value = {}
  cpGeneralError.value = ''
  cpSuccess.value = ''
  showCurrent.value = false
  showNew.value = false
  showConfirm.value = false
}

async function submitChangePassword() {
  cpErrors.value = {}
  cpGeneralError.value = ''
  cpSuccess.value = ''

  if (!isGoogleOnly.value && !cpForm.value.current_password) { cpErrors.value.current_password = 'Current password is required.'; return }
  if (!cpForm.value.password) { cpErrors.value.password = 'New password is required.'; return }
  if (cpForm.value.password !== cpForm.value.password_confirmation) {
    cpErrors.value.password_confirmation = 'Passwords do not match.'
    return
  }

  cpSaving.value = true
  try {
    await axios.post('/api/change-password', cpForm.value, {
      headers: { Authorization: `Bearer ${authToken.value}` },
    })
    cpSuccess.value = 'Password updated successfully.'
    cpForm.value = { current_password: '', password: '', password_confirmation: '' }
    setTimeout(() => closeChangePassword(), 1500)
  } catch (e) {
    const data = e.response?.data
    if (data?.errors) {
      cpErrors.value = Object.fromEntries(
        Object.entries(data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
    } else {
      cpGeneralError.value = data?.message ?? 'Failed to change password.'
    }
  } finally {
    cpSaving.value = false
  }
}
const page              = usePage()
const authToken      = ref('')
const authUser       = ref({})

const profileComplete = ref(localStorage.getItem('profile_complete') === 'true')

function refreshProfileStatus() {
  profileComplete.value = localStorage.getItem('profile_complete') === 'true'
}

const userName    = computed(() => authUser.value?.full_name ?? 'Applicant')
const userInitial = computed(() => (authUser.value?.full_name ?? 'A')[0].toUpperCase())
const isGoogleOnly = computed(() => !!authUser.value?.google_id)

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
        icon:  'grid',
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
        icon:  'document',
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
        icon:  'user',
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
