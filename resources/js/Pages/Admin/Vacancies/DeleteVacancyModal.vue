<template>
  <Teleport to="body">
    <div v-if="vacancy" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-2">Delete Vacancy?</h3>
        <p class="text-sm text-gray-500 mb-6">
          "<strong>{{ vacancy.position_title }}</strong>" will be removed from all listings. This cannot be undone from this screen.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="$emit('close')"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="doDelete" :disabled="deleting"
            class="px-4 py-2 text-sm bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-60">
            {{ deleting ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  vacancy: { type: Object, default: null },
})
const emit = defineEmits(['close', 'deleted'])

const toast = useToast()
const deleting = ref(false)

function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function doDelete() {
  deleting.value = true
  try {
    await axios.delete(`/api/vacancies/${props.vacancy.id}`, { headers: authHeaders() })
    toast.success('Vacancy deleted.')
    emit('deleted')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to delete vacancy.')
  } finally {
    deleting.value = false
  }
}
</script>
