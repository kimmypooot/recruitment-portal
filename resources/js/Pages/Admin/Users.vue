<template>
  <AdminLayout title="User Management">

    <!-- Stats row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="stat in statCards" :key="stat.label"
        class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4 flex items-center gap-4">
        <div :class="stat.iconBg" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5" :class="stat.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon"/>
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-1">
            <p class="text-xs text-gray-500 font-medium">{{ stat.label }}</p>
            <span v-if="stat.tooltip" :title="stat.tooltip"
              class="w-3.5 h-3.5 rounded-full bg-gray-100 text-gray-400 text-[9px] flex items-center justify-center cursor-help flex-shrink-0 font-bold leading-none">?</span>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-0.5">
            <span v-if="loading" class="inline-block h-6 w-8 bg-gray-200 rounded animate-pulse"></span>
            <span v-else>{{ stat.value }}</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
      <div class="flex flex-wrap items-center gap-2">

        <!-- Search -->
        <div class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="search" type="text" placeholder="Search name or email…"
            class="pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none w-60" />
        </div>

        <!-- Role filter dropdown -->
        <select v-model="roleFilter" @change="onRoleChange"
          class="px-3 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
          <option value="">All Roles</option>
          <option value="applicant">Applicant</option>
          <option value="hrmpsb">HRMPSB</option>
          <option value="admin">Admin</option>
        </select>

        <span v-if="!loading" class="text-xs text-gray-400">
          {{ users.length }} total
        </span>
      </div>

      <button @click="openCreate"
        class="flex items-center gap-2 px-4 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors flex-shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New User
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

      <!-- Loading -->
      <div v-if="loading" class="p-6 space-y-3">
        <div v-for="n in 6" :key="n" class="flex items-center gap-4 h-14">
          <div class="w-10 h-10 rounded-full bg-gray-100 animate-pulse flex-shrink-0"></div>
          <div class="flex-1 space-y-1.5">
            <div class="h-3.5 bg-gray-100 rounded w-1/4 animate-pulse"></div>
            <div class="h-3 bg-gray-100 rounded w-1/3 animate-pulse"></div>
          </div>
          <div class="h-5 w-20 bg-gray-100 rounded-full animate-pulse"></div>
          <div class="h-3 w-16 bg-gray-100 rounded animate-pulse"></div>
          <div class="flex gap-2">
            <div class="h-7 w-12 bg-gray-100 rounded animate-pulse"></div>
            <div class="h-7 w-14 bg-gray-100 rounded animate-pulse"></div>
          </div>
        </div>
      </div>

      <div v-else class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3 w-12">#</th>
            <th class="px-5 py-3">User</th>
            <th class="px-5 py-3">Role</th>
            <th class="px-5 py-3 hidden md:table-cell">Joined</th>
            <th class="px-5 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="(user, idx) in paginatedUsers" :key="user.id"
            class="hover:bg-gray-50/80 transition-colors group">
            <td class="px-5 py-3.5 text-xs text-gray-300 font-medium">
              {{ (currentPage - 1) * perPage + idx + 1 }}
            </td>
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="relative w-9 h-9 rounded-full flex-shrink-0 overflow-hidden">
                  <img v-if="user.photo_url" :src="user.photo_url"
                    class="w-full h-full object-cover rounded-full"
                    @error="e => { e.target.style.display = 'none'; e.target.nextElementSibling.style.display = 'flex' }"
                    alt="" />
                  <div :class="[avatarBg(user.role), user.photo_url ? 'hidden' : '']"
                    class="w-full h-full rounded-full flex items-center justify-center text-xs font-bold">
                    {{ initials(user) }}
                  </div>
                </div>
                <div class="min-w-0">
                  <p class="font-medium text-gray-900 truncate">{{ user.last_name }}, {{ user.first_name }}{{ user.middle_name ? ' ' + user.middle_name.charAt(0).toUpperCase() + '.' : '' }}{{ user.suffix ? ', ' + user.suffix : '' }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3.5">
              <span :class="roleClass(user.role)"
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold">
                {{ roleLabel(user.role) }}
              </span>
            </td>
            <td class="px-5 py-3.5 text-xs text-gray-400 hidden md:table-cell whitespace-nowrap">
              {{ formatDate(user.created_at) }}
            </td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-1.5">
                <button @click="openEdit(user)"
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-[#2a338f] bg-[#2a338f]/8 hover:bg-[#2a338f]/15 rounded-md transition-colors">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                  Edit
                </button>
                <button @click="confirmDelete(user)"
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  Delete
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!filteredUsers.length">
            <td colspan="5" class="px-5 py-16 text-center">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm font-medium text-gray-400">No users match your filters</p>
                <button @click="search = ''; roleFilter = ''; fetchUsers()"
                  class="text-xs text-[#2a338f] hover:underline">Clear filters</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

      <!-- Pagination -->
      <div v-if="!loading && totalPages > 1"
        class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span class="text-xs">
          Showing <strong class="text-gray-700">{{ (currentPage - 1) * perPage + 1 }}</strong>–<strong class="text-gray-700">{{ Math.min(currentPage * perPage, filteredUsers.length) }}</strong>
          of <strong class="text-gray-700">{{ filteredUsers.length }}</strong>
        </span>
        <div class="flex items-center gap-1">
          <button :disabled="currentPage === 1" @click="currentPage--"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
          </button>
          <button v-for="p in visiblePages" :key="p" @click="typeof p === 'number' && (currentPage = p)"
            :disabled="p === '…'"
            :class="['px-2.5 py-1 rounded-lg text-xs font-medium transition-colors',
              p === currentPage ? 'bg-[#2a338f] text-white' : p === '…' ? 'text-gray-300 cursor-default' : 'text-gray-600 hover:bg-gray-100']">
            {{ p }}
          </button>
          <button :disabled="currentPage === totalPages" @click="currentPage++"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- ── Create / Edit Modal ─────────────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="showModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col" style="max-height: 90vh;">

        <!-- Header -->
        <div class="flex items-start gap-4 px-6 pt-6 pb-4 border-b border-gray-100">
          <div v-if="editTarget" class="relative w-12 h-12 rounded-full flex-shrink-0 overflow-hidden">
            <img v-if="editTarget.photo_url" :src="editTarget.photo_url"
              class="w-full h-full object-cover rounded-full"
              @error="e => { e.target.style.display = 'none'; e.target.nextElementSibling.style.display = 'flex' }"
              alt="" />
            <div :class="[avatarBg(form.role), editTarget.photo_url ? 'hidden' : '']"
              class="w-full h-full rounded-full flex items-center justify-center text-sm font-bold">
              {{ initials({ first_name: form.first_name, last_name: form.last_name }) || initials(editTarget) }}
            </div>
          </div>
          <div v-else class="w-12 h-12 rounded-full bg-[#2a338f]/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="text-base font-semibold text-gray-900">
              {{ editTarget ? 'Edit User' : 'New User' }}
            </h3>
            <p v-if="editTarget" class="text-xs text-gray-400 truncate mt-0.5">{{ editTarget.email }}</p>
            <p v-else class="text-xs text-gray-400 mt-0.5">Fill in the details to create a new system user.</p>
          </div>
          <button @click="showModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Tab bar (edit only — shows Security tab) -->
        <div v-if="editTarget" class="flex border-b border-gray-100 px-6 bg-gray-50/50">
          <button v-for="tab in ['Account', 'Security']" :key="tab" @click="modalTab = tab"
            class="px-4 py-2.5 text-sm font-medium -mb-px border-b-2 transition-colors"
            :class="modalTab === tab
              ? 'text-[#2a338f] border-[#2a338f]'
              : 'text-gray-500 border-transparent hover:text-gray-700'">
            {{ tab }}
          </button>
        </div>

        <!-- Body (scrollable) -->
        <form @submit.prevent="submitUser" class="flex flex-col flex-1 overflow-hidden">
          <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">

            <!-- Account tab -->
            <template v-if="modalTab === 'Account'">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                  <input v-model="form.first_name" required type="text" autocomplete="off"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                  <input v-model="form.last_name" required type="text" autocomplete="off"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                  <input v-model="form.middle_name" type="text" autocomplete="off" placeholder="Optional"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Suffix</label>
                  <select v-model="form.suffix"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
                    <option value="">None</option>
                    <option>Jr.</option>
                    <option>Sr.</option>
                    <option>II</option>
                    <option>III</option>
                    <option>IV</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                <input v-model="form.email" required type="email" autocomplete="off"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">System Role <span class="text-red-500">*</span></label>
                <select v-model="form.role" required
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
                  <option value="applicant">Applicant</option>
                  <option value="hrmpsb">HRMPSB</option>
                  <option value="admin">Admin</option>
                </select>
                <!-- Role description -->
                <p class="mt-1.5 text-xs text-gray-400">{{ roleDescription(form.role) }}</p>
              </div>
              <!-- Password only on create -->
              <div v-if="!editTarget">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                <input v-model="form.password" required type="password" placeholder="Minimum 8 characters" autocomplete="new-password"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
              </div>
            </template>

            <!-- Security tab (edit only) -->
            <template v-else-if="modalTab === 'Security'">
              <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 flex gap-2.5 text-xs text-amber-700">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                Leave blank to keep the current password. The user will be required to use the new password on their next login.
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input v-model="form.password" type="password" placeholder="Enter new password…" autocomplete="new-password"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" placeholder="Re-enter new password…"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
                <p v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation"
                  class="mt-1 text-xs text-red-500">Passwords do not match.</p>
              </div>
            </template>

          </div>

          <!-- Footer -->
          <div class="flex justify-between items-center gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl flex-shrink-0">
            <button type="button" @click="showModal = false"
              class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
              Cancel
            </button>
            <button type="submit"
              :disabled="saving || (modalTab === 'Security' && form.password && form.password !== form.password_confirmation)"
              class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-60 transition-colors">
              <span v-if="saving" class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Saving…
              </span>
              <span v-else>{{ editTarget ? 'Save Changes' : 'Create User' }}</span>
            </button>
          </div>
        </form>

      </div>
    </div>
    </Teleport>

    <!-- ── Delete Confirm ─────────────────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="deleteTarget = null"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-semibold text-gray-900">Delete User?</h3>
            <p class="text-xs text-gray-400 mt-0.5">This action cannot be undone.</p>
          </div>
        </div>
        <div class="bg-gray-50 rounded-lg p-3 mb-5">
          <p class="text-sm font-medium text-gray-900">{{ deleteTarget.full_name }}</p>
          <p class="text-xs text-gray-400 mt-0.5">{{ deleteTarget.email }}</p>
          <span :class="roleClass(deleteTarget.role)"
            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold mt-2">
            {{ roleLabel(deleteTarget.role) }}
          </span>
        </div>
        <p class="text-sm text-gray-500 mb-5">
          Deleting this user will permanently remove their account and all associated data including applications and profile information.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="deleteTarget = null"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            Cancel
          </button>
          <button @click="doDelete" :disabled="saving"
            class="px-4 py-2 text-sm bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-60 transition-colors">
            {{ saving ? 'Deleting…' : 'Yes, Delete User' }}
          </button>
        </div>
      </div>
    </div>
    </Teleport>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

// ── State ────────────────────────────────────────────────────────────────────────
const loading      = ref(true)
const saving       = ref(false)
const users        = ref([])
const search       = ref('')
const roleFilter   = ref('')
const showModal    = ref(false)
const editTarget   = ref(null)
const deleteTarget = ref(null)
const modalTab     = ref('Account')
const currentPage  = ref(1)
const perPage      = 15

const form = reactive({
  first_name: '', last_name: '', middle_name: '', suffix: '',
  email: '', role: 'applicant', password: '', password_confirmation: '',
})

// ── Computed ─────────────────────────────────────────────────────────────────────
const filteredUsers = computed(() => {
  let list = users.value

  const q = search.value.toLowerCase().trim()
  if (q) {
    list = list.filter(u =>
      (u.full_name ?? '').toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
    )
  }

  return list
})

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / perPage))

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredUsers.value.slice(start, start + perPage)
})

