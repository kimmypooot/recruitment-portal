<template>
  <component
    :is="tag"
    v-bind="linkProps"
    :type="tag === 'button' ? type : undefined"
    :disabled="tag === 'button' ? (disabled || loading) : undefined"
    :aria-disabled="disabled || loading ? 'true' : undefined"
    :class="classes"
  >
    <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
    </svg>
    <slot v-else name="icon" />
    <span v-if="$slots.default"><slot /></span>
  </component>
</template>

<script setup>
/**
 * Shared button primitive covering the variant/size combinations already
 * hand-rolled across Auth, Applicant, Admin and Hrmpsb pages (see
 * Login.vue / CompleteProfile.vue / Error.vue for the source patterns).
 *
 * Renders as an Inertia <Link> when `href` is given (for navigation that
 * should feel like a button), otherwise a native <button>.
 */
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  variant: { type: String, default: 'primary' }, // primary | secondary | danger | ghost
  size: { type: String, default: 'md' }, // sm | md | lg
  type: { type: String, default: 'button' }, // button | submit | reset (ignored when href is set)
  href: { type: String, default: null },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  block: { type: Boolean, default: false },
})

// Inertia's <Link> has no native disabled state — a disabled/loading link
// button renders as a plain <span> instead so it can't still be navigated.
const tag = computed(() => {
  if (!props.href) return 'button'
  return props.disabled || props.loading ? 'span' : Link
})
const linkProps = computed(() => (tag.value === Link ? { href: props.href } : {}))

const sizeClasses = {
  sm: 'px-3.5 py-1.5 text-xs gap-1.5',
  md: 'px-6 py-2.5 text-sm gap-2',
  lg: 'px-8 py-3 text-base gap-2.5',
}

const variantClasses = {
  primary: 'bg-primary hover:bg-primary-dark text-white shadow-sm focus-visible:ring-primary',
  secondary: 'border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus-visible:ring-gray-400',
  danger: 'bg-accent hover:bg-accent-dark text-white shadow-sm focus-visible:ring-accent',
  ghost: 'text-primary bg-transparent hover:bg-primary-light focus-visible:ring-primary',
}

const classes = computed(() => [
  'inline-flex items-center justify-center font-semibold rounded-lg transition-colors',
  'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
  'disabled:opacity-60 disabled:cursor-not-allowed',
  sizeClasses[props.size] ?? sizeClasses.md,
  variantClasses[props.variant] ?? variantClasses.primary,
  props.block ? 'w-full' : '',
])
</script>
