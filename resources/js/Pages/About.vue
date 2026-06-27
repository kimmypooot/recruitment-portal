<template>
  <PublicLayout>

    <Head>
      <title>About</title>
      <meta name="description" content="About the Civil Service Commission Regional Office VIII — our mission, vision, and core values in serving the Filipino people through a world-class civil service." />
    </Head>

    <!-- Hero -->
    <section class="relative text-white overflow-hidden min-h-[280px]"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <!-- Brand gradient overlay -->
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(30,37,112,0.88) 0%, rgba(42,51,143,0.85) 50%, rgba(26,31,94,0.90) 100%);"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="max-w-2xl">
          <p class="text-white/60 text-sm font-medium uppercase tracking-wider mb-3">Who We Are</p>
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">About CSC RO VIII</h1>
          <p class="text-white/80 text-base leading-relaxed">
            The Civil Service Commission is the central personnel agency of the Philippine government,
            mandated to establish a career service and promote morale, efficiency, integrity, responsiveness,
            and courtesy in the civil service.
          </p>
        </div>
      </div>
      <div class="relative h-10 overflow-hidden z-10 pointer-events-none">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB" aria-hidden="true">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <!-- Mandate / Core Purpose / Vision -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 space-y-6">

      <!-- Mandate — full width -->
      <div class="p-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-start gap-5">
          <div class="w-10 h-10 rounded-xl bg-[#2a338f]/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="mandate.icon"/>
            </svg>
          </div>
          <div>
            <p class="text-xs font-semibold text-[#2a338f] uppercase tracking-wider mb-1">{{ mandate.label }}</p>
            <h3 class="text-base font-bold text-gray-900 mb-2">{{ mandate.title }}</h3>
            <p class="text-sm text-gray-600 leading-relaxed">{{ mandate.text }}</p>
          </div>
        </div>
      </div>

      <!-- Core Purpose + Vision — two columns -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="card in corePurposeVision" :key="card.label"
          class="p-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
          <div class="w-10 h-10 rounded-xl bg-[#2a338f]/10 flex items-center justify-center mb-4">
            <svg class="w-5 h-5 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon"/>
            </svg>
          </div>
          <p class="text-xs font-semibold text-[#2a338f] uppercase tracking-wider mb-1">{{ card.label }}</p>
          <h3 class="text-base font-bold text-gray-900 mb-2">{{ card.title }}</h3>
          <p class="text-sm text-gray-600 leading-relaxed">{{ card.text }}</p>
        </div>
      </div>

    </section>

    <!-- About the portal -->
    <section class="bg-white border-y border-gray-200">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <h2 class="text-2xl font-bold text-gray-900 mb-3">About This Portal</h2>
        <p class="text-gray-600 text-sm leading-relaxed mb-4">
          The CSC RO VIII - Recruitment Portal is the official online platform for posting career opportunities
          and accepting applications for vacant positions in the regional office. It streamlines the recruitment
          and selection process in accordance with the CSC's merit and fitness principles.
        </p>
        <p class="text-gray-600 text-sm leading-relaxed">
          All appointments in the civil service shall be made according to merit and fitness to be determined,
          as far as practicable, by competitive examinations. This portal supports a transparent, efficient,
          and fair selection process for all applicants.
        </p>
      </div>
    </section>

    <!-- Core values -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
      <h2 class="text-2xl font-bold text-gray-900 mb-2 text-center">Core Values</h2>
      <p class="text-gray-500 text-sm text-center mb-10">Principles that guide the Civil Service Commission</p>
      <div class="flex flex-wrap justify-center gap-5">
        <div v-for="value in values" :key="value.label" class="text-center p-5 rounded-2xl bg-[#2a338f]/5 border border-[#2a338f]/15 w-36">
          <div class="w-8 h-8 mx-auto mb-2 flex items-center justify-center">
            <svg class="w-7 h-7 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" :d="value.svg"/>
            </svg>
          </div>
          <p class="text-xs font-semibold text-[#2a338f]">{{ value.label }}</p>
        </div>
      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const mandate = {
  label: 'Mandate',
  title: 'Constitutional Authority',
  text: 'The Civil Service Commission (CSC), as the central personnel agency of the Government, shall establish a career service and adopt measures to promote morale, efficiency, integrity, responsiveness, progressiveness, and courtesy in the civil service. It shall strengthen the merit and rewards system, integrate all human resources development programs for all levels and ranks, and institutionalize a management climate conducive to public accountability. It shall submit to the President and the Congress an annual report on its personnel programs.',
  icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
}

const corePurposeVision = [
  {
    label: 'Core Purpose',
    title: 'Serving the Filipino People',
    text: 'Gawing Lingkod Bayani ang Bawat Kawani (To make every civil servant a servant hero).',
    icon: 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9',
  },
  {
    label: 'Vision',
    title: 'A Competent Civil Service',
    text: 'By 2030, the CSC shall be the leader in empowering people and organizations in HR and OD, and in serving the public through streamlined and digitalized services.',
    icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
  },
]

const values = [
  { label: 'Patriotism', svg: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4' },
  { label: 'Integrity', svg: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
  { label: 'Excellence', svg: 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z' },
  { label: 'Spirituality', svg: 'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418' },
]
</script>
