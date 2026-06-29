<template>
  <div>
    <a href="#main-content"
      class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-primary focus:text-white focus:rounded-lg focus:text-sm focus:font-semibold focus:outline-none">
      Skip to content
    </a>

  <div class="min-h-screen bg-gray-100 transition-all duration-500" :class="sidebarCollapsed ? '' : 'lg:pl-64'">

    <!-- Sidebar — always fixed (never scrolls with content) -->
    <aside
      :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:-translate-x-full' : 'lg:translate-x-0']"
      class="fixed inset-y-0 left-0 z-50 w-64 text-white flex flex-col transition-transform duration-200 bg-primary">

      <!-- Logo -->
      <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
        <img src="/images/csc-logo.png" alt="CSC Logo" class="h-9 w-9 object-contain flex-shrink-0"
          onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
        <div class="w-9 h-9 rounded-lg bg-white/15 items-center justify-center flex-shrink-0 hidden">
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div class="leading-tight">
          <p class="text-sm font-bold text-white">CSC RO VIII</p>
          <p class="text-xs text-white/60">HRMPSB Portal</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav flex-1 px-3 py-4 overflow-y-auto space-y-4">
        <div v-for="group in navGroups" :key="group.label">
          <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-3 mb-1.5">
            {{ group.label }}
          </p>
          <div class="space-y-0.5">
            <template v-for="item in group.items" :key="item.href">
              <Link
                v-if="!itemLockInfo(item.href).locked"
                :href="item.href"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                :class="isActive(item.href)
                  ? 'bg-white/15 text-white'
                  : 'text-white/75 hover:bg-white/10 hover:text-white'">
                <Icon :name="item.icon" size="4" class="flex-shrink-0" />
                {{ item.label }}
              </Link>
              <span v-else
                class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/25 cursor-not-allowed select-none"
                @mouseenter="showTooltip($event, itemLockInfo(item.href).reason)"
                @mouseleave="hideTooltip">
                <Icon :name="item.icon" size="4" class="flex-shrink-0" />
                <span class="flex-1 truncate">{{ item.label }}</span>
                <Icon name="lock" size="3" class="text-white/20 shrink-0" />
              </span>
            </template>
          </div>
        </div>
      </nav>

      <!-- Footer -->
      <div class="px-3 py-4 border-t border-white/10">
        <button v-if="canSwitchToAdmin" @click="showWorkspaceSwitch = true" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10 hover:text-white transition-colors mb-1">
          <Icon name="shield" size="4" class="flex-shrink-0" />
          Switch to Admin
        </button>
        <button @click="logout" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10 hover:text-white transition-colors">
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
    <div class="flex flex-col min-h-screen">

      <!-- Top bar — sticky so it stays visible while scrolling -->
      <header class="sticky top-0 z-40 bg-white border-b border-gray-200 px-4 sm:px-6 h-16 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
          <button @click="toggleSidebar" aria-label="Toggle sidebar" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100">
            <Icon name="menu" class="w-5 h-5" />
          </button>
          <h1 class="text-base font-semibold text-gray-900">{{ props2.title }}</h1>
        </div>

        <div class="relative" ref="dropdownRef">
          <button @click="dropdownOpen = !dropdownOpen" aria-label="User menu"
            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
            <div class="relative w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 overflow-hidden bg-primary">
              <span>{{ userInitial }}</span>
              <img :src="`/profile/photo?token=${authToken}`"
                class="absolute inset-0 w-full h-full object-cover"
                @error="e => e.target.style.display = 'none'"
                alt="" />
            </div>
            <div class="hidden sm:block text-left">
              <p class="text-sm font-semibold text-gray-800 leading-none">{{ userName }}</p>
              <p class="text-xs text-gray-400 mt-0.5">HRMPSB</p>
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
      </header>

      <main id="main-content" class="flex-1 p-4 sm:p-6" tabindex="-1">
        <slot />
      </main>

      <AppFooter />

    </div>

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

    <WorkspaceSwitcher :show="showWorkspaceSwitch" target="admin" />

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

    <Teleport to="body">
      <div v-if="tooltip.visible"
        class="fixed z-[9999] px-2.5 py-1.5 bg-gray-900 text-white text-[10px] leading-tight rounded-lg shadow-xl pointer-events-none whitespace-nowrap"
        :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px', transform: 'translateY(-50%)' }">
        {{ tooltip.text }}
      </div>
    </Teleport>

  </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import Icon from '@/Components/UI/Icon.vue'
