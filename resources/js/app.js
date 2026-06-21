import { createApp, h, defineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import DataPrivacyModal from './Components/DataPrivacyModal.vue'

createInertiaApp({
  title: (title) => `${title} — CSC Recruitment`,

  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    ),

  setup({ el, App, props, plugin }) {
    const Root = defineComponent({
      render: () => [h(App, props), h(DataPrivacyModal)],
    })

    createApp(Root)
      .use(plugin)
      .mount(el)
  },
})