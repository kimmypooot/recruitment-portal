<template>
  <AdminLayout title="Audit Logs">

    <!-- Filters -->
    <div class="flex flex-wrap gap-2 mb-6">
      <input
        v-model="search"
        @input="onSearch"
        type="text"
        placeholder="Search action or model..."
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none w-60" />
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-8 space-y-3">
        <div v-for="n in 8" :key="n" class="h-10 bg-gray-100 rounded animate-pulse"></div>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3">Action</th>
            <th class="px-5 py-3">Model</th>
            <th class="px-5 py-3">Record ID</th>
            <th class="px-5 py-3">User</th>
            <th class="px-5 py-3">Date & Time</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3">
              <span :class="actionClass(log.action)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ log.action }}
              </span>
            </td>
            <td class="px-5 py-3 text-gray-600 font-mono text-xs">{{ shortModel(log.auditable_type) }}</td>
            <td class="px-5 py-3 text-gray-400 font-mono text-xs">#{{ log.auditable_id }}</td>
            <td class="px-5 py-3 text-gray-600">{{ log.user_id ?? 'System' }}</td>
            <td class="px-5 py-3 text-gray-400 whitespace-nowrap text-xs">{{ formatDateTime(log.created_at) }}</td>
          </tr>
          <tr v-if="!filteredLogs.length">
            <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">No audit logs found.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span>Page {{ meta.current_page }} of {{ meta.last_page }}</span>
        <div class="flex gap-2">
          <button :disabled="meta.current_page === 1" @click="goPage(meta.current_page - 1)"
            class="px-3 py-1 rounded border border-gray-300 disabled:opacity-40 hover:bg-gray-50">Prev</button>
          <button :disabled="meta.current_page === meta.last_page" @click="goPage(meta.current_page + 1)"
            class="px-3 py-1 rounded border border-gray-300 disabled:opacity-40 hover:bg-gray-50">Next</button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { debounce } from 'lodash-es'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const loading = ref(true)
const logs    = ref([])
const meta    = ref({})
const search  = ref('')
const page    = ref(1)

const filteredLogs = computed(() => {
  const q = search.value.toLowerCase()
  return q ? logs.value.filter(l =>
    l.action?.toLowerCase().includes(q) ||
    l.auditable_type?.toLowerCase().includes(q)
  ) : logs.value
})

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function fetchLogs() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/audit-logs', {
      params: { page: page.value },
      headers: authHeaders(),
    })
    logs.value = data.data ?? data
    meta.value = data.meta ?? {}
  } finally {
    loading.value = false
  }
}

const onSearch = debounce(() => {}, 0)
function goPage(p) { page.value = p; fetchLogs() }

function shortModel(type) {
  return type?.split('\\').pop() ?? '—'
}

function actionClass(action) {
  return {
    created:   'bg-green-100 text-green-700',
    updated:   'bg-blue-100 text-blue-700',
    deleted:   'bg-red-100 text-red-700',
    published: 'bg-teal-100 text-teal-700',
    archived:  'bg-yellow-100 text-yellow-700',
  }[action] ?? 'bg-gray-100 text-gray-600'
}

function formatDateTime(str) {
  if (!str) return '—'
  return new Date(str).toLocaleString('en-PH', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

onMounted(fetchLogs)
</script>