import AppFooter from '@/Components/UI/AppFooter.vue'
import WorkspaceSwitcher from '@/Components/UI/WorkspaceSwitcher.vue'
import { useIdleTimer } from '@/composables/useIdleTimer'
import { navigateTo } from '@/utils/navigate'

useIdleTimer()

const props2 = defineProps({
  title:     { type: String, default: 'HRMPSB' },
  vacancyId: { type: Number, default: null },
})

const sidebarOpen       = ref(false)
const sidebarCollapsed  = ref(localStorage.getItem('sidebar_collapsed') === 'true')
const dropdownOpen      = ref(false)
const dropdownRef       = ref(null)
const showLogoutModal     = ref(false)
const showWorkspaceSwitch = ref(false)
const showSignOutPreload  = ref(false)
const soDot               = ref(0)
let soTimer = null
const stages           = ref(null)
const myRole           = ref(null)
const page             = usePage()
const authToken        = ref('')
const authUser         = ref({})
const tooltip          = ref({ visible: false, x: 0, y: 0, text: '' })

const stageReasons = {
  pre_assessment_exists:   'Complete Pre-Assessment first',
  qs_exists:               'Complete QS Screening first',
  qs_locked:               'Lock QS Screening results first',
  twe_exists:              'Complete TWE first',
  cbwe_exists:             'Complete CBWE first',
  cbwe_locked:             'Lock CBWE ratings first',
  bei_scheduled:           'Schedule BEI first',
  bei_locked:              'Lock BEI ratings first',
  eopt_exists:             'Complete EOPT first',
  background_check_locked: 'Lock Background Check first',
  deliberation_done:       'Complete Deliberation first',
}

const requiredStage = {
  '/hrmpsb/pre-assessment/':        { flag: 'pre_assessment_exists',      reason: stageReasons.pre_assessment_exists },
  '/hrmpsb/qs-evaluation/':         { flag: 'pre_assessment_exists',      reason: stageReasons.pre_assessment_exists },
  '/hrmpsb/qs-results/':            { flag: 'pre_assessment_exists',      reason: stageReasons.pre_assessment_exists },
  '/hrmpsb/exam-schedule/':         { flag: 'qs_locked',                  reason: stageReasons.qs_locked },
  '/hrmpsb/exam-results/':          { flag: 'qs_locked',                  reason: stageReasons.qs_locked },
  '/hrmpsb/bei-schedule/':          { flag: 'cbwe_locked',                reason: stageReasons.cbwe_locked },
  '/hrmpsb/cbwe-rating/':           { flag: 'twe_exists',                 reason: stageReasons.twe_exists },
  '/hrmpsb/bei-rating/':            { flag: 'bei_scheduled',              reason: stageReasons.bei_scheduled },
  '/hrmpsb/eopt/':                  { flag: 'bei_locked',                 reason: stageReasons.bei_locked },
  '/hrmpsb/background-check/':      { flag: 'eopt_exists',                reason: stageReasons.eopt_exists },
  '/hrmpsb/deliberation/':          { flag: 'background_check_locked',    reason: stageReasons.background_check_locked },
  '/hrmpsb/applicants/':            { flag: null,                         reason: null },
}

const userName    = computed(() => authUser.value?.full_name ?? 'HRMPSB Member')
const userEmail   = computed(() => authUser.value?.email ?? '')
const userInitial = computed(() => (authUser.value?.full_name ?? 'H')[0].toUpperCase())
const canSchedule = computed(() => {
  const role = authUser.value?.role
  if (role === 'admin') return true
  if (myRole.value && ['secretariat', 'hr-chief'].includes(myRole.value.hrmpsb_role)) return true
  return false
})

const canSwitchToAdmin = computed(() => {
  const role = authUser.value?.role
  if (role === 'admin') return true
  return canSchedule.value
})

const canViewAaDecisions = computed(() => {
  const role = authUser.value?.role
  if (role === 'admin') return true
  if (myRole.value && myRole.value.hrmpsb_role === 'secretariat') return true
  return false
})

