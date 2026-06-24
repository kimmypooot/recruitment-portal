<template>
  <Teleport to="body">
    <Transition enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center"
        style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px);">
        <div class="bg-white rounded-2xl shadow-2xl p-8 sm:p-10 text-center max-w-sm mx-4">
          <!-- Spinner -->
          <div class="relative w-16 h-16 mx-auto mb-6">
            <div class="absolute inset-0 border-4 border-gray-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-transparent border-t-[#2a338f] rounded-full animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
              <svg v-if="target === 'hrmpsb'" class="w-7 h-7 text-[#1a5276]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <svg v-else class="w-7 h-7 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
          </div>

          <!-- Message -->
          <p class="text-base font-semibold text-gray-900 mb-2">{{ message }}</p>
          <p class="text-sm text-gray-500 mb-2">
            {{ target === 'hrmpsb' ? 'HRMPSB Workspace' : 'Administrator Workspace' }}
          </p>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium"
            :class="target === 'hrmpsb' ? 'bg-[#1a5276]/10 text-[#1a5276]' : 'bg-[#2a338f]/10 text-[#2a338f]'">
            <span class="w-1.5 h-1.5 rounded-full" :class="target === 'hrmpsb' ? 'bg-[#1a5276]' : 'bg-[#2a338f]'"></span>
            {{ target === 'hrmpsb' ? 'HRMPSB Module' : 'Admin Module' }}
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  target: { type: String, default: 'hrmpsb' },
})

const message = ref('')

watch(() => props.show, (val) => {
  if (!val) return
  message.value = props.target === 'hrmpsb'
    ? 'Switching to HRMPSB Workspace...'
    : 'Switching to Administrator Workspace...'

  setTimeout(() => {
    const path = props.target === 'hrmpsb' ? '/hrmpsb/dashboard' : '/admin/dashboard'
    router.visit(path)
  }, 1200)
})
</script>
