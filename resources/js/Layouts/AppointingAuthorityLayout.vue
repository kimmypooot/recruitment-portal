<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Top bar -->
    <header class="bg-[#0f2a44] text-white shadow-md">
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
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
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
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <slot />
    </main>

    <!-- Logout modal -->
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

      <!-- Sign-out preload overlay -->
      <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
                  leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showSignOutPreload" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-hidden"
          style="background: linear-gradient(135deg, #f0eef9 0%, #e8eafa 50%, #fdeef0 100%);">
          <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-20 -left-20 w-72 h-72 rounded-full opacity-20"
              style="background: radial-gradient(circle, #2a338f 0%, transparent 70%); animation: float 8s ease-in-out infinite;"></div>
            <div class="absolute -bottom-16 -right-16 w-96 h-96 rounded-full opacity-15"
              style="background: radial-gradient(circle, #ec1c2d 0%, transparent 70%); animation: float 10s ease-in-out infinite reverse;"></div>
            <div class="absolute top-1/3 right-1/4 w-48 h-48 rounded-full opacity-10"
              style="background: radial-gradient(circle, #2a338f 0%, transparent 70%); animation: float 12s ease-in-out infinite 2s;"></div>
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

            <div class="space-y-2">
              <p class="text-xl font-semibold" style="color:#2a338f;">Signing you out</p>
              <p class="text-gray-500 text-sm">Please wait a moment…</p>
            </div>

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

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import api from '@/services/api'
import axios from 'axios'
import { navigateTo } from '@/utils/navigate'

const page = usePage()
const showLogoutModal = ref(false)
const showSignOutPreload = ref(false)
const assignedVacancies = ref([])
const soDot = ref(0)
let soTimer = null

function isActive(href) {
  return page.url.startsWith(href)
}

function logout() {
  showLogoutModal.value = true
}

async function confirmLogout() {
  showLogoutModal.value    = false
  showSignOutPreload.value = true
  soTimer = setInterval(() => { soDot.value = (soDot.value + 1) % 3 }, 400)

  const token = localStorage.getItem('auth_token')

  await Promise.allSettled([
    axios.post('/api/logout', {}, { headers: { Authorization: `Bearer ${token}` } }),
    new Promise(r => setTimeout(r, 900)),
  ])

  clearInterval(soTimer)
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

onUnmounted(() => {
  if (soTimer) clearInterval(soTimer)
})
</script>
