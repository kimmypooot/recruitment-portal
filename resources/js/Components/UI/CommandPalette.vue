<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="open" class="fixed inset-0 z-[9998] flex items-start justify-center p-4 pt-[12vh]"
        @keydown.esc.prevent="close">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-[2px]" @click="close"></div>

        <div role="dialog" aria-modal="true" aria-label="Command palette"
          class="rise-in relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
          <!-- Search box -->
          <div class="flex items-center gap-3 px-4 border-b border-gray-100">
            <Icon name="search" size="5" class="text-gray-400 flex-shrink-0" />
            <input ref="input" v-model="query" type="text" autocomplete="off" spellcheck="false"
              role="combobox" aria-expanded="true" aria-controls="cmdk-list"
              :aria-activedescendant="results.length ? 'cmdk-item-' + active : undefined"
              placeholder="Jump to a page or vacancy…"
              class="flex-1 py-4 text-sm text-gray-900 placeholder-gray-400 bg-transparent border-0 focus:ring-0 focus:outline-none"
              @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="go(results[active])" />
            <kbd class="hidden sm:inline text-[10px] font-semibold text-gray-400 border border-gray-200 rounded px-1.5 py-0.5">ESC</kbd>
          </div>

          <!-- Results -->
          <ul id="cmdk-list" role="listbox" ref="list" class="max-h-[50vh] overflow-y-auto py-2">
            <template v-for="(item, i) in results" :key="item.href + item.label">
              <li v-if="i === 0 || results[i - 1].group !== item.group"
                class="px-4 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ item.group }}</li>
              <li :id="'cmdk-item-' + i" role="option" :aria-selected="i === active" :data-idx="i"
                class="mx-2 px-3 py-2 rounded-lg flex items-center gap-3 cursor-pointer text-sm"
                :class="i === active ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-50'"
                @mousemove="active = i" @click="go(item)">
                <span class="truncate flex-1">{{ item.label }}</span>
                <span v-if="item.hint" class="text-[11px] truncate max-w-[40%]"
                  :class="i === active ? 'text-white/70' : 'text-gray-400'">{{ item.hint }}</span>
              </li>
            </template>
            <li v-if="!results.length" class="px-4 py-10 text-center text-sm text-gray-400">
              {{ loadingVacancies ? 'Loading…' : 'Nothing matches “' + query + '”' }}
            </li>
          </ul>

          <div class="flex items-center gap-4 px-4 py-2 border-t border-gray-100 bg-gray-50 text-[11px] text-gray-400">
            <span><kbd class="font-semibold">↑↓</kbd> navigate</span>
            <span><kbd class="font-semibold">↵</kbd> open</span>
            <span class="ml-auto">{{ isMac ? '⌘' : 'Ctrl' }} K to toggle</span>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import api from '@/services/api'
import Icon from '@/Components/UI/Icon.vue'
import { useTheme } from '@/composables/useTheme'

const page = usePage()
const { mode: themeMode, cycle: cycleTheme } = useTheme()
const open = ref(false)
const query = ref('')
const active = ref(0)
const input = ref(null)
const list = ref(null)
const vacancies = ref([])
const loadingVacancies = ref(false)
let vacanciesLoaded = false

const isMac = typeof navigator !== 'undefined' && /Mac|iPhone|iPad/.test(navigator.platform)

function currentUser() {
  const shared = page.props?.auth?.user
  if (shared) return shared
  try { return JSON.parse(localStorage.getItem('auth_user') ?? 'null') } catch { return null }
}

const STAGES = [
  ['Pre-Assessment', 'pre-assessment'],
  ['QS Evaluation', 'qs-evaluation'],
  ['QS Results', 'qs-results'],
  ['TWE Schedule', 'exam-schedule', '?exam_type=TWE'],
  ['TWE Results', 'exam-results', '?exam_type=TWE'],
  ['CBWE Rating', 'cbwe-rating'],
  ['BEI Schedule', 'bei-schedule'],
  ['BEI Rating', 'bei-rating'],
  ['EOPT', 'eopt'],
  ['Background Check', 'background-check'],
  ['Deliberation', 'deliberation'],
]

