<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
      <span v-else-if="showOptional" class="text-gray-400 font-normal">(optional)</span>
    </label>

    <div class="relative">
      <select
        :id="id"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        :aria-invalid="!!error"
        :aria-describedby="describedBy"
        v-bind="$attrs"
        class="w-full px-4 py-2.5 rounded-lg border text-sm text-gray-900 bg-white focus:ring-2 focus:outline-none transition appearance-none pr-10 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
        :class="error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-primary focus:border-primary'"
        @change="$emit('update:modelValue', $event.target.value)"
      >
        <option v-if="placeholder" value="" disabled :selected="!modelValue">{{ placeholder }}</option>
        <slot>
          <option v-for="opt in normalizedOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </slot>
      </select>

      <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
        <Icon name="chevronDown" :size="4" />
      </span>
    </div>

    <p v-if="error" :id="`${id}-error`" class="mt-1.5 text-xs text-red-600">{{ error }}</p>
    <p v-else-if="hint" :id="`${id}-hint`" class="mt-1.5 text-xs text-gray-400">{{ hint }}</p>
  </div>
</template>

<script setup>
/**
 * Shared select primitive. Pass `options` (array of strings, or
 * {value, label} objects) for the common case, or use the default slot to
 * hand-write <option> tags (grouped options, PSGC-style cascades, etc.).
 */
import { computed, useId } from 'vue'
import Icon from './Icon.vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  showOptional: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  id: { type: String, default: null },
})

defineEmits(['update:modelValue'])
defineOptions({ inheritAttrs: false })

const autoId = useId()
const id = computed(() => props.id ?? autoId)

const normalizedOptions = computed(() =>
  props.options.map((opt) => (typeof opt === 'object' && opt !== null ? opt : { value: opt, label: String(opt) }))
)

const describedBy = computed(() => {
  if (props.error) return `${id.value}-error`
  if (props.hint) return `${id.value}-hint`
  return undefined
})
</script>
