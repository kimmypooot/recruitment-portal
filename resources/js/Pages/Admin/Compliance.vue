<template>
  <AdminLayout title="Compliance Tracking">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 class="text-xl font-bold text-gray-900">Compliance Tracking</h1>
          <p class="text-sm text-gray-500 mt-1">30-day CSC appointment submission deadlines.</p>
        </div>
        <div class="flex gap-3">
          <button @click="load" :disabled="loading" class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 transition">
            Refresh
          </button>
        </div>
      </div>

      <!-- Summary cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
          <p class="text-xs text-gray-500 font-medium">Total Tracked</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ rows.length }}</p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-200 shadow-sm p-5">
          <p class="text-xs text-red-600 font-medium">Overdue</p>
          <p class="text-2xl font-bold text-red-700 mt-1">{{ overdueCount }}</p>
          <p class="text-xs text-red-500 mt-0.5">Past 30-day deadline</p>
        </div>
        <div class="bg-amber-50 rounded-xl border border-amber-200 shadow-sm p-5">
          <p class="text-xs text-amber-600 font-medium">Due Within 5 Days</p>
          <p class="text-2xl font-bold text-amber-700 mt-1">{{ urgentCount }}</p>
          <p class="text-xs text-amber-500 mt-0.5">Act immediately</p>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
          <h2 class="text-sm font-semibold text-gray-800">Submission Deadlines</h2>
          <div class="flex gap-2">
            <button v-for="f in filters" :key="f.value" @click="activeFilter = f.value"
              class="px-3 py-1 text-xs font-medium rounded-full transition"
              :class="activeFilter === f.value ? 'bg-[#2a338f] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
              {{ f.label }}
            </button>
          </div>
        </div>

        <div v-if="loading" class="p-8 space-y-3">
          <div v-for="n in 4" :key="n" class="h-12 bg-gray-100 rounded animate-pulse"></div>
        </div>

        <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="bg-gray-50">
            <tr class="text-xs text-gray-500 font-semibold uppercase tracking-wider text-left">
              <th class="px-5 py-3">Appointee</th>
              <th class="px-5 py-3">Position</th>
              <th class="px-5 py-3">Due Date</th>
              <th class="px-5 py-3">Days Remaining</th>
              <th class="px-5 py-3">Status</th>
              <th class="px-5 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="filteredRows.length === 0">
              <td colspan="6" class="px-5 py-10 text-center text-gray-400">No records found.</td>
            </tr>
            <tr v-for="row in filteredRows" :key="row.application_id" class="hover:bg-gray-50 transition">
              <td class="px-5 py-3.5 font-medium text-gray-900">{{ row.appointee }}</td>
              <td class="px-5 py-3.5 text-gray-600">{{ row.position }}</td>
              <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">{{ formatDate(row.due_at) }}</td>
              <td class="px-5 py-3.5">
                <span v-if="row.status === 'submitted'" class="text-green-600 font-medium">Submitted</span>
                <span v-else-if="row.days_remaining < 0" class="text-red-600 font-bold">{{ Math.abs(row.days_remaining) }}d overdue</span>
                <span v-else-if="row.days_remaining <= 5" class="text-amber-600 font-bold">{{ row.days_remaining }}d left</span>
                <span v-else class="text-gray-600">{{ row.days_remaining }}d left</span>
              </td>
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="{
                    'bg-green-100 text-green-700': row.status === 'submitted',
                    'bg-red-100 text-red-700': row.status === 'overdue' || row.days_remaining < 0,
                    'bg-amber-100 text-amber-700': row.status === 'pending' && row.days_remaining <= 5 && row.days_remaining >= 0,
                    'bg-gray-100 text-gray-600': row.status === 'pending' && row.days_remaining > 5,
                  }">
                  {{ statusLabel(row) }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-right">
                <a :href="`/admin/reports`" class="text-xs text-[#2a338f] hover:underline font-medium">Generate Form →</a>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const loading      = ref(true)
const rows         = ref([])
const activeFilter = ref('all')

const filters = [
  { value: 'all',      label: 'All' },
  { value: 'pending',  label: 'Pending' },
  { value: 'overdue',  label: 'Overdue' },
  { value: 'submitted',label: 'Submitted' },
]

const overdueCount = computed(() => rows.value.filter(r => r.status === 'overdue' || r.days_remaining < 0).length)
const urgentCount  = computed(() => rows.value.filter(r => r.status === 'pending' && r.days_remaining >= 0 && r.days_remaining <= 5).length)

const filteredRows = computed(() => {
  if (activeFilter.value === 'all') return rows.value
  if (activeFilter.value === 'overdue') return rows.value.filter(r => r.status === 'overdue' || r.days_remaining < 0)
  return rows.value.filter(r => r.status === activeFilter.value)
})

function statusLabel(row) {
  if (row.status === 'submitted') return 'Submitted'
  if (row.days_remaining < 0 || row.status === 'overdue') return 'Overdue'
  if (row.days_remaining <= 5) return 'Urgent'
  return 'Pending'
}

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function load() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/reports/compliance-deadlines', { headers: authHeaders() })
    rows.value = data.rows ?? []
  } catch (e) {
    console.error('Failed to load compliance data', e)
  } finally {
    loading.value = false
  }
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(load)
</script>
