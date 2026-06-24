<template>
  <PublicLayout>

    <Head>
      <title>How to Apply — CSC Recruitment</title>
      <meta name="description" content="Step-by-step guide on how to apply for government positions through the CSC Regional Office VIII online recruitment portal." />
    </Head>

    <!-- Hero -->
    <section class="relative text-white overflow-hidden"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <!-- Brand gradient overlay -->
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(30,37,112,0.88) 0%, rgba(42,51,143,0.85) 50%, rgba(26,31,94,0.90) 100%);"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="max-w-2xl">
          <p class="text-white/60 text-sm font-medium uppercase tracking-wider mb-3">Application Guide</p>
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">How to Apply</h1>
          <p class="text-white/80 text-base leading-relaxed">
            Follow these steps to submit your application for a position at the Civil Service Commission Regional Office.
          </p>
        </div>
      </div>
      <div class="relative h-10 overflow-hidden z-10">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <!-- Steps -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
      <div class="space-y-10">

        <div v-for="(step, index) in steps" :key="index" class="flex gap-6">
          <!-- Step number -->
          <div class="flex-shrink-0 w-12 h-12 rounded-full bg-[#2a338f] text-white flex items-center justify-center font-bold text-lg shadow">
            {{ index + 1 }}
          </div>
          <!-- Content -->
          <div class="pt-1.5 flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ step.title }}</h3>
            <p class="text-gray-600 text-sm leading-relaxed">{{ step.description }}</p>
            <ul v-if="step.items" class="mt-3 space-y-1.5">
              <li v-for="item in step.items" :key="item" class="flex items-start gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 mt-0.5 text-[#2a338f] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ item }}
              </li>
            </ul>
          </div>
        </div>

      </div>
    </section>

    <!-- Required Documents -->
    <section class="bg-white border-y border-gray-200">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Required Documents</h2>
        <p class="text-gray-500 text-sm mb-8">Prepare these documents before starting your application. All files must be in PDF, JPG, or PNG format (max 10 MB each).</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="doc in documents" :key="doc.name" class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50">
            <div class="w-9 h-9 rounded-lg bg-[#2a338f]/10 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ doc.name }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ doc.note }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
      <h2 class="text-xl font-bold text-gray-900 mb-2">Ready to apply?</h2>
      <p class="text-gray-500 text-sm mb-6">Create a free account to start tracking your application.</p>
      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <Link href="/register" class="px-6 py-3 bg-[#2a338f] text-white font-semibold text-sm rounded-lg hover:bg-[#1e2570] transition-colors shadow-sm">
          Create Account
        </Link>
        <Link href="/" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold text-sm rounded-lg hover:bg-gray-50 transition-colors">
          Browse Vacancies
        </Link>
      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const steps = [
  {
    title: 'Browse Open Vacancies',
    description: 'Visit the Vacancies page and review all currently open positions. Use the filters to narrow down by salary grade, office, or deadline.',
  },
  {
    title: 'Create an Account',
    description: 'Register for a free applicant account using your email address. You will need an account to submit and track your application.',
  },
  {
    title: 'Prepare Your Documents',
    description: 'Gather all required documents before starting your application. Scanned copies must be clear and legible.',
    items: [
      'Personal Data Sheet (CS Form No. 212)',
      'Certified true copies of eligibility certificates',
      'Certified true copies of academic credentials',
      'Performance ratings (last 2 rating periods)',
      'Work experience records',
    ],
  },
  {
    title: 'Submit Your Application',
    description: 'Fill out the online application form completely and upload all required documents. Double-check all information before submitting — applications cannot be edited after submission.',
  },
  {
    title: 'Track Your Application',
    description: 'Log in to your account anytime to check the status of your application. You will also receive email notifications whenever your status is updated.',
  },
  {
    title: 'Await Further Instructions',
    description: 'Qualified applicants will be notified via email for written examinations and/or panel interviews. Results of the selection process will be communicated through your registered email.',
  },
]

const documents = [
  { name: 'Personal Data Sheet', note: 'CS Form No. 212, revised 2017' },
  { name: 'Civil Service Eligibility', note: 'Certified true copy' },
  { name: 'Diploma / Transcript of Records', note: 'Certified true copy' },
  { name: 'Certificate of Training', note: 'Relevant trainings and seminars' },
  { name: 'Performance Rating', note: 'Last two rating periods (at least Very Satisfactory)' },
  { name: 'Work Experience Record', note: 'CS Form No. 212 Attachment' },
]
</script>
