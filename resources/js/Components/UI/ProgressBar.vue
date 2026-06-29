<template>
  <div
    v-if="show"
    class="fixed top-0 left-0 right-0 z-[99999] h-1"
    role="progressbar"
    aria-valuenow="50"
    aria-valuemin="0"
    aria-valuemax="100"
  >
    <div
      class="h-full transition-all duration-[400ms] ease-out"
      :class="colorClass"
      :style="{ width: width + '%' }"
    ></div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const show = ref(false)
const width = ref(0)
const colorClass = ref('bg-primary')

router.on('start', () => {
  show.value = true
  width.value = 20
  setTimeout(() => { width.value = 60 }, 100)
})

router.on('progress', (event) => {
  if (event.detail.progress?.percentage) {
    width.value = Math.min(event.detail.progress.percentage, 90)
  }
})

router.on('finish', () => {
  width.value = 100
  setTimeout(() => {
    show.value = false
    width.value = 0
  }, 400)
})

router.on('invalid', () => {
  colorClass.value = 'bg-accent'
  width.value = 100
  setTimeout(() => {
    show.value = false
    width.value = 0
    colorClass.value = 'bg-primary'
  }, 500)
})
</script>
