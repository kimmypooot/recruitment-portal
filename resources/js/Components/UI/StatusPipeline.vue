<template>
  <div>
    <div class="flex items-start gap-0">
      <div v-for="(step, idx) in pipeline" :key="step.key" class="flex-1 flex flex-col items-center min-w-0">
        <div class="flex items-center w-full">
          <div class="flex-1 h-0.5 transition-colors"
            :class="idx === 0 ? 'invisible' :
              isPipelinePast(step.key, status) || status === step.key ? 'bg-primary' : 'bg-gray-200'">
          </div>
          <div :class="[
            'rounded-full transition-all flex-shrink-0 w-2.5 h-2.5',
            status === step.key
              ? 'bg-primary ring-2 ring-primary/30 ring-offset-1'
              : isPipelinePast(step.key, status)
                ? 'bg-primary'
                : isTerminal(status)
                  ? 'bg-gray-100'
                  : 'bg-gray-200'
          ]"></div>
          <div class="flex-1 h-0.5 transition-colors"
            :class="idx === pipeline.length - 1 ? 'invisible' :
              isPipelinePast(pipeline[idx + 1].key, status) || status === pipeline[idx + 1].key ? 'bg-primary' : 'bg-gray-200'">
          </div>
        </div>
        <span class="mt-1 text-[8px] leading-tight text-center w-full hidden sm:block"
          :class="status === step.key ? 'text-primary font-semibold' : 'text-gray-400'">
          {{ step.short }}
        </span>
      </div>
    </div>

    <p v-if="showSummary" class="mt-2 text-xs text-gray-400">
      <span v-if="isTerminal(status)">
        <span v-if="status === 'withdrawn'" class="text-gray-500">Application withdrawn</span>
        <span v-else-if="status === 'appointed'" class="text-green-600 font-semibold">Congratulations — appointed!</span>
        <span v-else-if="status === 'completed'" class="text-green-600 font-medium">Process completed</span>
        <span v-else-if="status === 'disqualified'" class="text-red-500">Disqualified from selection</span>
        <span v-else-if="status === 'failed'" class="text-red-500">Not passed</span>
      </span>
      <span v-else>
        Stage <strong class="text-gray-600">{{ pipelineStep(status) }}</strong> of {{ pipeline.length }}
        <span class="mx-1 text-gray-200">·</span>
        <span class="text-gray-500">{{ statusLabel(status) }}</span>
      </span>
    </p>
  </div>
</template>

<script setup>
import {
  PIPELINE as pipeline,
  isPipelinePast, isTerminal, pipelineStep, statusLabel,
} from '@/config/statusConfig'

defineProps({
  status: { type: String, required: true },
  showSummary: { type: Boolean, default: true },
})
</script>
