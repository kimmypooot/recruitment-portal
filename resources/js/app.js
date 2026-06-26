import { createApp, h, defineComponent, Transition } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import axios from 'axios'
import DataPrivacyModal from './Components/DataPrivacyModal.vue'
import ToastContainer from './Components/UI/ToastContainer.vue'
import ConfirmDialog from './Components/UI/ConfirmDialog.vue'

// Rewrite root-relative axios URLs to absolute URLs using window.location.origin
// so calls work regardless of whether Laravel is served at a subdirectory
// (XAMPP) or at root (php artisan serve).
const origin = window.location.origin
axios.interceptors.request.use((config) => {
  if (config.url?.startsWith('/')) {
    config.url = `${origin}${config.url}`
  }
  return config
})

const pinia = createPinia()

createInertiaApp({
  title: (title) => `${title} — CSC RO VIII - Recruitment Portal`,

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
        h(ToastContainer),
        h(ConfirmDialog),
      ],
    })

    createApp(Root)
      .use(plugin)
      .use(pinia)
      .mount(el)
  },
})