<template>
  <span :class="badgeClass" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" role="status">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { statusLabel, statusBadgeClass } from '@/config/statusConfig'

const props = defineProps({
  status: { type: String, default: 'draft' }
})

const label = computed(() => statusLabel(props.status))
const badgeClass = computed(() => statusBadgeClass(props.status))
</script>