const visiblePages = computed(() => {
  const total = totalPages.value
  const cur   = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  if (cur <= 4)   return [1, 2, 3, 4, 5, '…', total]
  if (cur >= total - 3) return [1, '…', total - 4, total - 3, total - 2, total - 1, total]
  return [1, '…', cur - 1, cur, cur + 1, '…', total]
})

const statCards = computed(() => [
  {
    label: 'Total Users', value: users.value.length,
    tooltip: 'All registered system accounts regardless of role.',
    icon:  'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    iconBg: 'bg-[#2a338f]/10', iconColor: 'text-[#2a338f]',
  },
  {
    label: 'Applicants', value: users.value.filter(u => u.role === 'applicant').length,
    tooltip: 'Users who can browse vacancies and submit applications.',
    icon:  'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    iconBg: 'bg-gray-100', iconColor: 'text-gray-500',
  },
  {
    label: 'Admins', value: users.value.filter(u => u.role === 'admin').length,
    tooltip: 'Full system access including user management and audit logs.',
    icon:  'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    iconBg: 'bg-purple-50', iconColor: 'text-purple-600',
  },
  {
    label: 'HRMPSB', value: users.value.filter(u => u.role === 'hrmpsb').length,
    tooltip: 'HRMPSB members with access to evaluations, ratings, and deliberation. Designation assigned separately.',
    icon:  'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    iconBg: 'bg-amber-50', iconColor: 'text-amber-600',
  },
])

// Reset to page 1 when search changes
watch(search, () => { currentPage.value = 1 })

// ── Auth ─────────────────────────────────────────────────────────────────────────
function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

// ── CRUD ──────────────────────────────────────────────────────────────────────────
async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/users', {
      params: { role: roleFilter.value || undefined },
      headers: authHeaders(),
    })
    users.value = data.data ?? data
  } finally {
    loading.value = false
  }
}

