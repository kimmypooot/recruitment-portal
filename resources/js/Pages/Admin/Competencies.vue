<template>
  <AdminLayout title="Competencies">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Competency Library</h1>
          <p class="text-sm text-gray-500 mt-1">
            Define the competencies available for BEI rating across all positions.
          </p>
        </div>
        <button @click="openCreate" class="btn-primary">
          <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Add Competency
        </button>
      </div>

      <!-- Error -->
      <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
        {{ error }}
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="n in 6" :key="n" class="h-14 bg-gray-100 rounded-xl animate-pulse"></div>
      </div>

      <!-- Grouped list -->
      <div v-else class="space-y-6">
        <div v-for="groupName in groupOrder" :key="groupName">
          <template v-if="byGroup[groupName]?.length">

            <!-- Group header -->
            <div class="flex items-center gap-3 mb-3">
              <span class="text-xs font-bold uppercase tracking-widest px-2.5 py-1 rounded-full"
                :class="groupChipClass(groupName)">
                {{ groupName }}
              </span>
              <span class="text-xs text-gray-400">{{ byGroup[groupName].length }} competenc{{ byGroup[groupName].length === 1 ? 'y' : 'ies' }}</span>
              <div class="flex-1 h-px bg-gray-100"></div>
            </div>

            <!-- Competency rows -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-50 mb-2">
              <div v-for="comp in byGroup[groupName]" :key="comp.id"
                class="flex items-start gap-4 px-5 py-4 group hover:bg-gray-50/60 transition-colors">

                <!-- Info -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-semibold text-gray-900">{{ comp.competency_name }}</p>
                    <code class="text-[10px] font-mono bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">
                      {{ comp.competency_key }}
                    </code>
                  </div>
                  <p v-if="comp.description" class="text-xs text-gray-400 mt-0.5 leading-snug line-clamp-2">
                    {{ comp.description }}
                  </p>
                </div>

                <!-- Sort order badge -->
                <span class="text-[10px] text-gray-300 font-mono mt-0.5 shrink-0">
                  #{{ comp.sort_order }}
                </span>

                <!-- Actions (appear on hover) -->
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                  <button @click="openEdit(comp)"
                    class="p-1.5 text-gray-400 hover:text-[#2a338f] hover:bg-indigo-50 rounded-lg transition-colors"
                    title="Edit">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button @click="confirmDelete(comp)"
                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Delete">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>

              </div>
            </div>
          </template>
        </div>

        <!-- Empty state -->
        <div v-if="!competencies.length"
          class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-center py-20 text-center">
          <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <p class="text-sm font-semibold text-gray-900 mb-1">No competencies defined yet</p>
          <p class="text-xs text-gray-400">Click "Add Competency" to create the first one.</p>
        </div>
      </div>

    </div>

    <!-- ── Create / Edit modal ────────────────────────────────────────────── -->
    <Transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div v-if="modal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @click.self="closeModal">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg"
          @click.stop>

          <!-- Modal header -->
          <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">
              {{ modal.mode === 'create' ? 'Add Competency' : 'Edit Competency' }}
            </h2>
            <button @click="closeModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Modal body -->
          <form @submit.prevent="saveCompetency" class="px-6 py-5 space-y-4">

            <div>
              <label class="field-label">Competency Name <span class="text-red-500">*</span></label>
              <input v-model="form.competency_name" type="text" required autofocus
                placeholder="e.g. Professionalism and Ethics"
                class="field-input" :class="{ 'border-red-300': formErrors.competency_name }"/>
              <p v-if="formErrors.competency_name" class="field-error">{{ formErrors.competency_name[0] }}</p>
              <p v-if="modal.mode === 'edit'" class="text-[11px] text-gray-400 mt-1">
                Key: <code class="font-mono">{{ modal.competency?.competency_key }}</code> (cannot be changed)
              </p>
            </div>

            <div>
              <label class="field-label">Group <span class="text-red-500">*</span></label>
              <select v-model="form.competency_group" required class="field-input">
                <option value="">— Select group —</option>
                <option v-for="g in groupOrder" :key="g" :value="g">{{ g }}</option>
              </select>
              <p v-if="formErrors.competency_group" class="field-error">{{ formErrors.competency_group[0] }}</p>
            </div>

            <div>
              <label class="field-label">Sort Order</label>
              <input v-model.number="form.sort_order" type="number" min="0" max="255"
                placeholder="0"
                class="field-input w-28"/>
              <p class="text-[11px] text-gray-400 mt-1">Lower numbers appear first within the group.</p>
            </div>

            <div>
              <label class="field-label">Description <span class="text-gray-400 font-normal">(optional)</span></label>
              <textarea v-model="form.description" rows="3"
                placeholder="Brief description of what this competency assesses…"
                class="field-input resize-none"></textarea>
            </div>

            <!-- Modal footer -->
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
              <button type="submit" :disabled="modal.saving" class="btn-primary">
                <svg v-if="modal.saving" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                {{ modal.saving ? 'Saving…' : (modal.mode === 'create' ? 'Create' : 'Save Changes') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ── Delete confirmation modal ─────────────────────────────────────── -->
    <Transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        @click.self="deleteTarget = null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-bold text-gray-900">Delete Competency</h3>
              <p class="text-sm text-gray-500 mt-1">
                Are you sure you want to delete
                <span class="font-semibold text-gray-800">{{ deleteTarget.competency_name }}</span>?
                This cannot be undone.
              </p>
            </div>
          </div>
          <p v-if="deleteError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
            {{ deleteError }}
          </p>
          <div class="flex justify-end gap-3">
            <button @click="deleteTarget = null; deleteError = null" class="btn-secondary">Cancel</button>
            <button @click="deleteCompetency" :disabled="deleting" class="btn-danger">
              {{ deleting ? 'Deleting…' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

// Hardcoded order; can be replaced with import { COMPETENCY_GROUPS } from '@/utils/constants' if server order matches
const groupOrder = ['Core', 'Organizational', 'Leadership', 'Technical']

// ── Auth ──────────────────────────────────────────────────────────────────────
function authHeaders() {
  const t = localStorage.getItem('auth_token')
  return t ? { Authorization: `Bearer ${t}` } : {}
}

// ── State ─────────────────────────────────────────────────────────────────────
const loading      = ref(true)
const error        = ref(null)
const competencies = ref([])

const byGroup = computed(() => {
  const map = {}
  for (const g of groupOrder) {
    map[g] = competencies.value
      .filter(c => c.competency_group === g)
      .sort((a, b) => a.sort_order - b.sort_order || a.competency_name.localeCompare(b.competency_name))
  }
  return map
})

// ── Modal state ───────────────────────────────────────────────────────────────
const modal = reactive({
  open:        false,
  mode:        'create',   // 'create' | 'edit'
  competency:  null,
  saving:      false,
})
const form = reactive({
  competency_name:  '',
  competency_group: '',
  sort_order:       0,
  description:      '',
})
const formErrors = ref({})

// ── Delete state ──────────────────────────────────────────────────────────────
const deleteTarget = ref(null)
const deleting     = ref(false)
const deleteError  = ref(null)

// ── Data fetching ─────────────────────────────────────────────────────────────
async function load() {
  loading.value = true
  error.value   = null
  try {
    const { data } = await axios.get('/api/admin/competencies', { headers: authHeaders() })
    competencies.value = data.data ?? []
  } catch (e) {
    error.value = e?.response?.data?.message ?? 'Failed to load competencies.'
  } finally {
    loading.value = false
  }
}

// ── Create / Edit ─────────────────────────────────────────────────────────────
function openCreate() {
  modal.mode       = 'create'
  modal.competency = null
  form.competency_name  = ''
  form.competency_group = ''
  form.sort_order       = 0
  form.description      = ''
  formErrors.value      = {}
  modal.open = true
}

function openEdit(comp) {
  modal.mode       = 'edit'
  modal.competency = comp
  form.competency_name  = comp.competency_name
  form.competency_group = comp.competency_group
  form.sort_order       = comp.sort_order
  form.description      = comp.description ?? ''
  formErrors.value      = {}
  modal.open = true
}

function closeModal() {
  modal.open = false
}

async function saveCompetency() {
  modal.saving = true
  formErrors.value = {}
  try {
    const payload = {
      competency_name:  form.competency_name,
      competency_group: form.competency_group,
      sort_order:       form.sort_order,
      description:      form.description || null,
    }
    if (modal.mode === 'create') {
      await axios.post('/api/admin/competencies', payload, { headers: authHeaders() })
    } else {
      await axios.put(`/api/admin/competencies/${modal.competency.id}`, payload, { headers: authHeaders() })
    }
    modal.open = false
    await load()
  } catch (e) {
    formErrors.value = e?.response?.data?.errors ?? {}
    if (!Object.keys(formErrors.value).length) {
      error.value = e?.response?.data?.message ?? 'Save failed.'
    }
  } finally {
    modal.saving = false
  }
}

// ── Delete ────────────────────────────────────────────────────────────────────
function confirmDelete(comp) {
  deleteTarget.value = comp
  deleteError.value  = null
}

async function deleteCompetency() {
  if (!deleteTarget.value) return
  deleting.value    = true
  deleteError.value = null
  try {
    await axios.delete(`/api/admin/competencies/${deleteTarget.value.id}`, { headers: authHeaders() })
    deleteTarget.value = null
    await load()
  } catch (e) {
    deleteError.value = e?.response?.data?.message ?? 'Delete failed.'
  } finally {
    deleting.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function groupChipClass(group) {
  return {
    'Core':           'bg-blue-100 text-blue-700',
    'Organizational': 'bg-violet-100 text-violet-700',
    'Leadership':     'bg-amber-100 text-amber-700',
    'Technical':      'bg-teal-100 text-teal-700',
  }[group] ?? 'bg-gray-100 text-gray-600'
}

onMounted(load)
</script>

<style scoped>
@reference "../../../css/app.css";
.field-label {
  @apply block text-sm font-semibold text-gray-700 mb-1.5;
}
.field-input {
  @apply w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none;
}
.field-error {
  @apply text-xs text-red-600 mt-1;
}
.btn-primary {
  @apply inline-flex items-center px-4 py-2 bg-[#2a338f] text-white text-sm font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-50 transition-colors;
}
.btn-secondary {
  @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 transition-colors;
}
.btn-danger {
  @apply inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors;
}
</style>
