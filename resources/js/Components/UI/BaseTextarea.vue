<template>
  <div>
    <div class="flex items-baseline justify-between mb-1.5">
      <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700">
        {{ label }}
        <span v-if="required" class="text-red-500">*</span>
        <span v-else-if="showOptional" class="text-gray-400 font-normal">(optional)</span>
      </label>
      <span v-if="maxlength" class="text-xs text-gray-400 tabular-nums">{{ (modelValue ?? '').length }}/{{ maxlength }}</span>
    </div>

    <textarea
      :id="id"
      :value="modelValue"
      :rows="rows"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :maxlength="maxlength"
      :aria-invalid="!!error"
      :aria-describedby="describedBy"
      v-bind="$attrs"
      class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:outline-none transition resize-y disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
      :class="error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-primary focus:border-primary'"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur', $event)"
    />

    <p v-if="error" :id="`${id}-error`" class="mt-1.5 text-xs text-red-600">{{ error }}</p>
    <p v-else-if="hint" :id="`${id}-hint`" class="mt-1.5 text-xs text-gray-400">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed, useId } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: '' },
  rows: { type: [String, Number], default: 4 },
  placeholder: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  showOptional: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  maxlength: { type: [String, Number], default: null },
  id: { type: String, default: null },
})

defineEmits(['update:modelValue', 'blur'])
defineOptions({ inheritAttrs: false })

const autoId = useId()
const id = computed(() => props.id ?? autoId)

const describedBy = computed(() => {
  if (props.error) return `${id.value}-error`
  if (props.hint) return `${id.value}-hint`
  return undefined
})
</script>
