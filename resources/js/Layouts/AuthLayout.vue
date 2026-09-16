<script setup>
import { useId } from 'vue'
import { Link } from '@inertiajs/vue3'

/**
 * The two-column auth shell shared by Login and Register.
 *
 * The branding panel is identical across those pages — only the headline,
 * tagline, and benefit bullets differ — so the copy comes in as props and the
 * form rides the default slot. `useId` keeps the pattern's SVG def scoped
 * per-instance so two auth screens can never collide on the same id.
 */
defineProps({
  headline: { type: String, required: true },
  tagline: { type: String, required: true },
  benefits: { type: Array, default: () => [] },
})

const patternId = useId()
</script>

<template>
  <div class="min-h-dvh lg:grid lg:grid-cols-2">

    <!-- Left: branding. Hidden below lg. -->
    <aside class="relative hidden overflow-hidden lg:flex lg:min-h-dvh lg:flex-col lg:justify-center text-white"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <div class="absolute inset-0" style="background: linear-gradient(160deg, rgba(26,31,94,0.93) 0%, rgba(42,51,143,0.87) 55%, rgba(30,37,112,0.95) 100%);"></div>

      <svg class="pointer-events-none absolute inset-0 size-full opacity-[0.08]" aria-hidden="true">
        <defs>
          <pattern :id="patternId" width="64" height="64" patternUnits="userSpaceOnUse">
            <circle cx="32" cy="32" r="18" fill="none" stroke="white" stroke-width="1" />
            <path d="M0 32h64M32 0v64" stroke="white" stroke-width="0.5" />
          </pattern>
        </defs>
        <rect width="100%" height="100%" :fill="`url(#${patternId})`" />
      </svg>

      <div class="relative px-12 py-16 xl:px-20">
        <Link href="/" class="inline-flex items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
          <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white p-2 shadow-sm ring-1 ring-white/20">
            <img src="/images/csc-logo.png" alt="" class="h-full w-full object-contain" />
          </span>
          <span class="flex flex-col leading-none">
            <span class="text-xl font-semibold tracking-tight">CSC <span class="font-normal">RO VIII</span></span>
            <span class="mt-1 text-xs font-medium tracking-wide uppercase text-white/70">Recruitment Portal</span>
          </span>
        </Link>

        <div class="mt-10 max-w-lg">
          <h1 class="text-4xl leading-tight font-bold tracking-tight text-balance xl:text-5xl">
            {{ headline }}
          </h1>

          <p class="mt-6 text-base leading-relaxed text-white/80 xl:text-lg">
            {{ tagline }}
          </p>

          <ul v-if="benefits.length" class="mt-8 space-y-3 text-sm text-white/80">
            <li v-for="benefit in benefits" :key="benefit" class="flex items-start gap-3">
              <svg class="mt-0.5 size-5 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M5 12.5l4.5 4.5L19 7.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              {{ benefit }}
            </li>
          </ul>
        </div>
      </div>
    </aside>

    <!-- Mobile brand strip. Replaces the left panel below lg. -->
    <div class="relative overflow-hidden px-4 py-7 sm:px-6 sm:py-8 lg:hidden text-white"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(30,37,112,0.88) 0%, rgba(42,51,143,0.85) 50%, rgba(26,31,94,0.90) 100%);"></div>

      <div class="relative">
        <Link href="/" class="inline-flex items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
          <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-white/20">
            <img src="/images/csc-logo.png" alt="" class="h-full w-full object-contain" />
          </span>
          <span class="flex flex-col leading-none">
            <span class="text-base font-semibold tracking-tight">CSC <span class="font-normal">RO VIII</span></span>
            <span class="mt-1 text-[10px] font-medium tracking-wide uppercase text-white/70">Recruitment Portal</span>
          </span>
        </Link>

        <p class="mt-4 max-w-prose text-sm leading-relaxed text-white/85">
          {{ tagline }}
        </p>
      </div>
    </div>

    <!-- Right: form -->
    <main class="flex items-center justify-center bg-white px-4 py-10 sm:px-6 sm:py-12 lg:min-h-dvh lg:px-12 lg:py-16">
      <div class="w-full max-w-md">
        <slot />
      </div>
    </main>

  </div>
</template>