const items = computed(() => {
  const user = currentUser()
  const role = user?.role
  const out = []
  const add = (group, label, href, hint = '', keywords = '') => out.push({ group, label, href, hint, keywords })

  if (role === 'admin') {
    add('Admin', 'Dashboard', '/admin/dashboard')
    add('Admin', 'Vacancies', '/admin/vacancies')
    add('Admin', 'Applications', '/admin/applications')
    add('Admin', 'Feedbacks', '/admin/feedbacks')
    add('Admin', 'HRMPSB Composition', '/admin/hrmpsb', '', 'board members')
    add('Admin', 'Users', '/admin/users', '', 'accounts roles')
    add('Admin', 'Reports', '/admin/reports', '', 'export')
    add('Admin', 'Competencies', '/admin/competencies')
    add('Admin', 'Email Templates', '/admin/email-templates')
    add('Admin', 'Audit Logs', '/admin/audit-logs', '', 'history activity')
    add('Admin', 'Help & Guide', '/admin/help')
  }
  if (role === 'admin' || role === 'hrmpsb') {
    add('HRMPSB', 'HRMPSB Dashboard', '/hrmpsb/dashboard', '', 'board')
    add('HRMPSB', 'Appointing Authority Decisions', '/hrmpsb/aa-decisions', '', 'aa')
    for (const v of vacancies.value) {
      add('Applicants', `Applicants — ${v.position_title}`, `/hrmpsb/applicants/${v.id}`, v.place_of_assignment ?? '', 'documents')
      for (const [name, path, qs = ''] of STAGES) {
        add('Stages', `${name} — ${v.position_title}`, `/hrmpsb/${path}/${v.id}${qs}`, v.place_of_assignment ?? '')
      }
    }
  }
  if (role) {
    add('My Applications', 'Applicant Dashboard', '/applicant/dashboard')
    add('My Applications', 'My Applications', '/applicant/applications', '', 'status track')
    add('My Applications', 'My Profile', '/applicant/complete-profile', '', 'documents pds')
  }
  if (!role || role === 'applicant') {
    for (const v of vacancies.value) {
      add('Apply', `Apply — ${v.position_title}`, `/vacancies/${v.id}/apply`, v.place_of_assignment ?? '')
    }
  }
  out.push({ group: 'General', label: 'Toggle theme', href: 'action:theme', hint: 'Now: ' + themeMode.value, keywords: 'dark light mode appearance system', action: cycleTheme })
  add('General', 'Browse Vacancies', '/')
  add('General', 'How to Apply', '/how-to-apply')
  add('General', 'Track Application', '/track-application')
  add('General', 'Help', '/help')
  add('General', 'About', '/about')
  return out
})

const results = computed(() => {
  const tokens = query.value.toLowerCase().split(/\s+/).filter(Boolean)
  const matches = tokens.length
    ? items.value.filter(i => {
        const hay = `${i.label} ${i.hint} ${i.keywords} ${i.group}`.toLowerCase()
        return tokens.every(t => hay.includes(t))
      })
    : items.value.filter(i => i.group !== 'Stages' && i.group !== 'Applicants' && i.group !== 'Apply')
  // Keep group order stable but cap the list so a broad query stays fast.
  return matches.slice(0, 40)
})

watch(query, () => { active.value = 0 })

async function loadVacancies() {
  if (vacanciesLoaded) return
  vacanciesLoaded = true
  loadingVacancies.value = true
  try {
    const { data } = await api.get('/vacancies', { params: { status: 'published,closed', per_page: 100 } })
    vacancies.value = data.data ?? []
  } catch {
    vacanciesLoaded = false
  } finally {
    loadingVacancies.value = false
  }
}

function move(step) {
  if (!results.value.length) return
  active.value = (active.value + step + results.value.length) % results.value.length
  nextTick(() => list.value?.querySelector(`[data-idx="${active.value}"]`)?.scrollIntoView({ block: 'nearest' }))
}

function go(item) {
  if (!item) return
  close()
  if (item.action) { item.action(); return }
  router.visit(item.href)
}

function show() {
  open.value = true
  query.value = ''
  active.value = 0
  loadVacancies()
  nextTick(() => input.value?.focus())
}
function close() { open.value = false }

function onKey(e) {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault()
    open.value ? close() : show()
  }
}

onMounted(() => {
  window.addEventListener('keydown', onKey)
  window.addEventListener('open-command-palette', show)
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKey)
  window.removeEventListener('open-command-palette', show)
})
</script>