function onRoleChange() { currentPage.value = 1; fetchUsers() }

function openCreate() {
  editTarget.value = null
  modalTab.value   = 'Account'
  Object.assign(form, { first_name: '', last_name: '', middle_name: '', suffix: '', email: '', role: 'applicant', password: '', password_confirmation: '' })
  showModal.value = true
}

function openEdit(user) {
  editTarget.value = user
  modalTab.value   = 'Account'
  Object.assign(form, {
    first_name: user.first_name ?? '',
    last_name: user.last_name ?? '',
    middle_name: user.middle_name ?? '',
    suffix: user.suffix ?? '',
    email: user.email,
    role: user.role ?? 'applicant',
    password: '',
    password_confirmation: '',
  })
  showModal.value = true
}

async function submitUser() {
  if (modalTab.value === 'Security' && form.password && form.password !== form.password_confirmation) return

  saving.value = true
  try {
    if (editTarget.value) {
      const payload = {
        first_name: form.first_name,
        last_name: form.last_name,
        middle_name: form.middle_name || null,
        suffix: form.suffix || null,
        email: form.email,
        role: form.role,
      }
      if (form.password) { payload.password = form.password; payload.password_confirmation = form.password_confirmation }
      await axios.put(`/api/admin/users/${editTarget.value.id}`, payload, { headers: authHeaders() })
      toast.success('User updated successfully.')
    } else {
      await axios.post('/api/admin/users', {
        first_name: form.first_name,
        last_name: form.last_name,
        middle_name: form.middle_name || null,
        suffix: form.suffix || null,
        email: form.email,
        role: form.role,
        password: form.password,
        password_confirmation: form.password_confirmation,
      }, { headers: authHeaders() })
      toast.success('User created successfully.')
    }
    showModal.value = false
    fetchUsers()
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Something went wrong. Please try again.'
    toast.error(msg)
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
    toast.error('User deleted.')
    fetchUsers()
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Failed to delete user.'
    toast.error(msg)
  } finally {
    saving.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────────
function initials(user) {
  if (typeof user === 'object' && user) {
    return ((user.first_name?.[0] ?? '') + (user.last_name?.[0] ?? '')).toUpperCase() || '?'
  }
  return String(user ?? '').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase() || '?'
}

function avatarBg(role) {
  return {
    admin:      'bg-purple-100 text-purple-700',
    hrmpsb:     'bg-amber-100 text-amber-700',
    applicant:  'bg-gray-100 text-gray-600',
  }[role] ?? 'bg-gray-100 text-gray-600'
}

function roleClass(role) {
  return {
    admin:      'bg-purple-100 text-purple-700',
    hrmpsb:     'bg-amber-100 text-amber-700',
    applicant:  'bg-gray-100 text-gray-600',
  }[role] ?? 'bg-gray-100 text-gray-600'
}

function roleLabel(role) {
  return {
    applicant:  'Applicant',
    hrmpsb:     'HRMPSB',
    admin:      'Admin',
  }[role] ?? role
}

function roleDescription(role) {
  return {
    applicant:  'Can browse vacancies and submit applications.',
    hrmpsb:     'Access to the HRMPSB portal for evaluations and ratings. Designation assigned separately.',
    admin:      'Full system access including user management and audit logs.',
  }[role] ?? ''
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(fetchUsers)
</script>
