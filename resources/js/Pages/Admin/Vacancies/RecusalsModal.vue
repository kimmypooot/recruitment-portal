<template>
  <BaseModal :show="!!vacancy" title="Recusals" max-width="max-w-lg" @close="$emit('close')">
    <template v-if="vacancy">
      <h3 class="text-base font-semibold text-gray-900">Recusals</h3>
      <p class="text-xs text-gray-500 mt-0.5 mb-4">
        {{ vacancy.position_title }}. A recused member takes no part in evaluating this vacancy and is
        dropped from its board. Members who apply for it are recused automatically.
      </p>

      <div v-if="quorum && !quorum.met" role="alert"
        class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
        <strong>The board may no longer have a quorum.</strong>
        {{ quorum.members }} principal member{{ quorum.members === 1 ? '' : 's' }} left, against a minimum of {{ quorum.minimum }}
        <span v-if="quorum.recused">({{ quorum.recused }} recused)</span>.
        <span v-if="quorum.alternates">{{ quorum.alternates }} alternate{{ quorum.alternates === 1 ? '' : 's' }} can sit in. </span>
        Assign members or alternates before evaluation continues.
      </div>

      <div v-if="loading" class="text-sm text-gray-400 py-6 text-center">Loading…</div>

      <ul v-else-if="recusals.length" class="divide-y divide-gray-100 border border-gray-100 rounded-lg mb-4">
        <li v-for="r in recusals" :key="r.id" class="flex items-start justify-between gap-3 px-3 py-2.5">
          <div class="min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate">{{ r.name || 'Unknown user' }}</p>
            <p class="text-xs text-gray-500">
              {{ reasonLabel(r.reason) }}<span v-if="r.notes"> · {{ r.notes }}</span>
            </p>
            <p v-if="r.recorded_by" class="text-[11px] text-gray-400">Recorded by {{ r.recorded_by }}</p>
          </div>
          <button v-if="r.reason !== 'applicant'" @click="remove(r)" :disabled="busy"
            class="text-xs font-medium text-red-600 hover:underline disabled:opacity-50 flex-shrink-0">Remove</button>
          <span v-else class="text-[11px] text-gray-400 flex-shrink-0">Automatic</span>
        </li>
      </ul>
      <p v-else class="text-sm text-gray-400 py-4 text-center mb-2">Nobody is recused from this vacancy.</p>

      <form @submit.prevent="add" class="space-y-3 border-t border-gray-100 pt-4">
        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Declare a conflict</p>
        <select v-model="form.user_id" required
          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
          <option value="" disabled>Select a board member</option>
          <option v-for="m in members" :key="m.user_id" :value="m.user_id">{{ m.name }}</option>
        </select>
        <select v-model="form.reason"
          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
          <option value="relative">Related to a candidate</option>
          <option value="other">Other conflict</option>
        </select>
        <textarea v-model="form.notes" rows="2" maxlength="1000" placeholder="Notes (optional)"
          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30"></textarea>
        <div class="flex justify-end gap-3">
          <button type="button" @click="$emit('close')"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Close</button>
          <button type="submit" :disabled="busy || !form.user_id"
            class="px-4 py-2 text-sm bg-primary text-white font-semibold rounded-lg hover:opacity-90 disabled:opacity-60">
            {{ busy ? 'Saving…' : 'Recuse member' }}
          </button>
        </div>
      </form>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import BaseModal from '@/Components/UI/BaseModal.vue'
import { useToast } from '@/composables/useToast'

const props = defineProps({ vacancy: { type: Object, default: null } })
defineEmits(['close'])

const toast = useToast()
const loading = ref(false)
const busy = ref(false)
const recusals = ref([])
const members = ref([])
const quorum = ref(null)
const form = ref({ user_id: '', reason: 'relative', notes: '' })

const REASONS = { applicant: 'Applied for this vacancy', relative: 'Related to a candidate', other: 'Other conflict' }
const reasonLabel = (r) => REASONS[r] ?? r

const headers = () => ({ Authorization: `Bearer ${localStorage.getItem('auth_token')}` })

async function load() {
  loading.value = true
  form.value = { user_id: '', reason: 'relative', notes: '' }
  try {
    const [rec, comp] = await Promise.all([
      axios.get(`/api/admin/vacancies/${props.vacancy.id}/recusals`, { headers: headers() }),
      // The board for this vacancy, which already leaves out anyone recused.
      axios.get('/api/admin/hrmpsb/compositions', { params: { vacancy_id: props.vacancy.id }, headers: headers() }),
    ])
    recusals.value = rec.data.data ?? []
    quorum.value = rec.data.quorum ?? null
    const rows = comp.data.compositions ?? []
    const seen = new Set()
    members.value = rows
      .filter(c => c.user && !seen.has(c.user_id) && seen.add(c.user_id))
      .map(c => ({ user_id: c.user_id, name: c.user.full_name || c.user.email }))
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not load recusals.')
  } finally {
    loading.value = false
  }
}

async function add() {
  busy.value = true
  try {
    await axios.post(`/api/admin/vacancies/${props.vacancy.id}/recusals`, form.value, { headers: headers() })
    toast.success('Member recused from this vacancy.')
    await load()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not record the recusal.')
  } finally {
    busy.value = false
  }
}

async function remove(r) {
  busy.value = true
  try {
    await axios.delete(`/api/admin/vacancies/${props.vacancy.id}/recusals/${r.id}`, { headers: headers() })
    toast.success('Recusal removed.')
    await load()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Could not remove the recusal.')
  } finally {
    busy.value = false
  }
}

watch(() => props.vacancy, (v) => { if (v) load() })
</script>
