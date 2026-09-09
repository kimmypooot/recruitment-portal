<template>
  <BaseModal :show="!!app" title="Update Application Status" max-width="max-w-md"
    panel-class="max-h-[90vh] flex flex-col" @close="$emit('close')">
    <template v-if="app">
        <div class="px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <h3 class="text-base font-semibold text-gray-900">Update Application Status</h3>
          <p class="text-xs text-gray-400 mt-1">
            {{ applicantName }}
            <span class="mx-1 text-gray-300">·</span>
            {{ app.vacancy?.position_title ?? 'Position' }}
          </p>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
          <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
            <span class="text-xs text-gray-500 font-medium">Current:</span>
            <StatusBadge :status="app.status" />
          </div>

          <BaseSelect v-model="form.status" label="New Status" required>
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
          </BaseSelect>

          <!-- Schedule form: shown when exam or interview status is selected -->
          <div v-if="form.status === 'exam_scheduled' || form.status === 'for_interview'"
            class="rounded-xl border p-4 space-y-3"
            :class="form.status === 'exam_scheduled' ? 'bg-orange-50 border-orange-200' : 'bg-violet-50 border-violet-200'">
            <p class="text-xs font-semibold uppercase tracking-wide"
              :class="form.status === 'exam_scheduled' ? 'text-orange-800' : 'text-violet-800'">
              {{ form.status === 'exam_scheduled' ? 'Exam Schedule' : 'Interview Schedule' }}
              <span class="font-normal text-gray-400 ml-1 normal-case">(optional — create now or schedule later)</span>
            </p>
            <BaseInput type="datetime-local" v-model="schedule.scheduled_at" label="Date & Time" />
            <BaseInput type="text" v-model="schedule.venue" label="Venue" placeholder="e.g. CSC Regional Office, Room 201" />
            <BaseTextarea v-model="schedule.notes" :rows="2" label="Notes" show-optional
              placeholder="Additional instructions or reminders…" />
          </div>

          <BaseTextarea v-model="form.remarks" :rows="3" label="Remarks" show-optional
            hint="Visible to the applicant"
            placeholder="Add notes or feedback for the applicant…" />
        </div>

        <div class="flex justify-between gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl flex-shrink-0">
          <BaseButton variant="secondary" size="sm" @click="$emit('close')">Cancel</BaseButton>
          <BaseButton size="sm" :loading="saving" @click="submit">
            {{ saving ? 'Saving…' : 'Save Status' }}
          </BaseButton>
        </div>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import axios from 'axios'
import BaseModal from '@/Components/UI/BaseModal.vue'
import BaseSelect from '@/Components/UI/BaseSelect.vue'
import BaseInput from '@/Components/UI/BaseInput.vue'
import BaseTextarea from '@/Components/UI/BaseTextarea.vue'
import BaseButton from '@/Components/UI/BaseButton.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  app: { type: Object, default: null },
})
const emit = defineEmits(['close', 'saved'])

const toast = useToast()

const saving = ref(false)
const form = reactive({ status: '', remarks: '' })
const schedule = reactive({ scheduled_at: '', venue: '', notes: '' })

const applicantName = computed(() => {
  const p = props.app?.applicant
  if (p?.last_name && p?.first_name) {
    const middle = p.middle_name ? ' ' + p.middle_name.charAt(0).toUpperCase() + '.' : ''
    return `${p.last_name}, ${p.first_name}${middle}`
  }
  return p?.user?.full_name ?? '—'
})

watch(() => props.app, (app) => {
  if (!app) return
  form.status = app.status
  form.remarks = app.remarks ?? ''
  schedule.scheduled_at = ''
  schedule.venue = ''
  schedule.notes = ''
})

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function submit() {
  saving.value = true
  try {
    await axios.patch(`/api/applications/${props.app.id}/status`, form, { headers: authHeaders() })

    if (schedule.scheduled_at && schedule.venue &&
        (form.status === 'exam_scheduled' || form.status === 'for_interview')) {
      const endpoint = form.status === 'exam_scheduled' ? '/api/examinations' : '/api/interviews'
      try {
        await axios.post(endpoint, {
          application_id: props.app.id,
          scheduled_at: schedule.scheduled_at,
          venue: schedule.venue,
          notes: schedule.notes || undefined,
        }, { headers: authHeaders() })
      } catch (e) {
        toast.error('Status updated but failed to create schedule: ' + (e.response?.data?.message ?? 'unknown error'))
        emit('saved')
        return
      }
    }

    toast.success(`Status updated to "${form.status.replace(/_/g, ' ')}" for ${applicantName.value}.`)
    emit('saved')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to update status.')
  } finally {
    saving.value = false
  }
}
</script>