const canViewEopt = computed(() => {
  const role = authUser.value?.role
  if (role === 'admin') return true
  if (myRole.value) return true
  return false
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

onMounted(async () => {
  authToken.value = localStorage.getItem('auth_token') ?? ''
  authUser.value  = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)

  if (authToken.value) {
    try {
      const { data } = await axios.get('/api/hrmpsb/my-role', {
        headers: { Authorization: `Bearer ${authToken.value}` }
      })
      myRole.value = data.composition
    } catch {
      // Board role not available — proceed with system role only
    }
  }

  if (props2.vacancyId) {
    loadStages()
  }
})

async function loadStages() {
  try {
    const { data } = await axios.get('/api/hrmpsb/pipeline-stages', {
      params: { vacancy_ids: [props2.vacancyId] },
      headers: { Authorization: `Bearer ${authToken.value}` },
    })
    stages.value = data[props2.vacancyId] ?? null
  } catch {
    stages.value = null
  }
}

function showTooltip(e, text) {
  const rect = e.currentTarget.getBoundingClientRect()
  tooltip.value = {
    visible: true,
    x: rect.right + 10,
    y: rect.top + rect.height / 2,
    text,
  }
}

function hideTooltip() {
  tooltip.value.visible = false
}

function itemLockInfo(href) {
  if (!stages.value) return { locked: false, reason: null }
  for (const [path, rule] of Object.entries(requiredStage)) {
    if (href.startsWith(path)) {
      if (rule.flag && !stages.value[rule.flag]) {
        return { locked: true, reason: rule.reason }
      }
      return { locked: false, reason: null }
    }
  }
  return { locked: false, reason: null }
}

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})

const navGroups = computed(() => {
  const groups = [
    {
      label: 'Overview',
      items: [
        {
          label: 'My Assignments',
          href:  '/hrmpsb/dashboard',
          icon:  'grid',
        },
        ...(canViewAaDecisions.value
          ? [{
              label: 'AA Decisions',
              href:  '/hrmpsb/aa-decisions',
              icon:  'check',
            }]
          : []),
      ],
    },
  ]

  if (props2.vacancyId) {
    const v = props2.vacancyId
    groups[0].items.push({
      label: 'Applicants & Documents',
      href:  `/hrmpsb/applicants/${v}`,
      icon:  'document',
    })
    groups.push(
      {
        label: 'Screening',
        items: [
          {
            label: 'Pre-Assessment',
            href:  `/hrmpsb/pre-assessment/${v}`,
            icon:  'document',
          },
          {
            label: 'QS Evaluation',
            href:  `/hrmpsb/qs-evaluation/${v}`,
            icon:  'check',
          },
          {
            label: 'QS Results',
            href:  `/hrmpsb/qs-results/${v}`,
            icon:  'grid',
          },
        ],
      },
      {
        label: 'TWE (Written Exam)',
        items: (() => {
          const tweItems = []
          if (canSchedule.value) {
            tweItems.push({
              label: 'TWE Scheduler',
              href:  `/hrmpsb/exam-schedule/${v}?exam_type=TWE`,
              icon:  'calendar',
            })
          }
          tweItems.push(          {
            label: 'TWE Results',
            href:  `/hrmpsb/exam-results/${v}?exam_type=TWE`,
            icon:  'document',
          })
          return tweItems
        })(),
      },
      {
        label: 'CBWE',
        items: [
          {
            label: 'CBWE Rating',
            href:  `/hrmpsb/cbwe-rating/${v}`,
            icon:  'star',
          },
        ],
      },
      {
        label: 'Interview',
        items: (() => {
          const interviewItems = []
          if (canSchedule.value) {
            interviewItems.push({
              label: 'BEI Scheduler',
              href:  `/hrmpsb/bei-schedule/${v}`,
              icon:  'calendar',
            })
          }
          interviewItems.push(          {
            label: 'BEI Rating',
            href:  `/hrmpsb/bei-rating/${v}`,
            icon:  'star',
          })
          if (canViewEopt.value) {
            interviewItems.push({
              label: 'EOPT Assessment',
              href:  `/hrmpsb/eopt/${v}`,
              icon:  'shield',
            })
          }
          return interviewItems
        })(),
      },
      {
        label: 'Background Check',
        items: [
          {
            label: 'Background Investigation',
            href:  `/hrmpsb/background-check/${v}`,
            icon:  'shield',
          },
        ],
      },
      {
        label: 'Decision',
        items: [
          {
            label: 'Deliberation',
            href:  `/hrmpsb/deliberation/${v}`,
            icon:  'user',
          },
        ],
      }
    )
  }

  return groups
})

function isActive(href) {
  return page.url.startsWith(href)
}

function logout() {
  sidebarOpen.value = false
  dropdownOpen.value = false
  showLogoutModal.value = true
}

async function confirmLogout() {
  showLogoutModal.value    = false
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
</script>

<style scoped>
.sidebar-nav::-webkit-scrollbar { width: 5px; }
.sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }
.sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }
.sidebar-nav { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.15) transparent; }
@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33%       { transform: translate(30px, -30px) scale(1.05); }
  66%       { transform: translate(-20px, 20px) scale(0.95); }
}
</style>
