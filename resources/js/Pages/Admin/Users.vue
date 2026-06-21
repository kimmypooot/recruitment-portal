<template>
  <AdminLayout title="Users">

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
      <input
        v-model="search"
        @input="onSearch"
        type="text"
        placeholder="Search users..."
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none w-56" />
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New User
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-8 space-y-3">
        <div v-for="n in 5" :key="n" class="h-12 bg-gray-100 rounded animate-pulse"></div>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3">Name</th>
            <th class="px-5 py-3">Email</th>
            <th class="px-5 py-3">Role</th>
            <th class="px-5 py-3">Joined</th>
            <th class="px-5 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-bold flex-shrink-0">
                  {{ initials(user.name) }}
                </div>
                <span class="font-medium text-gray-900">{{ user.name }}</span>
              </div>
            </td>
            <td class="px-5 py-3.5 text-gray-600">{{ user.email }}</td>
            <td class="px-5 py-3.5">
              <span :class="roleClass(user.role)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">
                {{ roleLabel(user.role) }}
              </span>
            </td>
            <td class="px-5 py-3.5 text-gray-400 whitespace-nowrap">{{ formatDate(user.created_at) }}</td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <button @click="openEdit(user)" class="px-2.5 py-1 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors">Edit</button>
                <button @click="confirmDelete(user)" class="px-2.5 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">Delete</button>
              </div>
            </td>
          </tr>
          <tr v-if="!filteredUsers.length">
            <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">No users found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-5">{{ editTarget ? 'Edit User' : 'New User' }}</h3>
        <form @submit.prevent="submitUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" required type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input v-model="form.email" required type="email" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
            <select v-model="form.role" required class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
              <option value="applicant">Applicant</option>
              <option value="hr-officer">HR Officer</option>
              <option value="hr-manager">HR Manager</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div v-if="!editTarget">
            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
            <input v-model="form.password" required type="password" placeholder="Min. 8 characters" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" />
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-2 text-sm bg-blue-700 text-white font-semibold rounded-lg hover:bg-blue-800 disabled:opacity-60">
              {{ saving ? 'Saving…' : (editTarget ? 'Save Changes' : 'Create User') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirm -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="deleteTarget = null"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-2">Delete User?</h3>
        <p class="text-sm text-gray-500 mb-6">
          <strong>{{ deleteTarget.name }}</strong> will be permanently removed.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="deleteTarget = null" class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="doDelete" :disabled="saving" class="px-4 py-2 text-sm bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-60">
            {{ saving ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { debounce } from 'lodash-es'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const loading      = ref(true)
const saving       = ref(false)
const users        = ref([])
const search       = ref('')
const showModal    = ref(false)
const editTarget   = ref(null)
const deleteTarget = ref(null)

const form = reactive({ name: '', email: '', role: 'applicant', password: '' })

const filteredUsers = computed(() => {
  const q = search.value.toLowerCase()
  return q ? users.value.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)) : users.value
})

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/users', { headers: authHeaders() })
    users.value = data.data ?? data
  } finally {
    loading.value = false
  }
}

const onSearch = debounce(() => {}, 0)

function openCreate() {
  editTarget.value = null
  Object.assign(form, { name: '', email: '', role: 'applicant', password: '' })
  showModal.value = true
}

function openEdit(user) {
  editTarget.value = user
  Object.assign(form, { name: user.name, email: user.email, role: user.role ?? 'applicant', password: '' })
  showModal.value = true
}

async function submitUser() {
  saving.value = true
  try {
    if (editTarget.value) {
      await axios.put(`/api/admin/users/${editTarget.value.id}`, { name: form.name, email: form.email, role: form.role }, { headers: authHeaders() })
    } else {
      await axios.post('/api/admin/users', form, { headers: authHeaders() })
    }
    showModal.value = false
    fetchUsers()
  } finally {
    saving.value = false
  }
}

function confirmDelete(u) { deleteTarget.value = u }

async function doDelete() {
  saving.value = true
  try {
    await axios.delete(`/api/admin/users/${deleteTarget.value.id}`, { headers: authHeaders() })
    deleteTarget.value = null
    fetchUsers()
  } finally {
    saving.value = false
  }
}

function initials(name) {
  return name?.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase() ?? '?'
}

function roleLabel(role) {
  return { applicant: 'Applicant', 'hr-officer': 'HR Officer', 'hr-manager': 'HR Manager', admin: 'Admin' }[role] ?? role
}

function roleClass(role) {
  return {
    admin:        'bg-purple-100 text-purple-700',
    'hr-manager': 'bg-blue-100 text-blue-700',
    'hr-officer': 'bg-teal-100 text-teal-700',
    applicant:    'bg-gray-100 text-gray-600',
  }[role] ?? 'bg-gray-100 text-gray-600'
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(fetchUsers)
</script>
