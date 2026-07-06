<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="onBackdrop">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="onBackdrop"></div>
        <div ref="panel" role="dialog" aria-modal="true" :aria-label="title || undefined"
          class="relative bg-white rounded-2xl shadow-xl w-full p-6" :class="maxWidth">
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, ref, watch, onBeforeUnmount } from 'vue'
import { useFocusTrap } from '@/composables/useFocusTrap'

const props = defineProps({
  show:            { type: Boolean, required: true },
  title:           { type: String, default: '' },
  maxWidth:        { type: String, default: 'max-w-md' },
  closeOnBackdrop: { type: Boolean, default: true },
})

const emit = defineEmits(['close'])

const panel = ref(null)

// useFocusTrap closes by writing false into the ref it's given; proxy that
// write into a close event so the parent stays the source of truth.
const showProxy = computed({
  get: () => props.show,
  set: (value) => { if (!value) emit('close') },
})

useFocusTrap(showProxy, panel)

function onBackdrop() {
  if (props.closeOnBackdrop) emit('close')
}

// Lock body scroll while any modal is open (supports stacked modals).
let locked = false

function setScrollLock(open) {
  if (open && !locked) {
    locked = true
    openCount++
    document.body.style.overflow = 'hidden'
  } else if (!open && locked) {
    locked = false
    openCount = Math.max(0, openCount - 1)
    if (openCount === 0) document.body.style.overflow = ''
  }
}

watch(() => props.show, setScrollLock, { immediate: true })
onBeforeUnmount(() => setScrollLock(false))
</script>

<script>
let openCount = 0
</script>

<style scoped>
.modal-enter-active { transition: opacity 0.2s ease-out; }
.modal-leave-active { transition: opacity 0.15s ease-in; }
.modal-enter-from,
.modal-leave-to { opacity: 0; }
</style>
