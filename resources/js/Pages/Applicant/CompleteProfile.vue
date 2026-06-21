<template>
  <ApplicantLayout>
    <div class="max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">

      <!-- Page header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Complete Your Profile</h1>
        <p class="mt-1.5 text-sm text-gray-500 max-w-2xl">
          Fill out all sections below before submitting an application. Each section saves independently.
        </p>
      </div>

      <!-- Step tabs -->
      <div class="bg-white border border-gray-200 rounded-xl p-1.5 mb-7 shadow-sm">
        <div class="flex gap-1">
          <button
            v-for="tab in tabs" :key="tab.key"
            @click="activeTab = tab.key"
            class="flex flex-1 items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors min-w-0"
            :class="activeTab === tab.key
              ? 'bg-[#2a338f] text-white shadow-sm'
              : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon"/>
            </svg>
            <span class="truncate">{{ tab.label }}</span>
            <span v-if="tab.complete"
              class="flex-shrink-0 w-2 h-2 rounded-full"
              :class="activeTab === tab.key ? 'bg-green-300' : 'bg-green-500'">
            </span>
          </button>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════
           TAB 1 — Personal Info
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'personal'" class="space-y-5">

        <!-- Profile Photo Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-9 h-9 rounded-lg bg-[#2a338f]/10 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Profile Photo</p>
              <p class="text-xs text-gray-500 mt-0.5">Passport-size, professional photo for your application</p>
            </div>
          </div>
          <div class="px-6 py-5 flex items-center gap-6">
            <!-- Photo preview -->
            <div class="flex-shrink-0">
              <div class="w-28 h-28 rounded-full border-2 overflow-hidden flex items-center justify-center bg-gray-50"
                :class="photoPath ? 'border-[#2a338f]/30' : 'border-dashed border-gray-300'">
                <img v-if="photoPath" :src="photoUrl" class="w-full h-full object-cover" alt="Profile photo" />
                <svg v-else class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
              </div>
            </div>
            <!-- Info + button -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800">
                {{ photoPath ? 'Photo uploaded' : 'No photo yet' }}
              </p>
              <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                Use a recent photo with a plain background. Face the camera directly,
                no sunglasses or hats. Good lighting is essential.
              </p>
              <button @click="openPhotoModal"
                class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-xs font-semibold rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                {{ photoPath ? 'Change Photo' : 'Upload Photo' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Personal details card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-9 h-9 rounded-lg bg-[#2a338f]/10 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-[#2a338f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Additional Personal Details</p>
              <p class="text-xs text-gray-500 mt-0.5">Gender, civil status, birthday, and religion</p>
            </div>
          </div>
          <div class="px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Gender <span class="text-red-500 normal-case">*</span>
              </label>
              <select v-model="personal.gender"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
                <option value="">— Select —</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Civil Status <span class="text-red-500 normal-case">*</span>
              </label>
              <select v-model="personal.civil_status"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
                <option value="">— Select —</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Separated">Separated</option>
                <option value="Annulled">Annulled</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Date of Birth <span class="text-red-500 normal-case">*</span>
              </label>
              <input v-model="personal.birthday" type="date"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Religion</label>
              <input v-model="personal.religion" type="text" placeholder="e.g. Roman Catholic"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

          </div>
        </div>

        <!-- Address card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Residential Address</p>
              <p class="text-xs text-gray-500 mt-0.5">Region, province, city/municipality, barangay</p>
            </div>
          </div>
          <div class="px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">

            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Region <span class="text-red-500 normal-case">*</span>
              </label>
              <select v-model="personal.region"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
                <option value="">— Select Region —</option>
                <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Province <span class="text-red-500 normal-case">*</span>
              </label>
              <input v-model="personal.province" type="text" placeholder="e.g. Cebu"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                City / Municipality <span class="text-red-500 normal-case">*</span>
              </label>
              <input v-model="personal.city_municipality" type="text" placeholder="e.g. Cebu City"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Barangay <span class="text-red-500 normal-case">*</span>
              </label>
              <input v-model="personal.barangay" type="text" placeholder="e.g. Lahug"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

          </div>
        </div>

        <!-- Contact card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Contact Information</p>
              <p class="text-xs text-gray-500 mt-0.5">Mobile number and email address</p>
            </div>
          </div>
          <div class="px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Mobile Number <span class="text-red-500 normal-case">*</span>
              </label>
              <input v-model="personal.mobile_number" type="tel" placeholder="09XX XXX XXXX"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Email Address</label>
              <input :value="authUser.email" type="email" disabled
                class="w-full px-3 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-400 cursor-not-allowed" />
              <p class="text-xs text-gray-400 mt-1.5">Set from your account — cannot be changed here.</p>
            </div>

          </div>
        </div>

        <!-- Tab 1 save footer -->
        <div class="flex items-center justify-end pt-1">
          <button @click="savePersonal" :disabled="savingPersonal"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
            <svg v-if="savingPersonal" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            {{ savingPersonal ? 'Saving…' : 'Save Personal Info' }}
          </button>
        </div>

      </div>

      <!-- ══════════════════════════════════════════════════════════════
           TAB 2 — Experience, Education, Training
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'qualifications'" class="space-y-5">

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
            <button @click="openExpModal()"
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
                    <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 w-1/3">Agency / Office</th>
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
                        <button @click="openExpModal(exp)"
                          class="p-1 rounded text-gray-400 hover:text-[#2a338f] transition-colors">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                          </svg>
                        </button>
                        <button @click="deleteExperience(exp.id)"
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
            <button @click="openEduModal()"
              class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-xs font-semibold rounded-lg transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Add Education
            </button>
          </div>
          <div class="px-6 py-5">
            <div v-if="education.length" class="overflow-x-auto -mx-6 px-6">
              <table class="w-full text-sm min-w-[580px]">
                <thead>
                  <tr class="border-b border-gray-100">
                    <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Level</th>
                    <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">School</th>
                    <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Degree / Course</th>
                    <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Period</th>
                    <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Graduated</th>
                    <th class="pb-2.5 w-16"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="edu in education" :key="edu.id" class="group">
                    <td class="py-3 pr-4 text-gray-700 whitespace-nowrap">{{ formatLevel(edu.level) }}</td>
                    <td class="py-3 pr-4 font-medium text-gray-900">{{ edu.school_name }}</td>
                    <td class="py-3 pr-4 text-gray-500">{{ edu.degree_course ?? '—' }}</td>
                    <td class="py-3 pr-4 text-gray-500 whitespace-nowrap">{{ edu.period_from ?? '—' }} – {{ edu.period_to ?? '—' }}</td>
                    <td class="py-3 pr-4 text-gray-500">{{ edu.year_graduated ?? '—' }}</td>
                    <td class="py-3 text-right">
                      <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEduModal(edu)"
                          class="p-1 rounded text-gray-400 hover:text-[#2a338f] transition-colors">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                          </svg>
                        </button>
                        <button @click="deleteEducation(edu.id)"
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
            <button @click="openTrainingModal()"
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
                    <th class="pb-2.5 pr-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 w-1/3">Title of L&D Intervention</th>
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
                        <button @click="openTrainingModal(t)"
                          class="p-1 rounded text-gray-400 hover:text-[#2a338f] transition-colors">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                          </svg>
                        </button>
                        <button @click="deleteTraining(t.id)"
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

            <!-- Eligibility select -->
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
                <option value="RA 1080">RA 1080</option>
                <option value="PD 907 (Honor Graduate Eligibility)">PD 907 (Honor Graduate Eligibility)</option>
                <option value="Others">Others</option>
              </select>
              <div v-if="personal.eligibility === 'Others'" class="mt-2">
                <input v-model="personal.eligibility_other" type="text" placeholder="Specify eligibility"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition uppercase" />
              </div>
            </div>

            <!-- Special classifications — 3-col -->
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

        <!-- Tab 2 save footer -->
        <div class="flex items-center justify-end pt-1">
          <button @click="savePersonal" :disabled="savingPersonal"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
            <svg v-if="savingPersonal" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            {{ savingPersonal ? 'Saving…' : 'Save Eligibility Info' }}
          </button>
        </div>

      </div>

      <!-- ══════════════════════════════════════════════════════════════
           TAB 3 — Documents / Attachments
      ══════════════════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'documents'" class="space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Attachments</p>
              <p class="text-xs text-gray-500 mt-0.5">PDF only · Max 5 MB per file</p>
            </div>
          </div>

          <div class="px-6 py-6">

            <!-- Info banner -->
            <div class="flex items-start gap-2.5 rounded-lg border border-[#2a338f]/20 bg-[#2a338f]/5 px-4 py-3 text-xs text-[#2a338f] mb-6">
              <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span>Only <strong>PDF files</strong> are accepted. Each file must not exceed <strong>5 MB</strong>. Fields marked <span class="text-red-600 font-bold">*</span> are required.</span>
            </div>

            <!-- Upload zones grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div v-for="doc in docFields" :key="doc.key" :class="doc.full ? 'sm:col-span-2' : ''">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                  {{ doc.label }}
                  <span v-if="doc.required" class="text-red-500 normal-case ml-0.5">*</span>
                  <span v-else class="normal-case font-normal text-gray-400 ml-1">(optional)</span>
                  <span v-if="doc.note" class="normal-case font-normal text-gray-400 ml-1">— {{ doc.note }}</span>
                </p>

                <label
                  class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-xl px-4 py-5 cursor-pointer transition-colors group"
                  :class="docFiles[doc.key] || docPaths[doc.key]
                    ? 'border-green-400 bg-green-50'
                    : 'border-gray-200 hover:border-[#2a338f]/40 hover:bg-[#2a338f]/5'">
                  <input type="file" :name="doc.key" accept=".pdf" class="sr-only"
                    @change="handleFileSelect($event, doc.key)" />

                  <svg v-if="!(docFiles[doc.key] || docPaths[doc.key])"
                    class="w-7 h-7 text-gray-300 group-hover:text-[#2a338f] transition-colors"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                  </svg>
                  <svg v-else class="w-7 h-7 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>

                  <p class="text-sm font-medium text-center leading-snug"
                    :class="docFiles[doc.key] || docPaths[doc.key] ? 'text-green-700' : 'text-gray-500 group-hover:text-[#2a338f]'">
                    {{ docFiles[doc.key]?.name ?? (docPaths[doc.key] ? 'Uploaded — click to replace' : 'Click to browse or drag & drop') }}
                  </p>
                  <p v-if="!(docFiles[doc.key] || docPaths[doc.key])"
                    class="text-xs text-gray-400">PDF format only</p>
                </label>

                <!-- View button for already-uploaded files -->
                <div v-if="docPaths[doc.key] && !docFiles[doc.key]" class="mt-1.5 flex justify-end">
                  <a :href="`/profile/documents/${docPaths[doc.key]}?token=${authToken}`"
                    target="_blank"
                    class="inline-flex items-center gap-1 text-xs font-medium text-[#2a338f] hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View document
                  </a>
                </div>

              </div>
            </div>

            <!-- Upload button -->
            <div class="mt-6 pt-5 border-t border-gray-100 flex items-center justify-end">
              <button @click="uploadDocuments" :disabled="savingDocs"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                <svg v-if="savingDocs" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                {{ savingDocs ? 'Uploading…' : 'Upload Documents' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Completion CTA -->
        <div v-if="isComplete"
          class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-green-200 bg-green-50 px-5 py-4">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-green-800">Your profile is complete!</p>
              <p class="text-xs text-green-700 mt-0.5">You can now apply to open vacancies.</p>
            </div>
          </div>
          <Link href="/"
            class="flex-shrink-0 self-start sm:self-center px-4 py-2 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg transition-colors">
            Browse Vacancies
          </Link>
        </div>

      </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════════
         MODAL — Add Work Experience
    ══════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div v-if="expModal.open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="expModal.open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">

          <!-- Header -->
          <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 flex-shrink-0">
            <div>
              <h3 class="text-base font-semibold text-gray-900">{{ expModal.editingId ? 'Edit Work Experience' : 'Add Work Experience' }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">Fields marked <span class="text-red-500">*</span> are required</p>
            </div>
            <button @click="expModal.open = false"
              class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="px-7 py-6 overflow-y-auto space-y-5">

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Position Title <span class="text-red-500 normal-case">*</span>
              </label>
              <input v-model="expForm.position_title" type="text" placeholder="e.g. Administrative Officer II"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Department / Agency / Office <span class="text-red-500 normal-case">*</span>
              </label>
              <input v-model="expForm.department_agency" type="text" placeholder="e.g. Civil Service Commission Regional Office"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5">

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Salary Grade</label>
                <input v-model="expForm.salary_grade" type="text" placeholder="e.g. 15-1"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Status of Appointment</label>
                <select v-model="expForm.appointment_status"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
                  <option value="">— Select —</option>
                  <option value="Permanent">Permanent</option>
                  <option value="Temporary">Temporary</option>
                  <option value="Casual">Casual</option>
                  <option value="Contractual">Contractual</option>
                  <option value="Co-terminus">Co-terminus</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                  Date From <span class="text-red-500 normal-case">*</span>
                </label>
                <input v-model="expForm.date_from" type="date"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Date To</label>
                <input v-model="expForm.date_to" type="date" :disabled="expForm.is_present"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition disabled:opacity-40 disabled:cursor-not-allowed" />
                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                  <input type="checkbox" v-model="expForm.is_present" class="w-4 h-4 accent-blue-700 rounded" />
                  <span class="text-xs text-gray-600 select-none">Currently employed here (present)</span>
                </label>
              </div>

            </div>

            <label class="flex items-center gap-2.5 cursor-pointer select-none">
              <input type="checkbox" id="gov" v-model="expForm.government_service" class="w-4 h-4 accent-blue-700 rounded" />
              <span class="text-sm text-gray-700 font-medium">This is a Government Service position</span>
            </label>

            <p v-if="expModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
              {{ expModal.error }}
            </p>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 px-7 py-4 border-t border-gray-100 flex-shrink-0">
            <button @click="expModal.open = false"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">
              Cancel
            </button>
            <button @click="saveExperience" :disabled="expModal.saving"
              class="inline-flex items-center gap-2 px-5 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60">
              <svg v-if="expModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ expModal.saving ? 'Saving…' : expModal.editingId ? 'Save Changes' : 'Add Experience' }}
            </button>
          </div>

        </div>
      </div>
    </Teleport>

    <!-- ══════════════════════════════════════════════════════════════
         MODAL — Add Education
    ══════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div v-if="eduModal.open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="eduModal.open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">

          <!-- Header -->
          <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 flex-shrink-0">
            <div>
              <h3 class="text-base font-semibold text-gray-900">{{ eduModal.editingId ? 'Edit Educational Attainment' : 'Add Educational Attainment' }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">Fields marked <span class="text-red-500">*</span> are required</p>
            </div>
            <button @click="eduModal.open = false"
              class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="px-7 py-6 overflow-y-auto space-y-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                  Level <span class="text-red-500 normal-case">*</span>
                </label>
                <select v-model="eduForm.level"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
                  <option value="">— Select —</option>
                  <option value="Elementary">Elementary</option>
                  <option value="Secondary">Secondary / High School</option>
                  <option value="Vocational / Trade Course">Vocational / Trade Course</option>
                  <option value="College">College</option>
                  <option value="Graduate Studies">Graduate Studies</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                  Year Graduated
                </label>
                <input v-model="eduForm.year_graduated" type="text" placeholder="e.g. 2019"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                School / University Name <span class="text-red-500 normal-case">*</span>
              </label>
              <input v-model="eduForm.school_name" type="text" placeholder="e.g. University of the Philippines Cebu"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Degree / Course</label>
              <input v-model="eduForm.degree_course" type="text" placeholder="e.g. Bachelor of Science in Computer Science"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-5 gap-y-5">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Period From (Year)</label>
                <input v-model="eduForm.period_from" type="text" placeholder="e.g. 2015"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Period To (Year)</label>
                <input v-model="eduForm.period_to" type="text" placeholder="e.g. 2019"
                  :disabled="eduForm.period_to_present"
                  :class="eduForm.period_to_present ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : ''"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
                <label class="inline-flex items-center gap-2 mt-2 cursor-pointer select-none">
                  <input type="checkbox" v-model="eduForm.period_to_present"
                    @change="eduForm.period_to_present ? (eduForm.period_to = '') : null"
                    class="w-4 h-4 rounded border-gray-300 text-[#2a338f] accent-[#2a338f] cursor-pointer" />
                  <span class="text-xs text-gray-600 font-medium">Present</span>
                </label>
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Units Earned</label>
                <input v-model="eduForm.units_earned" type="text" placeholder="e.g. 140 or N/A"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Scholarship / Academic Honors</label>
              <input v-model="eduForm.honors" type="text" placeholder="e.g. Cum Laude, Magna Cum Laude"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <p v-if="eduModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
              {{ eduModal.error }}
            </p>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 px-7 py-4 border-t border-gray-100 flex-shrink-0">
            <button @click="eduModal.open = false"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">
              Cancel
            </button>
            <button @click="saveEducation" :disabled="eduModal.saving"
              class="inline-flex items-center gap-2 px-5 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60">
              <svg v-if="eduModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ eduModal.saving ? 'Saving…' : eduModal.editingId ? 'Save Changes' : 'Add Education' }}
            </button>
          </div>

        </div>
      </div>
    </Teleport>

    <!-- ══════════════════════════════════════════════════════════════
         MODAL — Add Training
    ══════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div v-if="trainingModal.open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="trainingModal.open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">

          <!-- Header -->
          <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 flex-shrink-0">
            <div>
              <h3 class="text-base font-semibold text-gray-900">{{ trainingModal.editingId ? 'Edit Training' : 'Add Training / Learning &amp; Development' }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">Fields marked <span class="text-red-500">*</span> are required</p>
            </div>
            <button @click="trainingModal.open = false"
              class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="px-7 py-6 overflow-y-auto space-y-5">

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                Title of L&D Intervention <span class="text-red-500 normal-case">*</span>
              </label>
              <input v-model="trainingForm.title" type="text" placeholder="e.g. Leadership and Management Seminar"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                  Date From <span class="text-red-500 normal-case">*</span>
                </label>
                <input v-model="trainingForm.date_from" type="date"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Date To</label>
                <input v-model="trainingForm.date_to" type="date"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">No. of Hours</label>
                <input v-model="trainingForm.hours" type="number" min="0" step="0.5" placeholder="e.g. 8"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Type of L&D</label>
                <select v-model="trainingForm.ld_type"
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition">
                  <option value="">— Select —</option>
                  <option value="Managerial">Managerial</option>
                  <option value="Supervisory">Supervisory</option>
                  <option value="Technical">Technical</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Conducted / Sponsored by</label>
              <input v-model="trainingForm.conducted_by" type="text" placeholder="e.g. Civil Service Commission"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition" />
            </div>

            <p v-if="trainingModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
              {{ trainingModal.error }}
            </p>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 px-7 py-4 border-t border-gray-100 flex-shrink-0">
            <button @click="trainingModal.open = false"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">
              Cancel
            </button>
            <button @click="saveTraining" :disabled="trainingModal.saving"
              class="inline-flex items-center gap-2 px-5 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60">
              <svg v-if="trainingModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ trainingModal.saving ? 'Saving…' : trainingModal.editingId ? 'Save Changes' : 'Add Training' }}
            </button>
          </div>

        </div>
      </div>
    </Teleport>

    <!-- ══════════════════════════════════════════════════════════════
         MODAL — Upload Profile Photo
    ══════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div v-if="photoModal.open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @mousedown.self="closePhotoModal">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">

          <!-- Header -->
          <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 flex-shrink-0">
            <div>
              <h3 class="text-base font-semibold text-gray-900">
                {{ photoModal.step === 'select' ? 'Upload Profile Photo' : 'Crop Your Photo' }}
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">
                {{ photoModal.step === 'select'
                  ? 'Choose a clear, professional passport-size photo'
                  : 'Drag to move · Handles to resize · Scroll to zoom' }}
              </p>
            </div>
            <button @click="closePhotoModal"
              class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="overflow-y-auto flex-1">

            <!-- STEP: Select -->
            <div v-if="photoModal.step === 'select'" class="px-7 py-6 space-y-5">

              <!-- Requirements -->
              <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 space-y-2">
                <p class="text-xs font-semibold text-amber-800 uppercase tracking-wide">Photo Requirements</p>
                <ul class="space-y-1.5">
                  <li v-for="req in photoRequirements" :key="req" class="flex items-start gap-2 text-xs text-amber-700">
                    <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                    </svg>
                    {{ req }}
                  </li>
                </ul>
              </div>

              <!-- File pick zone -->
              <label
                class="flex flex-col items-center justify-center gap-3 border-2 border-dashed rounded-xl px-6 py-10 cursor-pointer transition-colors hover:border-[#2a338f]/50 hover:bg-[#2a338f]/5 group"
                :class="photoModal.error ? 'border-red-300 bg-red-50' : 'border-gray-200'">
                <input type="file" accept="image/jpeg,image/png,image/jpg" class="sr-only" @change="onPhotoFileSelect" />
                <div class="w-14 h-14 rounded-full bg-gray-100 group-hover:bg-[#2a338f]/10 flex items-center justify-center transition-colors">
                  <svg class="w-7 h-7 text-gray-400 group-hover:text-[#2a338f] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v13.5A1.5 1.5 0 003.75 21zm10.5-11.25h.008v.008h-.008V9.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                  </svg>
                </div>
                <div class="text-center">
                  <p class="text-sm font-medium text-gray-700 group-hover:text-[#2a338f] transition-colors">Click to browse or drag & drop</p>
                  <p class="text-xs text-gray-400 mt-0.5">JPG or PNG · Max 3 MB</p>
                </div>
              </label>

              <p v-if="photoModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                {{ photoModal.error }}
              </p>
            </div>

            <!-- STEP: Crop -->
            <div v-else class="px-7 py-5 space-y-4">
              <!-- Cropper container -->
              <div class="rounded-xl overflow-hidden bg-gray-900" style="height: 360px;">
                <img ref="cropperImgRef" :src="photoModal.imgSrc" class="block max-w-full" alt="Crop preview" />
              </div>

              <!-- Crop controls hint -->
              <div class="flex items-center justify-between">
                <p class="text-xs text-gray-400">Crop box is freely resizable — no fixed aspect ratio enforced.</p>
                <button @click="photoModal.step = 'select'; destroyCropper()"
                  class="text-xs text-[#2a338f] hover:underline font-medium">
                  ← Choose different photo
                </button>
              </div>

              <p v-if="photoModal.error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                {{ photoModal.error }}
              </p>
            </div>

          </div>

          <!-- Footer -->
          <div v-if="photoModal.step === 'crop'"
            class="flex items-center justify-end gap-3 px-7 py-4 border-t border-gray-100 flex-shrink-0">
            <button @click="closePhotoModal"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">
              Cancel
            </button>
            <button @click="savePhoto" :disabled="photoModal.saving"
              class="inline-flex items-center gap-2 px-5 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60">
              <svg v-if="photoModal.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ photoModal.saving ? 'Saving…' : 'Save Photo' }}
            </button>
          </div>

        </div>
      </div>
    </Teleport>

    <!-- Global save toast -->
    <transition name="fade">
      <div v-if="savedPersonal"
        class="fixed bottom-6 right-6 bg-green-600 text-white text-sm font-medium px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Saved successfully!
      </div>
    </transition>

  </ApplicantLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { profileApi } from '@/services/api'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import ApplicantLayout from '@/Layouts/ApplicantLayout.vue'

// ─── State ───────────────────────────────────────────────────────────────────

const activeTab     = ref('personal')
const isComplete    = ref(false)
const savingPersonal = ref(false)
const savedPersonal  = ref(false)
const savingDocs     = ref(false)

const photoPath = ref('')
const photoUrl  = computed(() =>
  photoPath.value ? `/profile/photo?token=${authToken}&_=${photoPath.value}` : null
)

const photoModal = reactive({ open: false, saving: false, error: '', step: 'select', imgSrc: '' })
const cropperImgRef = ref(null)
let cropperInstance = null

const photoRequirements = [
  'Recent photo taken within the last 6 months',
  'Plain white or light-colored background',
  'Face the camera directly with eyes open and clearly visible',
  'Neutral expression or a natural, slight smile',
  'No hats, caps, or head coverings (unless for religious reasons)',
  'No sunglasses or tinted eyeglasses',
  'Good, even lighting — avoid shadows on the face',
  'Passport-size or 2×2 inch equivalent is preferred',
]

const experiences = ref([])
const education   = ref([])
const trainings   = ref([])

const docFiles = reactive({})
const docPaths = reactive({})

const authUser  = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
const authToken = localStorage.getItem('auth_token') ?? ''

const personal = reactive({
  gender: '', civil_status: '', birthday: '', religion: '',
  region: '', province: '', city_municipality: '', barangay: '',
  mobile_number: '',
  eligibility: '', eligibility_other: '',
  indigenous_group: '', pwd: '', solo_parent: '',
})

// ─── Tabs ─────────────────────────────────────────────────────────────────────

const tabs = computed(() => [
  {
    key: 'personal', label: 'Personal Info',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    complete: !!(personal.gender && personal.civil_status && personal.birthday && personal.mobile_number && personal.region),
  },
  {
    key: 'qualifications', label: 'Experience & Education',
    icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    complete: !!(personal.eligibility),
  },
  {
    key: 'documents', label: 'Documents',
    icon: 'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13',
    complete: !!(docPaths.pds && docPaths.app_letter && docPaths.coe && docPaths.tor),
  },
])

// ─── Constants ───────────────────────────────────────────────────────────────

const regions = [
  'NCR - National Capital Region',
  'CAR - Cordillera Administrative Region',
  'Region I - Ilocos Region',
  'Region II - Cagayan Valley',
  'Region III - Central Luzon',
  'Region IV-A - CALABARZON',
  'Region IV-B - MIMAROPA',
  'Region V - Bicol Region',
  'Region VI - Western Visayas',
  'Region VII - Central Visayas',
  'Region VIII - Eastern Visayas',
  'Region IX - Zamboanga Peninsula',
  'Region X - Northern Mindanao',
  'Region XI - Davao Region',
  'Region XII - SOCCSKSARGEN',
  'Region XIII - Caraga',
  'BARMM - Bangsamoro Autonomous Region in Muslim Mindanao',
]

const docFields = [
  { key: 'pds',        label: 'Personal Data Sheet (PDS) with Work Experience Sheet', required: true, full: true,
    note: 'CS Form No. 212 (Revised 2025)' },
  { key: 'app_letter', label: 'Application Letter', required: true },
  { key: 'ipcr',       label: 'IPCR', required: false },
  { key: 'coe',        label: 'Certificate of Eligibility', required: true },
  { key: 'tor',        label: 'Authenticated Transcript of Records', required: true },
]

// ─── Modal state ─────────────────────────────────────────────────────────────

const expModal      = reactive({ open: false, saving: false, error: '', editingId: null })
const eduModal      = reactive({ open: false, saving: false, error: '', editingId: null })
const trainingModal = reactive({ open: false, saving: false, error: '', editingId: null })

const expForm = reactive({
  position_title: '', department_agency: '',
  salary_grade: '', appointment_status: '', government_service: false,
  date_from: '', date_to: '', is_present: false,
})

const eduForm = reactive({
  level: '', school_name: '', degree_course: '',
  period_from: '', period_to: '', period_to_present: false,
  units_earned: '', year_graduated: '', honors: '',
})

const trainingForm = reactive({
  title: '', date_from: '', date_to: '', hours: '', ld_type: '', conducted_by: '',
})

// ─── Lifecycle ───────────────────────────────────────────────────────────────

onMounted(async () => {
  // Allow Apply.vue Edit → links to open a specific tab via ?tab=
  const tabParam = new URLSearchParams(window.location.search).get('tab')
  if (tabParam && ['personal', 'qualifications', 'documents'].includes(tabParam)) {
    activeTab.value = tabParam
  }

  try {
    const { data } = await profileApi.show()
    if (data.profile) {
      const p = data.profile
      Object.keys(personal).forEach(k => { if (p[k] !== null && p[k] !== undefined) personal[k] = p[k] })
      // Laravel's 'date' cast serializes as ISO 8601 (e.g. "1990-05-15T00:00:00.000000Z"),
      // but <input type="date"> only accepts YYYY-MM-DD — truncate to the date portion.
      if (personal.birthday) personal.birthday = String(personal.birthday).substring(0, 10)
      if (p.photo_path) photoPath.value = p.photo_path
      experiences.value = p.work_experiences ?? []
      education.value   = p.educational_attainments ?? []
      trainings.value   = p.trainings ?? []
      // Track existing file paths
      ;['pds', 'app_letter', 'ipcr', 'coe', 'tor'].forEach(k => {
        if (p[k + '_path']) docPaths[k] = p[k + '_path']
      })
    }
    isComplete.value = data.is_complete
  } catch {
    // 401 → api.js redirects to /login
  }
})

// ─── Personal info ───────────────────────────────────────────────────────────

async function savePersonal() {
  savingPersonal.value = true
  try {
    const { data } = await profileApi.update({ ...personal })
    isComplete.value = data.is_complete
    showSavedToast()
  } catch (e) {
    alert(e.response?.data?.message ?? 'Failed to save. Please try again.')
  } finally {
    savingPersonal.value = false
  }
}

function showSavedToast() {
  savedPersonal.value = true
  setTimeout(() => { savedPersonal.value = false }, 2500)
}

// ─── Documents ───────────────────────────────────────────────────────────────

function handleFileSelect(event, key) {
  const file = event.target.files[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    alert('File exceeds the 5 MB limit.')
    event.target.value = ''
    return
  }
  docFiles[key] = file
}

async function uploadDocuments() {
  const fd = new FormData()
  let hasFile = false

  ;['pds', 'app_letter', 'ipcr', 'coe', 'tor'].forEach(k => {
    if (docFiles[k]) { fd.append(k, docFiles[k]); hasFile = true }
  })

  if (!hasFile) { alert('Please select at least one file to upload.'); return }

  savingDocs.value = true
  try {
    const { data } = await profileApi.uploadDocuments(fd)
    // Update path refs
    ;['pds', 'app_letter', 'ipcr', 'coe', 'tor'].forEach(k => {
      if (data.profile[k + '_path']) { docPaths[k] = data.profile[k + '_path']; delete docFiles[k] }
    })
    isComplete.value = data.is_complete
    showSavedToast()
  } catch (e) {
    alert(e.response?.data?.message ?? 'Upload failed. Please try again.')
  } finally {
    savingDocs.value = false
  }
}

// ─── Experience ───────────────────────────────────────────────────────────────

function openExpModal(exp = null) {
  Object.assign(expForm, {
    position_title: exp?.position_title ?? '', department_agency: exp?.department_agency ?? '',
    salary_grade: exp?.salary_grade ?? '', appointment_status: exp?.appointment_status ?? '',
    government_service: exp?.government_service ?? false,
    date_from: exp?.date_from ?? '', date_to: exp?.date_to ?? '', is_present: exp?.is_present ?? false,
  })
  expModal.editingId = exp?.id ?? null
  expModal.error     = ''
  expModal.open      = true
}

async function saveExperience() {
  if (!expForm.position_title || !expForm.department_agency || !expForm.date_from) {
    expModal.error = 'Position title, agency, and date from are required.'
    return
  }
  expModal.saving = true
  expModal.error  = ''
  try {
    if (expModal.editingId) {
      const { data } = await profileApi.updateExperience(expModal.editingId, { ...expForm })
      const idx = experiences.value.findIndex(e => e.id === expModal.editingId)
      if (idx >= 0) experiences.value[idx] = data
    } else {
      const { data } = await profileApi.addExperience({ ...expForm })
      experiences.value.push(data)
    }
    expModal.open = false
  } catch (e) {
    expModal.error = e.response?.data?.message ?? 'Failed to save.'
  } finally {
    expModal.saving = false
  }
}

async function deleteExperience(id) {
  if (!confirm('Remove this work experience?')) return
  await profileApi.deleteExperience(id)
  experiences.value = experiences.value.filter(e => e.id !== id)
}

// ─── Education ────────────────────────────────────────────────────────────────

function openEduModal(edu = null) {
  const isPresent = edu?.period_to === 'Present'
  Object.assign(eduForm, {
    level: edu?.level ?? '', school_name: edu?.school_name ?? '',
    degree_course: edu?.degree_course ?? '',
    period_from: edu?.period_from ?? '',
    period_to: isPresent ? '' : (edu?.period_to ?? ''),
    period_to_present: isPresent,
    units_earned: edu?.units_earned ?? '', year_graduated: edu?.year_graduated ?? '',
    honors: edu?.honors ?? '',
  })
  eduModal.editingId = edu?.id ?? null
  eduModal.error     = ''
  eduModal.open      = true
}

async function saveEducation() {
  if (!eduForm.level || !eduForm.school_name) {
    eduModal.error = 'Level and school name are required.'
    return
  }
  eduModal.saving = true
  eduModal.error  = ''
  try {
    const payload = { ...eduForm }
    if (payload.period_to_present) payload.period_to = 'Present'
    delete payload.period_to_present
    if (eduModal.editingId) {
      const { data } = await profileApi.updateEducation(eduModal.editingId, payload)
      const idx = education.value.findIndex(e => e.id === eduModal.editingId)
      if (idx >= 0) education.value[idx] = data
    } else {
      const { data } = await profileApi.addEducation(payload)
      education.value.push(data)
    }
    eduModal.open = false
  } catch (e) {
    eduModal.error = e.response?.data?.message ?? 'Failed to save.'
  } finally {
    eduModal.saving = false
  }
}

async function deleteEducation(id) {
  if (!confirm('Remove this education record?')) return
  await profileApi.deleteEducation(id)
  education.value = education.value.filter(e => e.id !== id)
}

// ─── Training ─────────────────────────────────────────────────────────────────

function openTrainingModal(t = null) {
  Object.assign(trainingForm, {
    title: t?.title ?? '', date_from: t?.date_from ?? '', date_to: t?.date_to ?? '',
    hours: t?.hours ?? '', ld_type: t?.ld_type ?? '', conducted_by: t?.conducted_by ?? '',
  })
  trainingModal.editingId = t?.id ?? null
  trainingModal.error     = ''
  trainingModal.open      = true
}

async function saveTraining() {
  if (!trainingForm.title || !trainingForm.date_from) {
    trainingModal.error = 'Title and date from are required.'
    return
  }
  trainingModal.saving = true
  trainingModal.error  = ''
  try {
    if (trainingModal.editingId) {
      const { data } = await profileApi.updateTraining(trainingModal.editingId, { ...trainingForm })
      const idx = trainings.value.findIndex(t => t.id === trainingModal.editingId)
      if (idx >= 0) trainings.value[idx] = data
    } else {
      const { data } = await profileApi.addTraining({ ...trainingForm })
      trainings.value.push(data)
    }
    trainingModal.open = false
  } catch (e) {
    trainingModal.error = e.response?.data?.message ?? 'Failed to save.'
  } finally {
    trainingModal.saving = false
  }
}

async function deleteTraining(id) {
  if (!confirm('Remove this training record?')) return
  await profileApi.deleteTraining(id)
  trainings.value = trainings.value.filter(t => t.id !== id)
}

// ─── Utilities ────────────────────────────────────────────────────────────────

// ─── Photo ────────────────────────────────────────────────────────────────────

function openPhotoModal() {
  photoModal.open  = true
  photoModal.step  = 'select'
  photoModal.error = ''
  photoModal.imgSrc = ''
  destroyCropper()
}

function closePhotoModal() {
  photoModal.open = false
  destroyCropper()
}

function destroyCropper() {
  if (cropperInstance) {
    cropperInstance.destroy()
    cropperInstance = null
  }
}

async function onPhotoFileSelect(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file) return

  if (!file.type.startsWith('image/')) {
    photoModal.error = 'Please select a JPG or PNG image.'
    return
  }
  if (file.size > 3 * 1024 * 1024) {
    photoModal.error = 'Image must be under 3 MB.'
    return
  }

  const reader = new FileReader()
  reader.onload = async (ev) => {
    photoModal.imgSrc = ev.target.result
    photoModal.step   = 'crop'
    photoModal.error  = ''
    destroyCropper()
    await nextTick()
    if (cropperImgRef.value) {
      cropperInstance = new Cropper(cropperImgRef.value, {
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 0.85,
        guides: true,
        center: true,
        highlight: false,
        background: true,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
      })
    }
  }
  reader.readAsDataURL(file)
}

async function savePhoto() {
  if (!cropperInstance) return
  photoModal.saving = true
  photoModal.error  = ''

  try {
    const canvas = cropperInstance.getCroppedCanvas({
      maxWidth: 800,
      maxHeight: 800,
      imageSmoothingEnabled: true,
      imageSmoothingQuality: 'high',
    })

    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.92))
    const fd = new FormData()
    fd.append('photo', blob, 'profile-photo.jpg')

    const { data } = await profileApi.uploadPhoto(fd)
    photoPath.value = data.photo_path
    photoModal.open = false
    destroyCropper()
    showSavedToast()
  } catch (e) {
    photoModal.error = e.response?.data?.message ?? 'Upload failed. Please try again.'
  } finally {
    photoModal.saving = false
  }
}

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

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
