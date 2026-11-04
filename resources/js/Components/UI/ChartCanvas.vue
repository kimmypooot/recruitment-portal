<template>
  <div class="relative" :style="{ height: height + 'px' }">
    <canvas ref="canvas" role="img" :aria-label="ariaLabel"></canvas>
  </div>
</template>

<script setup>
// Thin Chart.js wrapper. Only the pieces we use are registered to keep the bundle small.
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useTheme } from '@/composables/useTheme'
import {
  Chart, DoughnutController, BarController, LineController,
  ArcElement, BarElement, LineElement, PointElement,
  CategoryScale, LinearScale, Tooltip, Legend, Filler,
} from 'chart.js'

Chart.register(
  DoughnutController, BarController, LineController,
  ArcElement, BarElement, LineElement, PointElement,
  CategoryScale, LinearScale, Tooltip, Legend, Filler,
)
Chart.defaults.font.family = "'Poppins', ui-sans-serif, system-ui, sans-serif"
Chart.defaults.color = '#6b7280'

const props = defineProps({
  type: { type: String, required: true },       // 'doughnut' | 'bar' | 'line'
  data: { type: Object, required: true },
  options: { type: Object, default: () => ({}) },
  height: { type: Number, default: 240 },
  ariaLabel: { type: String, default: 'Chart' },
})

const { isDark } = useTheme()
const canvas = ref(null)
let chart = null

function build() {
  Chart.defaults.color = isDark.value ? '#a9b2c8' : '#6b7280'
  Chart.defaults.borderColor = isDark.value ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.1)'
  Chart.defaults.plugins.tooltip.backgroundColor = isDark.value ? '#0a0e1d' : 'rgba(0,0,0,0.8)'
  chart?.destroy()
  if (!canvas.value) return
  chart = new Chart(canvas.value, {
    type: props.type,
    data: props.data,
    options: { responsive: true, maintainAspectRatio: false, ...props.options },
  })
}

onMounted(build)
watch(() => [props.data, props.options, props.type, isDark.value], build, { deep: true })
onBeforeUnmount(() => chart?.destroy())
</script>
