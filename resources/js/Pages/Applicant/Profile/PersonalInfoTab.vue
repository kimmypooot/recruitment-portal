<template>
  <div class="space-y-5">

    <!-- Profile Photo + Linked Accounts (side by side) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
          <div class="flex-shrink-0">
            <div class="w-28 h-28 rounded-full border-2 overflow-hidden flex items-center justify-center bg-gray-50"
              :class="photoPath ? 'border-[#2a338f]/30' : 'border-dashed border-gray-300'">
              <img v-if="photoPath" :src="photoUrl" class="w-full h-full object-cover" alt="Profile photo" />
              <svg v-else class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
              </svg>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800">
              {{ photoPath ? 'Photo uploaded' : 'No photo yet' }}
            </p>
            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
              Use a recent photo with a plain background. Face the camera directly, no sunglasses or hats.
            </p>
            <button @click="$emit('open-photo-modal')"
              class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-xs font-semibold rounded-lg transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
              </svg>
              {{ photoPath ? 'Change Photo' : 'Upload Photo' }}
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
          <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">Linked Accounts</p>
            <p class="text-xs text-gray-500 mt-0.5">Connect your Google account for easier sign-in</p>
          </div>
        </div>
        <div class="px-6 py-5">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <svg class="w-8 h-8" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              <div>
                <p class="text-sm font-medium text-gray-900">Google</p>
                <p v-if="hasGoogle" class="text-xs text-gray-500">Connected</p>
                <p v-else class="text-xs text-gray-400">Not connected</p>
              </div>
            </div>
            <button v-if="!hasGoogle" @click="linkGoogle" :disabled="linking"
              class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg transition-colors"
              :class="linking ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-[#2a338f] text-white hover:bg-[#1e2570]'">
              <svg v-if="linking" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ linking ? 'Connecting…' : 'Connect' }}
            </button>
            <button v-else @click="unlinkGoogle" :disabled="unlinking"
              class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition-colors"
              :class="unlinking ? 'opacity-50 cursor-not-allowed' : ''">
              {{ unlinking ? 'Disconnecting…' : 'Disconnect' }}
            </button>
          </div>
          <p v-if="linkError" class="mt-3 text-xs text-red-500">{{ linkError }}</p>
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
          <p class="text-sm font-semibold text-gray-900">Personal Details</p>
          <p class="text-xs text-gray-500 mt-0.5">Gender, civil status, birthday, and religion</p>
        </div>
      </div>
      <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-5">
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
            Gender <span class="text-red-500 normal-case">*</span>
          </label>
          <select v-model="personal.gender" @blur="validate('gender')"
            class="w-full px-3 py-2.5 rounded-lg border text-sm text-gray-900 bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition"
            :class="errors.gender ? 'border-red-400' : 'border-gray-300'">
            <option value="">— Select —</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
          <p v-if="errors.gender" class="mt-1 text-xs text-red-500">{{ errors.gender }}</p>
        </div>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
            Civil Status <span class="text-red-500 normal-case">*</span>
          </label>
          <select v-model="personal.civil_status" @blur="validate('civil_status')"
            class="w-full px-3 py-2.5 rounded-lg border text-sm text-gray-900 bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition"
            :class="errors.civil_status ? 'border-red-400' : 'border-gray-300'">
            <option value="">— Select —</option>
            <option value="Single">Single</option>
            <option value="Married">Married</option>
            <option value="Widowed">Widowed</option>
            <option value="Separated">Separated</option>
            <option value="Annulled">Annulled</option>
          </select>
          <p v-if="errors.civil_status" class="mt-1 text-xs text-red-500">{{ errors.civil_status }}</p>
        </div>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
            Date of Birth <span class="text-red-500 normal-case">*</span>
          </label>
          <input v-model="personal.birthday" type="date" @blur="validate('birthday')"
            class="w-full px-3 py-2.5 rounded-lg border text-sm text-gray-900 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition"
            :class="errors.birthday ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.birthday" class="mt-1 text-xs text-red-500">{{ errors.birthday }}</p>
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
      <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-5">
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
            Region <span class="text-red-500 normal-case">*</span>
          </label>
          <select v-model="personal.region" @blur="validate('region')"
            class="w-full px-3 py-2.5 rounded-lg border text-sm text-gray-900 bg-white focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition"
            :class="errors.region ? 'border-red-400' : 'border-gray-300'">
            <option value="">— Select Region —</option>
            <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
          </select>
          <p v-if="errors.region" class="mt-1 text-xs text-red-500">{{ errors.region }}</p>
        </div>
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
            Province <span class="text-red-500 normal-case">*</span>
          </label>
          <input v-model="personal.province" type="text" placeholder="e.g. Cebu" @blur="validate('province')"
            class="w-full px-3 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition"
            :class="errors.province ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.province" class="mt-1 text-xs text-red-500">{{ errors.province }}</p>
        </div>
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
            City / Municipality <span class="text-red-500 normal-case">*</span>
          </label>
          <input v-model="personal.city_municipality" type="text" placeholder="e.g. Cebu City" @blur="validate('city_municipality')"
            class="w-full px-3 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition"
            :class="errors.city_municipality ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.city_municipality" class="mt-1 text-xs text-red-500">{{ errors.city_municipality }}</p>
        </div>
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
            Barangay <span class="text-red-500 normal-case">*</span>
          </label>
          <input v-model="personal.barangay" type="text" placeholder="e.g. Lahug" @blur="validate('barangay')"
            class="w-full px-3 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition"
            :class="errors.barangay ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.barangay" class="mt-1 text-xs text-red-500">{{ errors.barangay }}</p>
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
          <input v-model="personal.mobile_number" type="tel" placeholder="09XX XXX XXXX" @blur="validate('mobile_number')"
            class="w-full px-3 py-2.5 rounded-lg border text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none transition"
            :class="errors.mobile_number ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="errors.mobile_number" class="mt-1 text-xs text-red-500">{{ errors.mobile_number }}</p>
        </div>
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Email Address</label>
          <input :value="authEmail" type="email" disabled
            class="w-full px-3 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-400 cursor-not-allowed" />
          <p class="text-xs text-gray-400 mt-1.5">Set from your account — cannot be changed here.</p>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  personal:    { type: Object, required: true },
  photoPath:   { type: String, default: '' },
  photoUrl:    { type: String, default: null },
  regions:     { type: Array,  required: true },
  authEmail:   { type: String, default: '' },
  googleId:    { type: String, default: null },
  googleAvatar: { type: String, default: null },
  errors:      { type: Object, default: () => ({}) },
})

