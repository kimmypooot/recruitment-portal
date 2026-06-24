import { createApp, h, defineComponent, Transition } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import axios from 'axios'
import DataPrivacyModal from './Components/DataPrivacyModal.vue'

// Rewrite root-relative axios URLs (e.g. '/api/vacancies') to absolute URLs
// using the APP_URL injected by PHP, so calls work regardless of whether
// Laravel is served at a subdirectory (XAMPP) or at root (php artisan serve).
// This only affects the global axios instance; the api.js service uses its
// own axios.create() instance with baseURL already set correctly.
const appUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') ?? ''
axios.interceptors.request.use((config) => {
  if (config.url?.startsWith('/')) {
    config.url = `${appUrl}${config.url}`
  }
  return config
})

const pinia = createPinia()

createInertiaApp({
  title: (title) => `${title} — CSC Recruitment`,

  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    ),

  setup({ el, App, props, plugin }) {
    const Root = defineComponent({
      render: () => [
        h(Transition, { name: 'fade', mode: 'out-in' }, () => h(App, props)),
        h(DataPrivacyModal),
      ],
    })

    createApp(Root)
      .use(plugin)
      .use(pinia)
      .mount(el)
  },
})