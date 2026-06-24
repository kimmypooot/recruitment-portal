<template>
  <HrmbsboardLayout title="Final Deliberation" :vacancyId="props.vacancyId">
    <div class="space-y-6">

      <!-- Vacancy Banner -->
      <VacancyBanner
        :vacancy="vacancy"
        :stage="4"
        stageLabel="Final Deliberation &amp; Selection"
        :loading="loading"
      />

      <!-- Actions row -->
      <div class="flex items-center justify-end">
        <!-- Unmask button -->
        <button
          v-if="canUnmask && !allUnmasked"
          @click="confirmUnmask = true"
          class="btn-danger"
        >Reveal Identities</button>
      </div>

      <!-- Unmask confirmation -->
      <div v-if="confirmUnmask" class="bg-red-50 border border-red-300 rounded-xl p-4 flex items-center justify-between">
        <p class="text-sm text-red-800 font-medium">
          This will reveal all applicant identities to all deliberation participants. This action cannot be undone.
        </p>
        <div class="flex gap-2">
          <button @click="confirmUnmask = false" class="btn-secondary">Cancel</button>
          <button @click="unmaskAll" :disabled="unmasking" class="btn-danger">
            {{ unmasking ? 'Revealing…' : 'Confirm Reveal' }}
          </button>
        </div>
      </div>

      <!-- Unmasked banner -->
      <div v-if="allUnmasked" class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-sm text-blue-800 font-medium">
        Applicant identities have been revealed for final deliberation.
      </div>

      <!-- Consolidated Assessment Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="font-semibold text-gray-800">Comparative Assessment</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Applicant</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">QS</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">TWE</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">CBWE</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">BEI Avg</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Action</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Rank</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Decision</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-if="loading">
                <td colspan="8" class="px-4 py-8 text-center text-gray-400">Loading…</td>
              </tr>
              <tr v-else-if="applications.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-400">No applications in deliberation.</td>
              </tr>
              <tr v-for="app in applications" :key="app.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <div v-if="app.unmasked">
                    <p class="font-medium text-gray-900">{{ app.name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ app.token }}</p>
                  </div>
                  <span v-else class="font-mono text-indigo-700 font-semibold">{{ app.token }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                    :class="app.qs_result === 'qualified' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'">
                    {{ app.qs_result ?? '—' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  {{ app.exam_scores?.TWE?.percentage != null ? app.exam_scores.TWE.percentage + '%' : '—' }}
                </td>
                <td class="px-4 py-3 text-center">
                  {{ app.exam_scores?.CBWE?.percentage != null ? app.exam_scores.CBWE.percentage + '%' : '—' }}
                </td>
                <td class="px-4 py-3 text-center font-semibold">
                  {{ app.bei_average ?? '—' }}
                </td>
                <td class="px-4 py-3 text-center">
                  <select
                    v-if="canDecide"
                    v-model="decisions[app.id].action"
                    class="text-xs border border-gray-300 rounded px-2 pr-7 py-1"
                  >
                    <option value="">—</option>
                    <option value="endorsed">Endorsed</option>
                    <option value="appointed">Appointed</option>
                    <option value="not_recommended">Not Recommended</option>
                  </select>
                  <span v-else class="text-xs text-gray-500">{{ app.deliberation?.action ?? '—' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <input
                    v-if="canDecide"
                    v-model.number="decisions[app.id].rank"
                    type="number"
                    min="1"
                    class="w-14 text-xs border border-gray-300 rounded px-2 py-1 text-center"
                    placeholder="—"
                  />
                  <span v-else>{{ app.deliberation?.rank ?? '—' }}</span>
                </td>
                <td class="px-4 py-3">
                  <button
                    v-if="canDecide && decisions[app.id].action"
                    @click="saveDecision(app.id)"
                    :disabled="saving[app.id]"
                    class="text-xs px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50"
                  >{{ saving[app.id] ? '…' : 'Save' }}</button>
                  <span v-else-if="app.deliberation?.action" class="text-xs text-green-600 font-medium">✓ Saved</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <!-- Back nav -->
      <div class="flex justify-start pt-2">
        <a :href="`/hrmpsb/bei-rating/${vacancyId}`" class="btn-secondary">← BEI Rating</a>
      </div>
    </div>
  </HrmbsboardLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import HrmbsboardLayout from '@/Layouts/HrmbsboardLayout.vue'
import VacancyBanner from '@/Components/Hrmpsb/VacancyBanner.vue'
import api from '@/services/api'

const props = defineProps({ vacancyId: Number })

const loading = ref(true)
const unmasking = ref(false)
const confirmUnmask = ref(false)
const error = ref(null)
const vacancy = ref(null)
const applications = ref([])
const canUnmask = ref(false)
const canDecide = ref(false)

const decisions = reactive({})
const saving = reactive({})

const allUnmasked = computed(() => applications.value.every(a => a.unmasked))

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/deliberation/${props.vacancyId}`)
    vacancy.value = data.vacancy
    applications.value = data.applications
    canUnmask.value = data.can_unmask
    canDecide.value = data.can_unmask // same roles

    // Init decision rows
    data.applications.forEach(app => {
      decisions[app.id] = {
        action: app.deliberation?.action ?? '',
        rank: app.deliberation?.rank ?? null,
      }
    })
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load deliberation data.'
  } finally {
    loading.value = false
  }
}

async function unmaskAll() {
  unmasking.value = true
  error.value = null
  try {
    await api.patch(`/deliberation/${props.vacancyId}/unmask`)
    confirmUnmask.value = false
    await load()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to reveal identities.'
  } finally {
    unmasking.value = false
  }
}

async function saveDecision(applicationId) {
  saving[applicationId] = true
  error.value = null
  try {
    await api.post(`/deliberation/${props.vacancyId}/decide`, {
      application_id: applicationId,
      action: decisions[applicationId].action,
      rank: decisions[applicationId].rank || null,
    })
    await load()
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to save decision.'
  } finally {
    saving[applicationId] = false
  }
}

onMounted(load)
</script>

<style scoped>
@reference "../../../css/app.css";
.btn-primary {
  @apply inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition;
}
.btn-secondary {
  @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 transition;
}
.btn-danger {
  @apply inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 transition;
}
</style>
