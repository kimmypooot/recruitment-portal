<template>
  <div class="mt-2 space-y-1" aria-live="polite">
    <p v-for="req in requirements" :key="req.label"
      class="flex items-center gap-1.5 text-xs transition-colors"
      :class="req.met ? 'text-green-600' : 'text-gray-400'">
      <Icon v-if="req.met" name="check" size="3.5" class="flex-shrink-0" />
      <svg v-else class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="9"/>
      </svg>
      {{ req.label }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Icon from '@/Components/UI/Icon.vue'
import { passwordRequirements } from '@/utils/password'

const props = defineProps({
  password: { type: String, default: '' },
})

const requirements = computed(() => passwordRequirements(props.password))
</script>
