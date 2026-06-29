<template>
  <div class="animate-pulse" :class="wrapperClass">
    <div
      v-if="variant === 'text'"
      class="space-y-2"
    >
      <div
        v-for="i in lines"
        :key="i"
        class="h-3 bg-gray-200 rounded"
        :style="{ width: widths[i - 1] ?? '100%' }"
      ></div>
    </div>

    <div
      v-else-if="variant === 'card'"
      class="bg-white rounded-xl border border-gray-200 p-5"
    >
      <div class="flex gap-2 mb-3">
        <div class="h-5 w-12 bg-gray-200 rounded-md"></div>
        <div class="h-5 w-16 bg-gray-200 rounded-full"></div>
      </div>
      <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
      <div class="h-4 bg-gray-200 rounded mb-4 w-1/2"></div>
      <div class="space-y-2">
        <div class="h-3 bg-gray-100 rounded w-full"></div>
        <div class="h-3 bg-gray-100 rounded w-5/6"></div>
        <div class="h-3 bg-gray-100 rounded w-2/3"></div>
      </div>
      <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
        <div class="h-3 w-24 bg-gray-200 rounded"></div>
        <div class="h-8 w-24 bg-gray-200 rounded-lg"></div>
      </div>
    </div>

    <div
      v-else-if="variant === 'table-row'"
      class="flex items-center gap-4 h-12"
    >
      <div class="h-3 bg-gray-100 rounded w-6 flex-shrink-0"></div>
      <div class="flex-1 h-3 bg-gray-100 rounded"></div>
      <div class="h-3 bg-gray-100 rounded w-20"></div>
      <div class="h-3 bg-gray-100 rounded w-16 hidden md:block"></div>
      <div class="h-3 bg-gray-100 rounded w-24 hidden lg:block"></div>
      <div class="h-7 bg-gray-100 rounded-lg w-28"></div>
    </div>

    <div
      v-else-if="variant === 'stat-card'"
      class="bg-white rounded-xl border border-gray-200 p-5 flex items-start gap-4 shadow-sm"
    >
      <div class="w-11 h-11 rounded-xl bg-gray-200 flex-shrink-0"></div>
      <div class="flex-1 space-y-2">
        <div class="h-3 bg-gray-200 rounded w-16"></div>
        <div class="h-7 bg-gray-200 rounded w-10"></div>
      </div>
    </div>

    <div
      v-else-if="variant === 'application-row'"
      class="bg-white rounded-xl border border-gray-200 p-5"
    >
      <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-gray-100 flex-shrink-0"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          <div class="h-3 bg-gray-100 rounded w-1/3"></div>
          <div class="h-3 bg-gray-100 rounded w-1/4 mt-2"></div>
        </div>
        <div class="h-5 w-20 bg-gray-100 rounded-full flex-shrink-0"></div>
      </div>
    </div>

    <div
      v-else-if="variant === 'custom'"
      class="space-y-3"
    >
      <div
        v-for="i in count"
        :key="i"
        class="h-10 bg-gray-100 rounded animate-pulse"
      ></div>
    </div>

    <div
      v-else
      class="space-y-3"
    >
      <div
        v-for="i in count"
        :key="i"
        class="h-14 bg-gray-100 rounded-xl animate-pulse"
      ></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  variant: { type: String, default: 'default' },
  count: { type: Number, default: 5 },
  lines: { type: Number, default: 3 },
  widths: { type: Array, default: () => [] },
  wrapperClass: { type: String, default: '' },
})
</script>
