import { createApp, h, defineComponent, Transition } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import DataPrivacyModal from './Components/DataPrivacyModal.vue'

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