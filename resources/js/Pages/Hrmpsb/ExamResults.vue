<template>
  <HrmbsboardLayout title="Exam Results">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Exam Results</h1>
          <p v-if="vacancy" class="text-sm text-gray-500 mt-1">{{ vacancy.position_title }}</p>
        </div>
        <span
          v-if="vacancy"
          class="text-xs font-medium px-2 py-1 rounded-full"
          :class="vacancy.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
        >{{ vacancy.status }}</span>
      </div>

      <!-- Encode Form (secretariat only) -->
      <div v-if="canEncode" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Encode Score</h2>
        <form @submit.prevent="submitScore" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Applicant Token</label>
            <select v-model="form.application_id" class="input" required>
              <option value="">Select applicant…</option>
              <option v-for="app in applications" :key="app.id" :value="app.id">
                {{ app.unmasked ? app.name : app.token }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Exam Type</label>
            <select v-model="form.exam_type" class="input" required>
              <option value="TWE">TWE</option>
              <option value="CBWE">CBWE</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Raw Score</label>
            <input v-model.number="form.raw_score" type="number" step="0.01" min="0" max="100" class="input" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Score</label>
            <input v-model.number="form.max_score" type="number" step="0.01" min="1" max="100" class="input" required />
          </div>
          <div class="sm:col-span-4 flex justify-end">
            <button type="submit" :disabled="submitting" class="btn-primary">
              {{ submitting ? 'Saving…' : 'Save Score' }}
            </button>
          </div>
        </form>
        <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
      </div>

      <!-- Results Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Applicant</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">TWE</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">CBWE</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="loading">
              <td colspan="4" class="px-4 py-8 text-center text-gray-400">Loading…</td>
            </tr>
            <tr v-else-if="applications.length === 0">
              <td colspan="4" class="px-4 py-8 text-center text-gray-400">No applications found.</td>
            </tr>
            <tr v-for="app in applications" :key="app.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <span v-if="app.unmasked" class="font-medium text-gray-900">{{ app.name }}</span>
                <span v-else class="font-mono text-indigo-700 font-semibold">{{ app.token }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">{{ app.status }}</span>
              </td>
              <td class="px-4 py-3">
                <template v-if="getScore(app, 'TWE')">
                  <span class="font-semibold">{{ getScore(app, 'TWE').raw_score }}</span>
                  <span class="text-gray-400"> / {{ getScore(app, 'TWE').max_score }}</span>
                  <span class="ml-1 text-xs text-gray-500">({{ getScore(app, 'TWE').percentage }}%)</span>
                </template>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-4 py-3">
                <template v-if="getScore(app, 'CBWE')">
                  <span class="font-semibold">{{ getScore(app, 'CBWE').raw_score }}</span>
                  <span class="text-gray-400"> / {{ getScore(app, 'CBWE').max_score }}</span>
                  <span class="ml-1 text-xs text-gray-500">({{ getScore(app, 'CBWE').percentage }}%)</span>
                </template>
                <span v-else class="text-gray-400">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Navigation -->
      <div class="flex justify-between pt-2">
        <a :href="`/hrmpsb/qs-results/${vacancyId}`" class="btn-secondary">← QS Results</a>
        <a :href="`/hrmpsb/bei-rating/${vacancyId}`" class="btn-primary">BEI Rating →</a>
      </div>
    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import api from '@/services/api'

const props = defineProps({ vacancyId: Number })

const loading = ref(true)
const submitting = ref(false)
const error = ref(null)
const vacancy = ref(null)
const applications = ref([])
const canEncode = ref(false)

const form = ref({
  application_id: '',
  exam_type: 'TWE',
  raw_score: '',
  max_score: 100,
})

function getScore(app, type) {
  return app.exam_results?.find(r => r.exam_type === type) ?? null
}

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/exam-results/${props.vacancyId}`)
    vacancy.value = data.vacancy
    applications.value = data.applications
    canEncode.value = data.can_encode ?? false
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load exam results.'
  } finally {
    loading.value = false
  }
}

async function submitScore() {
  submitting.value = true
  error.value = null
  try {
    await api.post('/exam-results', {
      ...form.value,
      vacancy_id: props.vacancyId,
    })
    form.value = { application_id: '', exam_type: 'TWE', raw_score: '', max_score: 100 }
    await load()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to save score.'
  } finally {
    submitting.value = false
  }
}

onMounted(load)
</script>

<style scoped>
@reference "../../../css/app.css";
.input {
  @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent;
}
.btn-primary {
  @apply inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition;
}
.btn-secondary {
  @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 transition;
}
</style>
