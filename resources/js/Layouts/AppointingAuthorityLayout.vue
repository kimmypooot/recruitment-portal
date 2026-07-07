<template>
  <div>
    <a href="#main-content"
      class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-primary focus:text-white focus:rounded-lg focus:text-sm focus:font-semibold focus:outline-none">
      Skip to content
    </a>

  <div class="min-h-screen bg-gray-50">
    <!-- Top bar -->
    <header class="bg-primary text-white shadow-md">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
          <div class="flex items-center gap-3">
            <img src="/images/csc-logo.png" alt="CSC Logo" class="h-8 w-8 object-contain"
              onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
            <div class="leading-tight">
              <p class="text-sm font-bold">CSC RO VIII</p>
              <p class="text-[10px] text-amber-300/80 uppercase tracking-wider font-medium">Appointing Authority</p>
            </div>
          </div>
          <button @click="showLogoutModal = true"
            class="text-xs text-white/60 hover:text-white flex items-center gap-1.5 bg-white/8 hover:bg-white/15 px-3 py-1.5 rounded-lg transition-all duration-200">
            <Icon name="logout" class="w-3.5 h-3.5" />
            Sign Out
          </button>
        </div>
        <nav class="flex gap-4 -mb-px">
          <Link href="/appointing-authority/dashboard"
            class="pb-2.5 text-sm font-medium border-b-2 transition-colors duration-200"
            :class="isActive('/appointing-authority/dashboard')
              ? 'text-white border-amber-400'
              : 'text-white/50 border-transparent hover:text-white/80 hover:border-white/20'">
            Dashboard
          </Link>
          <Link v-for="v in assignedVacancies" :key="v.id"
            :href="`/appointing-authority/${v.id}`"
            class="pb-2.5 text-sm font-medium border-b-2 transition-colors duration-200 truncate max-w-[180px]"
            :class="isActive(`/appointing-authority/${v.id}`)
              ? 'text-white border-amber-400'
              : 'text-white/50 border-transparent hover:text-white/80 hover:border-white/20'">
            {{ v.position_title }}
          </Link>
        </nav>
      </div>
    </header>

    <!-- Main -->
    <main id="main-content" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6" tabindex="-1">
      <slot />
    </main>

    <!-- Logout modal -->
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

    <!-- Sign-out preload overlay -->
    <AuthSplashOverlay :visible="showSignOutPreload">
      <p class="text-xl font-semibold text-primary">Signing you out</p>
      <p class="text-gray-500 text-sm">Please wait a moment…</p>
    </AuthSplashOverlay>
  </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import api from '@/services/api'
import axios from 'axios'
import BaseModal from '@/Components/UI/BaseModal.vue'
import Icon from '@/Components/UI/Icon.vue'
import AuthSplashOverlay from '@/Components/UI/AuthSplashOverlay.vue'
import { navigateTo } from '@/utils/navigate'

const page = usePage()
const showLogoutModal = ref(false)
const showSignOutPreload = ref(false)
const assignedVacancies = ref([])

function isActive(href) {
  return page.url.startsWith(href)
}

function logout() {
  showLogoutModal.value = true
}

async function confirmLogout() {
  showLogoutModal.value    = false
  showSignOutPreload.value = true

  const token = localStorage.getItem('auth_token')

  await Promise.allSettled([
    axios.post('/api/logout', {}, { headers: { Authorization: `Bearer ${token}` } }),
    new Promise(r => setTimeout(r, 900)),
  ])

  localStorage.removeItem('auth_token')
  localStorage.removeItem('auth_user')
  navigateTo('/login')
}

async function loadVacancies() {
  try {
    const { data } = await api.get('/hrmpsb/pipeline-stages', { params: { vacancy_ids: '__all__' } })
    const ids = Object.entries(data).filter(([, s]) => s.deliberation_exists).map(([id]) => id)
    if (ids.length === 0) return
    const { data: vacs } = await api.get('/vacancies', { params: { ids: ids.join(','), per_page: 50 } })
    assignedVacancies.value = (vacs.data ?? vacs ?? []).map(v => ({ id: v.id, position_title: v.position_title }))
  } catch { /* silently fail */ }
}

onMounted(loadVacancies)
</script>
