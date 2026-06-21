// resources/js/stores/auth.js
import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  const page = usePage();

  // The authenticated user from Inertia shared data
  const user = computed(() => page.props.auth.user);
  const roles = computed(() => user.value?.roles ?? []);
  const permissions = computed(() => user.value?.permissions ?? []);

  function hasRole(role) {
    return roles.value.includes(role);
  }
  function can(permission) {
    return permissions.value.includes(permission);
  }

  return { user, roles, permissions, hasRole, can };
});

// Usage in any Vue component:
// const auth = useAuthStore();
// if (auth.can('manage-vacancies')) { ... }
// if (auth.hasRole('hr-manager')) { ... }
