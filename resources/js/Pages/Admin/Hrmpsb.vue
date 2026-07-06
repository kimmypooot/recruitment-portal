<template>
  <AdminLayout title="HRMPSB Management">

    <!-- ── HRMPSB Board Composition ─────────────────────────────────────── -->
    <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
      <div>
        <h2 class="text-lg font-semibold text-gray-900">Board Composition</h2>
        <p class="text-sm text-gray-500 mt-0.5">Fixed membership — applies to all vacancies. Reconstitute by updating members here.</p>
      </div>
      <button @click="openAssignModal"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Member
      </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-8 space-y-3">
        <div v-for="n in 5" :key="n" class="h-10 bg-gray-100 rounded animate-pulse"></div>
      </div>

      <div v-else class="overflow-x-auto">
      <table class="w-full text-sm">
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
              <div class="font-medium text-gray-900">{{ c.user?.full_name }}</div>
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
            <td class="px-5 py-3.5 text-xs text-gray-500">{{ c.assigned_by?.full_name ?? '—' }}</td>
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

    <!-- ── Place of Assignment Heads ────────────────────────────────────── -->
    <div class="mt-10">
      <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Place of Assignment Heads</h2>
          <p class="text-sm text-gray-500 mt-0.5">Map each Field Office or Regional Support Unit to its designated Head of Unit. This assignment is used per-vacancy to dynamically build the HRMPSB composition. Vacancies under Human Resource Division (HRD) automatically exclude the Head of Unit since the Chief of HR Division is already a mandatory member. Vacancies under Office of the Regional Director (ORD) also exclude it, since the head of that office is the Appointing Authority, for whom a Head of Unit designation does not apply.</p>
        </div>
        <button @click="openPoaHeadModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Assign Head
        </button>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div v-if="loadingHeads" class="p-8 space-y-3">
          <div v-for="n in 3" :key="n" class="h-10 bg-gray-100 rounded animate-pulse"></div>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
                <th class="px-5 py-3">Place of Assignment</th>
                <th class="px-5 py-3">Designated Head of Unit</th>
                <th class="px-5 py-3">Assigned By</th>
                <th class="px-5 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="h in poaHeads" :key="h.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3.5">
                  <span class="font-medium text-gray-900">{{ h.place_of_assignment }}</span>
                </td>
                <td class="px-5 py-3.5">
                  <div class="font-medium text-gray-900">{{ h.user?.full_name }}</div>
                  <div class="text-xs text-gray-400">{{ h.user?.email }}</div>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500">{{ h.assigned_by?.full_name ?? '—' }}</td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openPoaHeadEditModal(h)"
                      class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-md transition-colors">
                      Change
                    </button>
                    <button @click="removePoaHead(h)"
                      class="px-2.5 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                      Remove
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!poaHeads.length">
                <td colspan="4" class="px-5 py-12 text-center text-sm text-gray-400">
                  No heads assigned yet. Click "Assign Head" to designate a Head of Unit for each place of assignment.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Assign Member Modal ──────────────────────────────────────────── -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="showModal = false"></div>
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
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
              <option value="">Select user…</option>
              <option v-for="u in eligibleUsers" :key="u.id" :value="u.id">{{ u.full_name }}</option>
            </select>
            <p class="mt-1 text-xs text-gray-400">Only users with the HRMPSB system role are shown. Assign their system role first in Admin → Users.</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">HRMPSB Role <span class="text-red-500">*</span></label>
            <select v-model="assignForm.hrmpsb_role" required
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
              <option value="">Select role…</option>
              <option v-for="(label, key) in roles" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Member Type <span class="text-red-500">*</span></label>
            <div class="flex gap-4">
              <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="radio" v-model="assignForm.member_type" value="principal" class="text-primary" />
                Principal
              </label>
              <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="radio" v-model="assignForm.member_type" value="alternate" class="text-primary" />
                Alternate
              </label>
            </div>
          </div>
          <div v-if="assignError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ assignError }}</div>
          <div class="flex justify-end gap-3 pt-1">
            <button type="button" @click="showModal = false"
              class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
            <button type="submit" :disabled="saving"
              class="px-4 py-2 text-sm bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark disabled:opacity-60">
              {{ saving ? 'Saving…' : 'Add to Board' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── Assign/Edit Place of Assignment Head Modal ───────────────────── -->
    <div v-if="showPoaHeadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="showPoaHeadModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <h3 class="text-base font-semibold text-gray-900">{{ editingPoaHead ? 'Change Head of Unit' : 'Assign Head of Unit' }}</h3>
          <button @click="showPoaHeadModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <form @submit.prevent="submitPoaHead" class="px-6 py-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Place of Assignment <span class="text-red-500">*</span></label>
            <select v-model="poaHeadForm.place_of_assignment" required :disabled="editingPoaHead"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
              <option value="">Select place of assignment…</option>
              <optgroup label="CSC Field Offices">
                <option v-for="loc in fieldOffices" :key="loc" :value="loc">{{ loc }}</option>
              </optgroup>
              <optgroup label="Regional Support Units">
                <option v-for="loc in rsuOffices" :key="loc" :value="loc">{{ loc }}</option>
              </optgroup>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Head of Unit <span class="text-red-500">*</span></label>
            <select v-model="poaHeadForm.user_id" required
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white">
              <option value="">Select user…</option>
              <option v-for="u in eligibleUsers" :key="u.id" :value="u.id">{{ u.full_name }}</option>
            </select>
            <p class="mt-1 text-xs text-gray-400">Only users with the HRMPSB system role can be designated as Head of Unit.</p>
          </div>
          <div v-if="poaHeadForm.place_of_assignment === HRD_PLACE" class="text-sm text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
            Note: The HRD is the Human Resource Division. Since the Chief of HR Division is already a mandatory HRMPSB member, the Head of Unit role will be excluded from the HRMPSB composition for HRD vacancies.
          </div>
          <div v-if="poaHeadForm.place_of_assignment === ORD_PLACE" class="text-sm text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
            Note: The ORD is the Office of the Regional Director. Since the head of this office is the Appointing Authority — who plays a separate role in the recruitment process — the Head of Unit role does not apply and will be excluded from the HRMPSB composition for ORD vacancies.
          </div>
          <div v-if="poaHeadError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ poaHeadError }}</div>
          <div class="flex justify-end gap-3 pt-1">
            <button type="button" @click="showPoaHeadModal = false"
              class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
            <button type="submit" :disabled="savingPoaHead"
              class="px-4 py-2 text-sm bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark disabled:opacity-60">
              {{ savingPoaHead ? 'Saving…' : (editingPoaHead ? 'Update' : 'Assign') }}
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
import { useConfirm } from '@/composables/useConfirm'
import { useToast } from '@/composables/useToast'
import { CSC_FIELD_OFFICES, REGIONAL_SUPPORT_UNITS } from '@/constants/officesOfAssignment'

const HRD_PLACE = 'Human Resource Division (HRD)'
const ORD_PLACE = 'Office of the Regional Director (ORD)'

const toast = useToast()
const { confirm } = useConfirm()

// ── Global Composition ─────────────────────────────────────────────────
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
  const { data } = await axios.get('/api/admin/users?role=hrmpsb', { headers: authHeaders() })
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
    toast.success('Member added to board.')
    loadCompositions()
  } catch (e) {
    assignError.value = e.response?.data?.message ?? 'Failed to add member.'
    toast.error(assignError.value)
  } finally {
    saving.value = false
  }
}