defineEmits(['open-photo-modal', 'google-linked', 'google-unlinked'])

const hasGoogle = ref(!!props.googleId)
const linking = ref(false)
const unlinking = ref(false)
const linkError = ref('')

async function linkGoogle() {
  linking.value = true
  linkError.value = ''
  try {
    const { data } = await axios.post('/api/auth/google/link')
    window.location.href = data.redirect_url
  } catch (err) {
    linkError.value = err.response?.data?.message || 'Failed to initiate linking.'
    linking.value = false
  }
}

async function unlinkGoogle() {
  if (!confirm('Disconnect your Google account? You can still sign in with your password.')) return
  unlinking.value = true
  linkError.value = ''
  try {
    await axios.post('/api/auth/google/unlink')
    hasGoogle.value = false
    const user = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
    delete user.google_id
    delete user.google_avatar
    localStorage.setItem('auth_user', JSON.stringify(user))
  } catch (err) {
    linkError.value = err.response?.data?.message || 'Failed to disconnect.'
  } finally {
    unlinking.value = false
  }
}

const fieldLabels = {
  gender: 'Gender',
  civil_status: 'Civil Status',
  birthday: 'Date of Birth',
  region: 'Region',
  province: 'Province',
  city_municipality: 'City/Municipality',
  barangay: 'Barangay',
  mobile_number: 'Mobile Number',
}

function validate(field) {
  if (!props.personal[field]) {
    props.errors[field] = `${fieldLabels[field] ?? field} is required.`
  } else {
    delete props.errors[field]
  }
}
</script>
