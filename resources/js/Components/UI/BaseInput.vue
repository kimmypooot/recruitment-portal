<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
      <span v-else-if="showOptional" class="text-gray-400 font-normal">(optional)</span>
    </label>

    <div class="relative">
      <span v-if="$slots.icon" class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
        <slot name="icon" />
      </span>

      <input
        :id="id"
        ref="inputEl"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :aria-invalid="!!error"
        :aria-describedby="describedBy"
        v-bind="$attrs"
        class="w-full py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:outline-none transition disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
        :class="[
          $slots.icon ? 'pl-10 pr-4' : 'px-4',
          $slots.suffix ? 'pr-10' : '',
          error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-primary focus:border-primary',
        ]"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
      />

      <span v-if="$slots.suffix" class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
        <slot name="suffix" />
      </span>
    </div>

    <p v-if="error" :id="`${id}-error`" class="mt-1.5 text-xs text-red-600">{{ error }}</p>
    <p v-else-if="hint" :id="`${id}-hint`" class="mt-1.5 text-xs text-gray-400">{{ hint }}</p>
  </div>
</template>

<script setup>
/**
 * Shared text-input primitive — extracted from the identical
 * `px-4 py-2.5 rounded-lg border ... focus:ring-2 focus:ring-primary`
 * markup repeated across Login.vue, Register.vue, CompleteProfile.vue, etc.
 */
import { computed, ref, useId } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  showOptional: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  id: { type: String, default: null },
})

defineEmits(['update:modelValue', 'blur'])
defineOptions({ inheritAttrs: false })

const inputEl = ref(null)
const autoId = useId()
const id = computed(() => props.id ?? autoId)

const describedBy = computed(() => {
  if (props.error) return `${id.value}-error`
  if (props.hint) return `${id.value}-hint`
  return undefined
})

defineExpose({ focus: () => inputEl.value?.focus() })
</script>
