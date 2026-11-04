<template>
  <span ref="el">{{ display }}</span>
</template>

<script setup>
// Counts up to `value` (and re-counts when it changes). Respects reduced-motion.
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  value: { type: Number, default: 0 },
  duration: { type: Number, default: 900 },
})

const display = ref(0)
let frame = null

function animate(from, to) {
  cancelAnimationFrame(frame)
  const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
  if (reduce || from === to || props.duration <= 0) {
    display.value = to
    return
  }
  const start = performance.now()
  const step = (now) => {
    const t = Math.min((now - start) / props.duration, 1)
    const eased = 1 - Math.pow(1 - t, 3)
    display.value = Math.round(from + (to - from) * eased)
    if (t < 1) frame = requestAnimationFrame(step)
  }
  frame = requestAnimationFrame(step)
}

const el = ref(null)
let observer = null
let started = false

onMounted(() => {
  // Wait until the number is on screen (e.g. a stats band far down the page).
  if (!('IntersectionObserver' in window)) { started = true; animate(0, props.value); return }
  observer = new IntersectionObserver((entries) => {
    if (entries.some(e => e.isIntersecting)) {
      started = true
      observer.disconnect()
      animate(0, props.value)
    }
  }, { threshold: 0.2 })
  observer.observe(el.value)
})
watch(() => props.value, (to, from) => { if (started) animate(from ?? 0, to) })
onBeforeUnmount(() => { cancelAnimationFrame(frame); observer?.disconnect() })
</script>
