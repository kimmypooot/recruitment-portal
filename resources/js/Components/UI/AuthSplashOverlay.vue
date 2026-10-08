<template>
  <ImisSplash :visible="visible" :title="title" :subtitle="subtitle" :note="note" :instant="instant" />
</template>

<script setup>
// Shared full-screen "signing you in / out" splash used by Login and the
// Applicant/Admin/HRMPSB/Appointing Authority layouts' sign-out flow. It is
// the IMIS transition splash (ImisSplash), so every system under IMIS shows
// the same screen.
import ImisSplash from '@/Components/UI/ImisSplash.vue'

defineProps({
  visible: { type: Boolean, default: false },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  note: { type: String, default: '' },
  instant: { type: Boolean, default: false },
})
</script>
