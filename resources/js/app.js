import { createApp, h, defineComponent, Transition } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import axios from 'axios'
import ToastContainer from './Components/UI/ToastContainer.vue'
import ConfirmDialog from './Components/UI/ConfirmDialog.vue'
import ProgressBar from './Components/UI/ProgressBar.vue'
import { useToast } from '@/composables/useToast'
import { useSessionExpiry } from '@/composables/useSessionExpiry'
import { useIdleTimer } from '@/composables/useIdleTimer'

const origin = window.location.origin
axios.interceptors.request.use((config) => {
  if (config.url?.startsWith('/')) {
    config.url = `${origin}${config.url}`
  }
  return config
})

const pinia = createPinia()

createInertiaApp({
  title: (title) => title ? `${title} — CSC RO VIII - Recruitment Portal` : 'CSC RO VIII - Recruitment Portal',

  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    ),

  setup({ el, App, props, plugin }) {
    const Root = defineComponent({
      setup() {
        useSessionExpiry()
        useIdleTimer()
      },
      render: () => [
        h(ProgressBar),
        h(Transition, { name: 'fade', mode: 'out-in' }, () => h(App, props)),
        h(ToastContainer),
        h(ConfirmDialog),
      ],
    })

    const app = createApp(Root)
      .use(plugin)
      .use(pinia)

    app.config.errorHandler = (err, instance, info) => {
      const toast = useToast()
      toast.error('An unexpected error occurred. Please try again.')
      console.error('Global error:', err)
      console.error('Component:', instance)
      console.error('Info:', info)
    }

    document.addEventListener('inertia:error', (event) => {
      const { status } = event.detail.response
      const toast = useToast()
      if (status === 419) {
        toast.warning('Your session has expired. Please log in again.')
      } else if (status === 403) {
        toast.error('You do not have permission to perform this action.')
      }
    })

    app.mount(el)

    return app
  },
})
