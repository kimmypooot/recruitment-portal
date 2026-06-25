<template>
  <Teleport to="body">
    <div v-if="state.show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" @keydown.escape="state.alert ? onConfirm() : onCancel()">
      <div class="absolute inset-0 bg-black/50" @click="state.alert ? onConfirm() : onCancel()"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
        <div :class="iconBgClass"
          class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6" :class="iconColorClass" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath"/>
          </svg>
        </div>
        <h3 class="text-base font-semibold text-gray-900 mb-1">{{ state.title }}</h3>
        <p class="text-sm text-gray-500 mb-6">{{ state.message }}</p>
        <div class="flex gap-3">
          <button v-if="!state.alert" @click="onCancel"
            class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
            {{ state.cancelText }}
          </button>
          <button @click="onConfirm" :disabled="state.loading"
            class="flex-1 py-2 text-sm text-white rounded-lg transition-colors font-semibold disabled:opacity-60"
            :class="confirmClass">
            <span v-if="state.loading" class="flex items-center justify-center gap-1.5">
              <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ state.confirmText }}
            </span>
            <span v-else>{{ state.confirmText }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useConfirm } from '@/composables/useConfirm'

const { state, onConfirm, onCancel } = useConfirm()

const iconMap = {
  warning: { path: 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z', bg: 'bg-red-50', color: 'text-red-500' },
  danger: { path: 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z', bg: 'bg-red-50', color: 'text-red-500' },
  info: { path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', bg: 'bg-blue-50', color: 'text-blue-500' },
  logout: { path: 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1', bg: 'bg-red-50', color: 'text-red-500' },
  success: { path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', bg: 'bg-green-50', color: 'text-green-600' },
  delete: { path: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', bg: 'bg-red-50', color: 'text-red-500' },
}

const iconPath = computed(() => iconMap[state.iconType]?.path ?? iconMap.warning.path)
const iconBgClass = computed(() => iconMap[state.iconType]?.bg ?? 'bg-red-50')
const iconColorClass = computed(() => iconMap[state.iconType]?.color ?? 'text-red-500')

const confirmClass = computed(() => {
  if (state.variant === 'danger') return 'bg-red-600 hover:bg-red-700'
  if (state.variant === 'primary') return 'bg-[#2a338f] hover:bg-[#1e2570]'
  return 'bg-gray-600 hover:bg-gray-700'
})
</script>
