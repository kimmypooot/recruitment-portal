<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
      <span v-else-if="showOptional" class="text-gray-400 font-normal">(optional)</span>
    </label>

    <label
      class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-xl px-4 py-5 cursor-pointer transition-colors group"
      :class="dropzoneClasses"
      @dragover.prevent="dragging = true"
      @dragleave.prevent="dragging = false"
      @drop.prevent="onDrop"
    >
      <input
        :id="id"
        type="file"
        class="sr-only"
        :accept="accept"
        :disabled="disabled"
        @change="onPick"
      />

      <Icon v-if="!hasFile" name="upload" :size="7" class="text-gray-300 group-hover:text-primary transition-colors" />
      <Icon v-else name="check" :size="7" class="text-green-500" />

      <p class="text-sm font-medium text-center leading-snug" :class="hasFile ? 'text-green-700' : 'text-gray-500 group-hover:text-primary'">
        {{ statusText }}
      </p>
      <p v-if="!hasFile" class="text-xs text-gray-400">{{ hint || acceptHint }}</p>
    </label>

    <div v-if="existingUrl && !file" class="mt-1.5 flex items-center justify-between gap-2">
      <span v-if="existingLabel" class="flex items-center gap-1 text-xs text-gray-400">
        <Icon name="clock" :size="3" />
        {{ existingLabel }}
      </span>
      <span v-else class="flex-1" />
      <a :href="existingUrl" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline flex-shrink-0">
        <Icon name="eye" :size="3.5" />
        View document
      </a>
    </div>

    <p v-if="error" class="mt-1.5 text-xs text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
/**
 * Single-file drag & drop uploader — extracted from the dropzone pattern in
 * Profile/DocumentsTab.vue and CompleteProfile.vue's photo picker, so new
 * upload fields (background investigation reports, CS Form attachments,
 * etc.) don't hand-roll the same markup a fourth time.
 *
 * Client-side accept/size checks are a UX convenience only — the server
 * (mimes/mimetypes/max validation rules) remains the real authority.
 */
import { computed, ref, useId } from 'vue'
import Icon from './Icon.vue'

const props = defineProps({
  modelValue: { type: File, default: null },
  label: { type: String, default: '' },
  accept: { type: String, default: '' }, // e.g. ".pdf" or "image/jpeg,image/png"
  maxSizeMb: { type: Number, default: null },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  showOptional: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  existingUrl: { type: String, default: '' }, // link to a file already on the server
  existingLabel: { type: String, default: '' }, // e.g. "Updated Jan 3, 2026"
  id: { type: String, default: null },
})

const emit = defineEmits(['update:modelValue', 'error'])

const autoId = useId()
const id = computed(() => props.id ?? autoId)
const dragging = ref(false)
const file = computed(() => props.modelValue)
const hasFile = computed(() => !!file.value || !!props.existingUrl)

const acceptHint = computed(() => {
  if (!props.accept) return 'Click to browse or drag & drop'
  const exts = props.accept.split(',').map((a) => a.trim().replace(/^\./, '').toUpperCase()).join(', ')
  return `${exts} format only`
})

const statusText = computed(() => {
  if (file.value) return file.value.name
  if (props.existingUrl) return 'Uploaded — click to replace'
  return 'Click to browse or drag & drop'
})

const dropzoneClasses = computed(() => {
  if (props.disabled) return 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-60'
  if (file.value || props.existingUrl) return 'border-green-400 bg-green-50'
  if (dragging.value) return 'border-primary bg-primary/5'
  return 'border-gray-200 hover:border-primary/40 hover:bg-primary/5'
})

function selectFile(candidate) {
  if (!candidate) return

  if (props.maxSizeMb && candidate.size > props.maxSizeMb * 1024 * 1024) {
    emit('error', `File is too large. Maximum size is ${props.maxSizeMb} MB.`)
    return
  }

  emit('update:modelValue', candidate)
}

function onPick(event) {
  selectFile(event.target.files?.[0] ?? null)
}

function onDrop(event) {
  dragging.value = false
  if (props.disabled) return
  selectFile(event.dataTransfer?.files?.[0] ?? null)
}
</script>
