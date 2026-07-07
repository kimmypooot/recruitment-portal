<template>
  <BaseModal :show="open" title="Batch Update Status" max-width="max-w-md"
    panel-class="flex flex-col" @close="close">
        <div class="px-6 py-5 border-b border-gray-100">
          <h3 class="text-base font-semibold text-gray-900">Batch Update Status</h3>
          <p class="text-xs text-gray-400 mt-1">
            Applying to
            <span class="font-semibold text-gray-700">{{ ids.length }}</span>
            applicant{{ ids.length !== 1 ? 's' : '' }}
          </p>
        </div>

        <div class="px-6 py-5 space-y-4">
          <!-- Progress bar (shown while saving) -->
          <div v-if="saving" class="space-y-2">
            <div class="flex items-center justify-between text-xs text-gray-500">
              <span>Updating… {{ progress }}/{{ ids.length }}</span>
              <span>{{ Math.round((progress / ids.length) * 100) }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
              <div class="bg-primary h-1.5 rounded-full transition-all duration-300"
                :style="{ width: `${(progress / ids.length) * 100}%` }"></div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Status <span class="text-red-500">*</span></label>
            <select v-model="form.status" :disabled="saving"
              class="w-full px-3 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none bg-white disabled:opacity-60">
              <option value="" disabled>Select a status…</option>
              <optgroup label="Initial">
                <option value="under_review">Under Review</option>
              </optgroup>
              <optgroup label="Screening">
                <option value="screened">Screened</option>
                <option value="qualified">Qualified</option>
                <option value="disqualified">Disqualified</option>
              </optgroup>
              <optgroup label="Selection">
                <option value="exam_scheduled">Exam Scheduled</option>
                <option value="shortlisted">Shortlisted</option>
                <option value="for_interview">For Interview</option>
                <option value="interviewed">Interviewed</option>
                <option value="recommended">Recommended</option>
              </optgroup>
              <optgroup label="Final">
                <option value="appointed">Appointed</option>
                <option value="completed">Completed</option>
                <option value="withdrawn">Withdrawn</option>
              </optgroup>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Remarks <span class="text-gray-400 font-normal">(optional — will be applied to all selected)</span>
            </label>
            <textarea v-model="form.remarks" rows="3" :disabled="saving"
              placeholder="Add notes or feedback…"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none resize-none disabled:opacity-60"></textarea>
          </div>

          <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
        </div>

        <div class="flex justify-between gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl">
          <button @click="close" :disabled="saving"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors disabled:opacity-50">
            Cancel
          </button>
          <button @click="submit" :disabled="saving || !form.status"
            class="px-4 py-2 text-sm bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark disabled:opacity-60 transition-colors">
            <span v-if="saving" class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              Updating…
            </span>
            <span v-else>Apply to {{ ids.length }} Applicant{{ ids.length !== 1 ? 's' : '' }}</span>
          </button>
        </div>
  </BaseModal>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import axios from 'axios'
import BaseModal from '@/Components/UI/BaseModal.vue'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  open: { type: Boolean, default: false },
  ids: { type: Array, default: () => [] },
})
const emit = defineEmits(['close', 'saved'])

const toast = useToast()

const saving = ref(false)
const progress = ref(0)
const error = ref('')
const form = reactive({ status: '', remarks: '' })

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    form.status = ''
    form.remarks = ''
    error.value = ''
    progress.value = 0
  }
})

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function close() {
  if (saving.value) return
  emit('close')
}

async function submit() {
  if (!form.status) return
  saving.value = true
  progress.value = 0
  error.value = ''
  let failed = 0

  for (const id of props.ids) {
    try {
      await axios.patch(`/api/applications/${id}/status`, {
        status: form.status,
        remarks: form.remarks || undefined,
      }, { headers: authHeaders() })
    } catch {
      failed++
    }
    progress.value++
  }

  saving.value = false

  if (failed > 0) {
    error.value = `${failed} application${failed !== 1 ? 's' : ''} could not be updated.`
    return
  }

  toast.success(`Status updated to "${form.status.replace(/_/g, ' ')}" for ${props.ids.length} applicant${props.ids.length !== 1 ? 's' : ''}.`)
  emit('saved')
}
</script>
