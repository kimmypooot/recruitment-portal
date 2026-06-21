<template>
  <PublicLayout>

    <!-- Hero -->
    <section class="bg-gradient-to-br from-blue-800 via-blue-700 to-blue-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="max-w-2xl">
          <p class="text-blue-300 text-sm font-medium uppercase tracking-wider mb-3">Who We Are</p>
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">About the CSC Regional Office</h1>
          <p class="text-blue-100 text-base leading-relaxed">
            The Civil Service Commission is the central personnel agency of the Philippine government,
            mandated to establish a career service and promote morale, efficiency, integrity, responsiveness,
            and courtesy in the civil service.
          </p>
        </div>
      </div>
      <div class="relative h-10 overflow-hidden">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <!-- Mission / Vision / Mandate -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div v-for="card in mvg" :key="card.label" class="p-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
          <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mb-4">
            <svg class="w-5 h-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon"/>
            </svg>
          </div>
          <p class="text-xs font-semibold text-blue-700 uppercase tracking-wider mb-1">{{ card.label }}</p>
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
          The CSC Regional Office Recruitment Portal is the official online platform for posting career opportunities
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
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
        <div v-for="value in values" :key="value.label" class="text-center p-5 rounded-2xl bg-blue-50 border border-blue-100">
          <p class="text-2xl mb-2">{{ value.emoji }}</p>
          <p class="text-xs font-semibold text-blue-800">{{ value.label }}</p>
        </div>
      </div>
    </section>

    <!-- Contact -->
    <section class="bg-blue-800 text-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <h2 class="text-2xl font-bold mb-8 text-center">Contact Us</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
          <div>
            <p class="text-blue-300 text-xs uppercase tracking-wider mb-1">Address</p>
            <p class="text-sm">Eastern Visayas Regional Office<br>CSC Regional Office</p>
          </div>
          <div>
            <p class="text-blue-300 text-xs uppercase tracking-wider mb-1">Email</p>
            <p class="text-sm">recruitment@csc-ro.gov.ph</p>
          </div>
          <div>
            <p class="text-blue-300 text-xs uppercase tracking-wider mb-1">Phone</p>
            <p class="text-sm">(055) 123-4567</p>
          </div>
        </div>
      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'

const mvg = [
  {
    label: 'Mission',
    title: 'Serving the Filipino People',
    text: 'To promote integrity, professionalism, and excellence in the civil service and ensure that government personnel are selected based on merit and fitness.',
    icon: 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9',
  },
  {
    label: 'Vision',
    title: 'A Competent Civil Service',
    text: 'A civil service characterized by patriotism, professionalism, and integrity — imbued with a sense of public accountability and dedication to a democratic way of life.',
    icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
  },
  {
    label: 'Mandate',
    title: 'Constitutional Authority',
    text: 'As the central personnel agency, the CSC establishes and administers a career service system, ensuring appointments based on merit and fitness as determined by competitive examinations.',
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
  },
]

const values = [
  { label: 'Integrity', emoji: '⚖️' },
  { label: 'Excellence', emoji: '🌟' },
  { label: 'Accountability', emoji: '📋' },
  { label: 'Fairness', emoji: '🤝' },
  { label: 'Service', emoji: '🇵🇭' },
]
</script>
