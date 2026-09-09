<template>
  <BaseInput
    type="date"
    :model-value="modelValue"
    :label="label"
    :hint="hint"
    :error="error"
    :required="required"
    :show-optional="showOptional"
    :disabled="disabled"
    :id="id"
    :min="min"
    :max="max"
    @update:model-value="$emit('update:modelValue', $event)"
    @blur="$emit('blur', $event)"
  >
    <template #icon><Icon name="calendar" :size="4" /></template>
  </BaseInput>
</template>

<script setup>
/**
 * Native <input type="date"> keeps the OS/browser's own accessible date UI
 * (and matches every date field already in CompleteProfile.vue) — this just
 * gives it BaseInput's label/error/hint chrome and a calendar glyph so it
 * reads as a date field at a glance, rather than reimplementing a calendar
 * widget the platform already provides.
 */
import BaseInput from './BaseInput.vue'
import Icon from './Icon.vue'

defineProps({
  modelValue: { type: String, default: '' }, // 'YYYY-MM-DD'
  label: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  showOptional: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  id: { type: String, default: null },
  min: { type: String, default: undefined },
  max: { type: String, default: undefined },
})

defineEmits(['update:modelValue', 'blur'])
</script>
