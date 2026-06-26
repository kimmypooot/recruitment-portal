<template>
  <Teleport to="body">
    <div v-if="app" class="fixed inset-0 z-50 flex justify-end">
      <div class="absolute inset-0 bg-black/40" @click="$emit('close')"></div>
      <div class="relative bg-white w-full max-w-lg h-full flex flex-col shadow-2xl overflow-hidden">

        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 text-sm font-bold flex-shrink-0">
              {{ initials }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ applicantName }}</p>
              <p class="text-xs text-gray-400">Applicant Credentials</p>
            </div>
          </div>
          <button @click="$emit('close')" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
          <div v-if="loading" class="space-y-4">
            <div v-for="n in 6" :key="n" class="h-16 bg-gray-100 rounded-xl animate-pulse"></div>
          </div>

          <template v-else-if="profile">
            <section>
              <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-md bg-blue-100 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">Work Experience</h3>
                <span class="text-xs text-gray-400">({{ profile.work_experiences?.length ?? 0 }})</span>
              </div>
              <div v-if="profile.work_experiences?.length" class="space-y-2.5">
                <div v-for="exp in profile.work_experiences" :key="exp.id"
                  class="rounded-xl border border-gray-200 p-3.5 bg-white hover:bg-gray-50 transition-colors">
                  <div class="flex items-start justify-between gap-2 mb-1">
                    <p class="text-sm font-semibold text-gray-900 leading-snug">{{ exp.position_title }}</p>
                    <span :class="exp.government_service ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500'"
                      class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full flex-shrink-0 mt-0.5">
                      {{ exp.government_service ? "Gov't" : 'Private' }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-600 font-medium">{{ exp.department_agency }}</p>
                  <p class="text-xs text-gray-400 mt-1">
                    {{ exp.date_from }} – {{ exp.is_present ? 'Present' : (exp.date_to ?? '—') }}
                    <span v-if="exp.appointment_status" class="mx-1.5 text-gray-200">·</span>
                    <span v-if="exp.appointment_status">{{ exp.appointment_status }}</span>
                  </p>
                </div>
              </div>
              <p v-else class="text-sm text-gray-400 italic">No work experience recorded.</p>
            </section>

            <section>
              <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-md bg-purple-100 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                  </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">Education</h3>
                <span class="text-xs text-gray-400">({{ profile.educational_attainments?.length ?? 0 }})</span>
              </div>
              <div v-if="profile.educational_attainments?.length" class="space-y-2.5">
                <div v-for="edu in profile.educational_attainments" :key="edu.id"
                  class="rounded-xl border border-gray-200 p-3.5 bg-white hover:bg-gray-50 transition-colors">
                  <div class="flex items-start justify-between gap-2 mb-1">
                    <p class="text-sm font-semibold text-gray-900 leading-snug">{{ edu.degree_course ?? edu.level }}</p>
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-purple-50 text-purple-700 flex-shrink-0 mt-0.5 capitalize">
                      {{ edu.level?.replace('_', ' ') }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-600 font-medium">{{ edu.school_name }}</p>
                  <p class="text-xs text-gray-400 mt-1">
                    <span v-if="edu.period_from">{{ edu.period_from }}{{ edu.period_to ? ' – ' + edu.period_to : '' }}</span>
                    <span v-if="edu.year_graduated" class="ml-1">· Graduated {{ edu.year_graduated }}</span>
                    <span v-if="edu.honors" class="ml-1">· {{ edu.honors }}</span>
                  </p>
                </div>
              </div>
              <p v-else class="text-sm text-gray-400 italic">No educational attainment recorded.</p>
            </section>

            <section>
              <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-md bg-orange-100 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3.5 h-3.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                  </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">Trainings & Seminars</h3>
                <span class="text-xs text-gray-400">({{ profile.trainings?.length ?? 0 }})</span>
              </div>
              <div v-if="profile.trainings?.length" class="space-y-2.5">
                <div v-for="tr in profile.trainings" :key="tr.id"
                  class="rounded-xl border border-gray-200 p-3.5 bg-white hover:bg-gray-50 transition-colors">
                  <p class="text-sm font-semibold text-gray-900 leading-snug mb-1">{{ tr.title }}</p>
                  <p v-if="tr.conducted_by" class="text-xs text-gray-600 font-medium">{{ tr.conducted_by }}</p>
                  <div class="flex items-center gap-3 mt-1">
                    <p class="text-xs text-gray-400">{{ tr.date_from }}{{ tr.date_to ? ' – ' + tr.date_to : '' }}</p>
                    <span v-if="tr.hours" class="text-xs font-semibold text-orange-700 bg-orange-50 px-1.5 py-0.5 rounded-full">{{ tr.hours }}h</span>
                    <span v-if="tr.ld_type" class="text-xs text-gray-400">· {{ tr.ld_type }}</span>
                  </div>
                </div>
              </div>
              <p v-else class="text-sm text-gray-400 italic">No training records.</p>
            </section>

            <section>
              <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-md bg-green-100 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3.5 h-3.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                  </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">Eligibility</h3>
              </div>
              <div v-if="profile.eligibility" class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-sm font-semibold text-green-900">{{ profile.eligibility }}</p>
                <p v-if="profile.eligibility_other" class="text-xs text-green-700 mt-1">
                  Additional: {{ profile.eligibility_other }}
                </p>
              </div>
              <p v-else class="text-sm text-gray-400 italic">No eligibility recorded.</p>
            </section>
          </template>

          <div v-else class="flex flex-col items-center justify-center py-16 text-center">
            <p class="text-sm text-gray-400">No credentials data available.</p>
          </div>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  app: { type: Object, default: null },
  profile: { type: Object, default: null },
  loading: { type: Boolean, default: false },
})

defineEmits(['close'])

const initials = computed(() => {
  const name = props.app?.applicant?.user?.name
  return name?.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase() ?? '?'
})

const applicantName = computed(() => {
  if (!props.app) return ''
  const p = props.app?.applicant
  if (p?.last_name && p?.first_name) {
    const middle = p.middle_name ? ' ' + p.middle_name : ''
    return `${p.last_name}, ${p.first_name}${middle}`
  }
  return p?.user?.name ?? '—'
})
</script>
