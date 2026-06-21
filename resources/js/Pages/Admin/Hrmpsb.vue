<template>
  <AdminLayout title="HRMPSB Management">

    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-lg font-semibold text-gray-900">Board Composition</h2>
        <p class="text-sm text-gray-500 mt-0.5">Fixed membership — applies to all vacancies. Reconstitute by updating members here.</p>
      </div>
      <button @click="openAssignModal"
        class="flex items-center gap-2 px-4 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Member
      </button>
    </div>

    <!-- Composition table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-8 space-y-3">
        <div v-for="n in 5" :key="n" class="h-10 bg-gray-100 rounded animate-pulse"></div>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3">Member</th>
            <th class="px-5 py-3">HRMPSB Role</th>
            <th class="px-5 py-3">Type</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3">Assigned By</th>
            <th class="px-5 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="c in compositions" :key="c.id" class="hover:bg-gray-50 transition-colors"
            :class="{ 'opacity-50': !c.is_active }">
            <td class="px-5 py-3.5">
              <div class="font-medium text-gray-900">{{ c.user?.name }}</div>
              <div class="text-xs text-gray-400">{{ c.user?.email }}</div>
            </td>
            <td class="px-5 py-3.5 text-gray-700">{{ roles[c.hrmpsb_role] ?? c.hrmpsb_role }}</td>
            <td class="px-5 py-3.5">
              <span :class="c.member_type === 'principal' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600'"
                class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ c.member_type }}
              </span>
            </td>
            <td class="px-5 py-3.5">
              <button @click="toggleActive(c)"
                :class="c.is_active ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-red-50 text-red-600 hover:bg-red-100'"
                class="px-2 py-0.5 rounded-full text-xs font-medium transition-colors">
                {{ c.is_active ? 'Active' : 'Inactive' }}
              </button>
            </td>
            <td class="px-5 py-3.5 text-xs text-gray-500">{{ c.assigned_by?.name ?? '—' }}</td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-2">
                <button @click="toggleType(c)"
                  class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-md transition-colors">
                  Switch to {{ c.member_type === 'principal' ? 'Alternate' : 'Principal' }}
                </button>
                <button @click="removeMember(c)"
                  class="px-2.5 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                  Remove
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!compositions.length">
            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">
              No HRMPSB members assigned yet. Click "Add Member" to set up the board.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Role coverage summary -->
    <div v-if="compositions.length" class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
      <div v-for="(label, key) in roles" :key="key"
        class="bg-white rounded-lg border px-4 py-3 text-sm"
        :class="coveredRoles.has(key) ? 'border-green-200' : 'border-red-200 bg-red-50'">
        <div class="font-medium" :class="coveredRoles.has(key) ? 'text-gray-800' : 'text-red-700'">{{ label }}</div>
        <div class="text-xs mt-0.5" :class="coveredRoles.has(key) ? 'text-green-600' : 'text-red-500'">
          {{ coveredRoles.has(key) ? 'Assigned' : 'Not assigned' }}
        </div>
      </div>
    </div>

    <!-- Assign member modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <h3 class="text-base font-semibold text-gray-900">Add HRMPSB Member</h3>
          <button @click="showModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <form @submit.prevent="submitAssign" class="px-6 py-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">User <span class="text-red-500">*</span></label>
            <select v-model="assignForm.user_id" required
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
              <option value="">Select user…</option>
              <option v-for="u in eligibleUsers" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
            <p class="mt-1 text-xs text-gray-400">Their system role will be updated to HRMPSB Member or Secretariat automatically.</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">HRMPSB Role <span class="text-red-500">*</span></label>
            <select v-model="assignForm.hrmpsb_role" required
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
              <option value="">Select role…</option>
              <option v-for="(label, key) in roles" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Member Type <span class="text-red-500">*</span></label>
            <div class="flex gap-4">
              <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="radio" v-model="assignForm.member_type" value="principal" class="text-[#2a338f]" />
                Principal
              </label>
              <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="radio" v-model="assignForm.member_type" value="alternate" class="text-[#2a338f]" />
                Alternate
              </label>
            </div>
          </div>
          <div v-if="assignError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ assignError }}</div>
          <div class="flex justify-end gap-3 pt-1">
            <button type="button" @click="showModal = false"
              class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
            <button type="submit" :disabled="saving"
              class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-60">
              {{ saving ? 'Saving…' : 'Add to Board' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const compositions = ref([])
const roles        = ref({})
const users        = ref([])
const loading      = ref(false)
const showModal    = ref(false)
const saving       = ref(false)
const assignError  = ref('')

const assignForm = reactive({ user_id: '', hrmpsb_role: '', member_type: 'principal' })

const eligibleUsers = computed(() => users.value.filter(u => u.role !== 'applicant'))

const coveredRoles = computed(() => {
  const active = compositions.value.filter(c => c.is_active)
  return new Set(active.map(c => c.hrmpsb_role))
})

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function loadCompositions() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/hrmpsb/compositions', { headers: authHeaders() })
    compositions.value = data.compositions
    roles.value        = data.roles
  } finally {
    loading.value = false
  }
}

async function loadUsers() {
  const { data } = await axios.get('/api/admin/users', { headers: authHeaders() })
  users.value = data.data ?? data
}

function openAssignModal() {
  assignForm.user_id     = ''
  assignForm.hrmpsb_role = ''
  assignForm.member_type = 'principal'
  assignError.value      = ''
  showModal.value        = true
}

async function submitAssign() {
  saving.value      = true
  assignError.value = ''
  try {
    await axios.post('/api/admin/hrmpsb/compositions', assignForm, { headers: authHeaders() })
    showModal.value = false
    loadCompositions()
  } catch (e) {
    assignError.value = e.response?.data?.message ?? 'Failed to add member.'
  } finally {
    saving.value = false
  }
}

async function toggleType(c) {
  await axios.patch(`/api/admin/hrmpsb/compositions/${c.id}/toggle-type`, {}, { headers: authHeaders() })
  loadCompositions()
}

async function toggleActive(c) {
  await axios.patch(`/api/admin/hrmpsb/compositions/${c.id}/toggle-active`, {}, { headers: authHeaders() })
  loadCompositions()
}

async function removeMember(c) {
  if (!confirm(`Remove ${c.user?.name} from the HRMPSB?`)) return
  await axios.delete(`/api/admin/hrmpsb/compositions/${c.id}`, { headers: authHeaders() })
  loadCompositions()
}

onMounted(() => { loadCompositions(); loadUsers() })
</script>
