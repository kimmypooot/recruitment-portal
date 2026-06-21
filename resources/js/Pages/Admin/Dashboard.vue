<template>
  <AdminLayout title="Dashboard">

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
      <div v-for="card in statCards" :key="card.label"
        class="bg-white rounded-xl border border-gray-200 p-5 flex items-start gap-4 shadow-sm">
        <div :class="card.iconBg" class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5" :class="card.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon"/>
          </svg>
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium">{{ card.label }}</p>
          <p v-if="!loading" class="text-2xl font-bold text-gray-900 mt-0.5">{{ card.value }}</p>
          <div v-else class="h-7 w-14 bg-gray-200 rounded animate-pulse mt-0.5"></div>
          <p v-if="card.sub" class="text-xs text-gray-400 mt-0.5">{{ card.sub }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Pipeline -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Application Pipeline</h2>
        <div v-if="loading" class="space-y-3">
          <div v-for="n in 5" :key="n" class="h-8 bg-gray-100 rounded animate-pulse"></div>
        </div>
        <div v-else class="space-y-3">
          <div v-for="item in pipeline" :key="item.status" class="flex items-center gap-3">
            <span class="w-28 text-xs text-gray-500 truncate capitalize">{{ item.status.replace('_', ' ') }}</span>
            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
              <div
                class="h-2 rounded-full bg-blue-600 transition-all duration-500"
                :style="{ width: pipelineMax ? (item.count / pipelineMax * 100) + '%' : '0%' }">
              </div>
            </div>
            <span class="text-xs font-semibold text-gray-700 w-6 text-right">{{ item.count }}</span>
          </div>
          <p v-if="!pipeline.length" class="text-sm text-gray-400 text-center py-4">No data yet.</p>
        </div>
      </div>

      <!-- Recent Applications -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-sm font-semibold text-gray-900">Recent Applications</h2>
          <Link href="/admin/applications" class="text-xs text-blue-700 hover:underline font-medium">View all</Link>
        </div>
        <div v-if="loading" class="space-y-3">
          <div v-for="n in 5" :key="n" class="h-10 bg-gray-100 rounded animate-pulse"></div>
        </div>
        <div v-else-if="recentApplications.length" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                <th class="pb-2 font-medium">Applicant</th>
                <th class="pb-2 font-medium">Position</th>
                <th class="pb-2 font-medium">Status</th>
                <th class="pb-2 font-medium">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="app in recentApplications" :key="app.id" class="hover:bg-gray-50 transition-colors">
                <td class="py-2.5 pr-4 text-gray-900 font-medium">{{ app.applicant?.user?.name ?? '—' }}</td>
                <td class="py-2.5 pr-4 text-gray-600 truncate max-w-[160px]">{{ app.vacancy?.position_title ?? '—' }}</td>
                <td class="py-2.5 pr-4">
                  <StatusBadge :status="app.status" />
                </td>
                <td class="py-2.5 text-gray-400 whitespace-nowrap">{{ formatDate(app.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-sm text-gray-400 text-center py-8">No applications yet.</p>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

const loading            = ref(true)
const stats              = ref({})
const pipeline           = ref([])
const recentApplications = ref([])

const pipelineMax = computed(() => Math.max(...pipeline.value.map(p => p.count), 1))

const statCards = computed(() => [
  {
    label: 'Total Vacancies',
    value: stats.value.vacancies?.total ?? 0,
    sub: `${stats.value.vacancies?.published ?? 0} published`,
    icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    iconBg: 'bg-blue-100', iconColor: 'text-blue-700',
  },
  {
    label: 'Closing Soon',
    value: stats.value.vacancies?.closing_soon ?? 0,
    sub: 'within 7 days',
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    iconBg: 'bg-yellow-100', iconColor: 'text-yellow-700',
  },
  {
    label: 'Total Applications',
    value: stats.value.applications?.total ?? 0,
    sub: `${stats.value.applications?.this_month ?? 0} this month`,
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    iconBg: 'bg-green-100', iconColor: 'text-green-700',
  },
  {
    label: 'Pending Review',
    value: stats.value.applications?.pending ?? 0,
    sub: 'awaiting action',
    icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    iconBg: 'bg-red-100', iconColor: 'text-red-700',
  },
])

async function loadDashboard() {
  try {
    const [statsRes, pipelineRes, recentRes] = await Promise.all([
      axios.get('/api/dashboard/stats'),
      axios.get('/api/dashboard/pipeline'),
      axios.get('/api/dashboard/recent-applications'),
    ])
    stats.value              = statsRes.data
    pipeline.value           = pipelineRes.data
    recentApplications.value = recentRes.data
  } catch (e) {
    console.error('Dashboard load failed', e)
  } finally {
    loading.value = false
  }
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(loadDashboard)
</script>