async function toggleType(c) {
  try {
    await axios.patch(`/api/admin/hrmpsb/compositions/${c.id}/toggle-type`, {}, { headers: authHeaders() })
    toast.success(`${c.user?.full_name} switched to ${c.member_type === 'principal' ? 'Alternate' : 'Principal'}.`)
    loadCompositions()
  } catch (e) {
    toast.error(e?.response?.data?.message ?? 'Failed to toggle type.')
  }
}

async function toggleActive(c) {
  try {
    await axios.patch(`/api/admin/hrmpsb/compositions/${c.id}/toggle-active`, {}, { headers: authHeaders() })
    toast.success(`${c.user?.full_name} ${c.is_active ? 'deactivated' : 'activated'}.`)
    loadCompositions()
  } catch (e) {
    toast.error(e?.response?.data?.message ?? 'Failed to toggle status.')
  }
}

async function removeMember(c) {
  const ok = await confirm(`Remove ${c.user?.full_name} from the HRMPSB?`)
  if (!ok) return
  try {
    await axios.delete(`/api/admin/hrmpsb/compositions/${c.id}`, { headers: authHeaders() })
    toast.error(`${c.user?.full_name} removed from board.`)
    loadCompositions()
  } catch (e) {
    toast.error(e?.response?.data?.message ?? 'Failed to remove member.')
  }
}

// ── Place of Assignment Heads ─────────────────────────────────────────
const poaHeads        = ref([])
const loadingHeads    = ref(false)
const showPoaHeadModal = ref(false)
const savingPoaHead   = ref(false)
const poaHeadError    = ref('')
const editingPoaHead  = ref(false)

const poaHeadForm = reactive({ place_of_assignment: '', user_id: '' })

const fieldOffices = CSC_FIELD_OFFICES
const rsuOffices   = REGIONAL_SUPPORT_UNITS

async function loadPoaHeads() {
  loadingHeads.value = true
  try {
    const { data } = await axios.get('/api/admin/place-of-assignment-heads', { headers: authHeaders() })
    poaHeads.value = data.heads
  } finally {
    loadingHeads.value = false
  }
}

function openPoaHeadModal() {
  editingPoaHead.value = false
  poaHeadForm.place_of_assignment = ''
  poaHeadForm.user_id = ''
  poaHeadError.value = ''
  showPoaHeadModal.value = true
}

function openPoaHeadEditModal(head) {
  editingPoaHead.value = true
  poaHeadForm.place_of_assignment = head.place_of_assignment
  poaHeadForm.user_id = head.user_id
  poaHeadError.value = ''
  showPoaHeadModal.value = true
}

async function submitPoaHead() {
  savingPoaHead.value = true
  poaHeadError.value = ''
  try {
    await axios.post('/api/admin/place-of-assignment-heads', poaHeadForm, { headers: authHeaders() })
    showPoaHeadModal.value = false
    toast.success(editingPoaHead.value ? 'Head of Unit updated.' : 'Head of Unit assigned.')
    loadPoaHeads()
  } catch (e) {
    poaHeadError.value = e.response?.data?.message ?? 'Failed to save.'
    toast.error(poaHeadError.value)
  } finally {
    savingPoaHead.value = false
  }
}

async function removePoaHead(head) {
  const ok = await confirm(`Remove ${head.user?.full_name} as Head of Unit for "${head.place_of_assignment}"?`)
  if (!ok) return
  try {
    await axios.delete(`/api/admin/place-of-assignment-heads/${head.id}`, { headers: authHeaders() })
    toast.error(`Head of Unit removed for ${head.place_of_assignment}.`)
    loadPoaHeads()
  } catch (e) {
    toast.error(e?.response?.data?.message ?? 'Failed to remove.')
  }
}

onMounted(() => { loadCompositions(); loadUsers(); loadPoaHeads() })
</script>
