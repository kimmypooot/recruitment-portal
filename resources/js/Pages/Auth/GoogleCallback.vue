<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="text-center">
      <div v-if="status === 'processing'" class="space-y-4">
        <svg class="animate-spin h-8 w-8 text-[#2a338f] mx-auto" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <p class="text-gray-600 text-sm">{{ statusText }}</p>
      </div>
      <div v-else-if="status === 'error'" class="space-y-3">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100">
          <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-red-600 text-sm max-w-md">{{ errorMessage }}</p>
        <a :href="`${appUrl}/login`" class="inline-block text-sm text-[#2a338f] hover:underline font-medium">Back to Sign In</a>
      </div>
      <div v-else-if="status === 'link_success'" class="space-y-3">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100">
          <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <p class="text-gray-700 text-sm font-medium">Google account linked successfully!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const appUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') ?? ''

const status = ref('processing')
const statusText = ref('Processing…')
const errorMessage = ref('')

const errorMap = {
  email_exists:        'This email is already registered. Please sign in with your password, then link Google in your Profile settings.',
  auth_failed:         'Google authentication failed. Please try again.',
  link_user_not_found: 'User not found. Please sign in and try again.',
  link_already_taken:  'This Google account is already linked to another user.',
  link_failed:         'Failed to link your Google account. Please try again.',
  already_linked:      'Your account is already linked to a Google account.',
}

onMounted(async () => {
  const params = new URLSearchParams(window.location.search)
  const token = params.get('token')
  const userData = params.get('user')
  const error = params.get('error')
  const linkSuccess = params.get('link_success')

  if (userData) {
    try {
      const user = JSON.parse(atob(userData))
      localStorage.setItem('auth_user', JSON.stringify(user))
    } catch {}
  }

  if (error) {
    status.value = 'error'
    errorMessage.value = errorMap[error] || 'An error occurred during authentication.'
    return
  }

  if (linkSuccess) {
    status.value = 'link_success'
    statusText.value = 'Google account linked!'
    setTimeout(() => { window.location.href = `${appUrl}/applicant/complete-profile` }, 1500)
    return
  }

  if (token) {
    localStorage.setItem('auth_token', token)
    statusText.value = 'Signing you in…'
    const user = JSON.parse(localStorage.getItem('auth_user') ?? '{}')
    if (user.role === 'applicant') {
      try {
        const { data } = await api.get('/profile', {
          headers: { Authorization: `Bearer ${token}` }
        })
        window.location.href = data.is_complete
          ? `${appUrl}/applicant/dashboard`
          : `${appUrl}/applicant/complete-profile`
      } catch {
        window.location.href = `${appUrl}/applicant/dashboard`
      }
    } else {
      window.location.href = `${appUrl}/applicant/dashboard`
    }
    return
  }

  status.value = 'error'
  errorMessage.value = 'No authentication data received. Please try again.'
})
</script>
