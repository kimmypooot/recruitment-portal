<template>
  <div class="space-y-5">

    <!-- Work Experience -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-900">Relevant Work Experience</p>
          <p class="text-xs text-amber-600 font-medium mt-0.5">Enter experience relevant to the applied position</p>
        </div>
        <button @click="$emit('add-experience')"
          class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-xs font-semibold rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Add Experience
        </button>
      </div>
      <div class="px-6 py-5">
        <div v-if="experiences.length" class="overflow-x-auto -mx-6 px-6">
          <table class="w-full text-sm min-w-[540px]">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 w-1/3">Position Title</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 w-1/3">Agency</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Period</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Gov't</th>
                <th class="pb-2.5 w-16"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="exp in experiences" :key="exp.id" class="group">
                <td class="py-3 pr-4 font-medium text-gray-900">{{ exp.position_title }}</td>
                <td class="py-3 pr-4 text-gray-600">{{ exp.department_agency }}</td>
                <td class="py-3 pr-4 text-gray-500 whitespace-nowrap">
                  {{ formatDateRange(exp.date_from, exp.is_present ? null : exp.date_to, exp.is_present) }}
                </td>
                <td class="py-3 pr-4 text-gray-500">{{ exp.government_service ? 'Yes' : 'No' }}</td>
                <td class="py-3 text-right">
                  <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="$emit('edit-experience', exp)"
                      class="p-1 rounded text-gray-400 hover:text-[#2a338f] transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <button @click="$emit('delete-experience', exp.id)"
                      class="p-1 rounded text-gray-400 hover:text-red-500 transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="flex flex-col items-center justify-center py-10 text-center">
          <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-400">No work experience added yet</p>
          <p class="text-xs text-gray-400 mt-0.5">Click "Add Experience" to get started</p>
        </div>
      </div>
    </div>

    <!-- Education -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-900">Educational Attainment</p>
          <p class="text-xs text-gray-500 mt-0.5">List all levels from highest to lowest</p>
        </div>
        <button @click="$emit('add-education')"
          class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-xs font-semibold rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Add Education
        </button>
      </div>
      <div class="px-6 py-5">
        <div v-if="educationRecords.length" class="overflow-x-auto -mx-6 px-6">
          <table class="w-full text-sm min-w-[580px]">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Level</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">School</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Degree</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Period</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Graduated</th>
                <th class="pb-2.5 w-16"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="edu in educationRecords" :key="edu.id" class="group">
                <td class="py-3 pr-4 text-gray-700 whitespace-nowrap">{{ formatLevel(edu.level) }}</td>
                <td class="py-3 pr-4 font-medium text-gray-900">{{ edu.school_name }}</td>
                <td class="py-3 pr-4 text-gray-500">{{ edu.degree_course ?? '—' }}</td>
                <td class="py-3 pr-4 text-gray-500 whitespace-nowrap">{{ edu.period_from ?? '—' }} – {{ edu.period_to ?? '—' }}</td>
                <td class="py-3 pr-4 text-gray-500">{{ edu.year_graduated ?? '—' }}</td>
                <td class="py-3 text-right">
                  <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="$emit('edit-education', edu)"
                      class="p-1 rounded text-gray-400 hover:text-[#2a338f] transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <button @click="$emit('delete-education', edu.id)"
                      class="p-1 rounded text-gray-400 hover:text-red-500 transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="flex flex-col items-center justify-center py-10 text-center">
          <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-400">No education records added yet</p>
          <p class="text-xs text-gray-400 mt-0.5">Click "Add Education" to get started</p>
        </div>
      </div>
    </div>

    <!-- Training -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <div class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-900">Relevant Trainings</p>
          <p class="text-xs text-amber-600 font-medium mt-0.5">Enter trainings relevant to the applied position</p>
        </div>
        <button @click="$emit('add-training')"
          class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-xs font-semibold rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Add Training
        </button>
      </div>
      <div class="px-6 py-5">
        <div v-if="trainings.length" class="overflow-x-auto -mx-6 px-6">
          <table class="w-full text-sm min-w-[600px]">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 w-1/3">Title</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Date</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Hours</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Type</th>
                <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Conducted by</th>
                <th class="pb-2.5 w-16"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="t in trainings" :key="t.id" class="group">
                <td class="py-3 pr-4 font-medium text-gray-900">{{ t.title }}</td>
                <td class="py-3 pr-4 text-gray-500 whitespace-nowrap">{{ formatDateRange(t.date_from, t.date_to) }}</td>
                <td class="py-3 pr-4 text-gray-500">{{ t.hours ?? '—' }}</td>
                <td class="py-3 pr-4 text-gray-500">{{ t.ld_type ?? '—' }}</td>
                <td class="py-3 pr-4 text-gray-500">{{ t.conducted_by ?? '—' }}</td>
                <td class="py-3 text-right">
                  <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="$emit('edit-training', t)"
                      class="p-1 rounded text-gray-400 hover:text-[#2a338f] transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <button @click="$emit('delete-training', t.id)"
                      class="p-1 rounded text-gray-400 hover:text-red-500 transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="flex flex-col items-center justify-center py-10 text-center">
          <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-400">No training records added yet</p>
          <p class="text-xs text-gray-400 mt-0.5">Click "Add Training" to get started</p>
        </div>
      </div>
    </div>

    <!-- Eligibility & Other Info -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <div class="w-9 h-9 rounded-lg bg-[#2a338f]/10 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-900">Eligibility &amp; Other Information</p>
          <p class="text-xs text-gray-500 mt-0.5">Civil Service eligibility and special classifications</p>
        </div>
      </div>
      <div class="px-6 py-6 space-y-6">
        <div class="max-w-sm">
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
            Eligibility <span class="text-red-500 normal-case">*</span>
          </label>
          <select v-model="personal.eligibility"
            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
            <option value="">— Select —</option>
            <option value="N/A">N/A</option>
            <option value="Career Service Professional Eligibility">Career Service Professional Eligibility</option>
            <option value="Career Service Subprofessional Eligibility">Career Service Subprofessional Eligibility</option>
            <option value="RA 1080 (Bar/Board Exam)">RA 1080 (Bar/Board Exam)</option>
            <option value="PD 907 (Honor Graduate Eligibility)">PD 907 (Honor Graduate Eligibility)</option>
            <option value="RA 9418 (Eligibility for NP/EO)">RA 9418 (Eligibility for NP/EO)</option>
            <option value="RA 10817">RA 10817 (Leveraging Human Resource for Careers Act Eligibility)</option>
            <option value="Contractual Service Eligibility">Contractual Service Eligibility</option>
            <option value="Barangay Official Eligibility">Barangay Official Eligibility (BOE)</option>
            <option value="Honor Graduate Eligibility">Honor Graduate Eligibility (DepEd)</option>
            <option value="Skill Eligibility">Skill Eligibility</option>
            <option value="Sanggunian Eligibility">Sanggunian Eligibility</option>
            <option value="Veteran Preference Rating">Veteran Preference Rating</option>
            <option value="Others">Others</option>
          </select>
          <div v-if="personal.eligibility === 'Others'" class="mt-2">
            <input v-model="personal.eligibility_other" type="text" placeholder="Specify eligibility"
              class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition uppercase" />
          </div>
        </div>

        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Special Classifications</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-lg border border-gray-200 px-4 py-3.5">
              <p class="text-xs font-semibold text-gray-700 mb-2.5">Indigenous People / Cultural Community</p>
              <div class="flex gap-4">
                <label v-for="v in ['Yes', 'No']" :key="v" class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" :value="v" v-model="personal.indigenous_group" class="accent-blue-700 w-4 h-4" />
                  <span class="text-sm text-gray-700">{{ v }}</span>
                </label>
              </div>
            </div>
            <div class="rounded-lg border border-gray-200 px-4 py-3.5">
              <p class="text-xs font-semibold text-gray-700 mb-2.5">Person with Disability (PWD)</p>
              <div class="flex gap-4">
                <label v-for="v in ['Yes', 'No']" :key="v" class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" :value="v" v-model="personal.pwd" class="accent-blue-700 w-4 h-4" />
                  <span class="text-sm text-gray-700">{{ v }}</span>
                </label>
              </div>
            </div>
            <div class="rounded-lg border border-gray-200 px-4 py-3.5">
              <p class="text-xs font-semibold text-gray-700 mb-2.5">Solo Parent</p>
              <div class="flex gap-4">
                <label v-for="v in ['Yes', 'No']" :key="v" class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" :value="v" v-model="personal.solo_parent" class="accent-blue-700 w-4 h-4" />
                  <span class="text-sm text-gray-700">{{ v }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
defineProps({
  personal:         { type: Object, required: true },
  experiences:      { type: Array,  default: () => [] },
  educationRecords: { type: Array,  default: () => [] },
  trainings:        { type: Array,  default: () => [] },
})

defineEmits([
  'add-experience', 'edit-experience', 'delete-experience',
  'add-education', 'edit-education', 'delete-education',
  'add-training', 'edit-training', 'delete-training',
])

function formatDateRange(from, to, isPresent = false) {
  if (!from) return '—'
  const MONTHS = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'Jun.',
                  'Jul.', 'Aug.', 'Sep.', 'Oct.', 'Nov.', 'Dec.']
  const parse = (str) => { const [y, m, d] = str.split('-').map(Number); return { y, m: m - 1, d } }
  const f = parse(from)
  const fromStr = `${MONTHS[f.m]} ${f.d}`
  if (isPresent) return `${fromStr}, ${f.y} – Present`
  if (!to)       return `${fromStr}, ${f.y}`
  const t = parse(to)
  if (f.m === t.m && f.y === t.y) {
    if (f.d === t.d) return `${fromStr}, ${f.y}`
    return `${MONTHS[f.m]} ${f.d}-${t.d}, ${f.y}`
  }
  if (f.y === t.y) return `${fromStr} - ${MONTHS[t.m]} ${t.d}, ${f.y}`
  return `${fromStr}, ${f.y} - ${MONTHS[t.m]} ${t.d}, ${t.y}`
}

function formatLevel(level) {
  return level?.replace(/_/g, ' ') ?? level
}
</script>
