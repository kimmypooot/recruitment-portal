import { reactive } from 'vue'

const state = reactive({
  show: false,
  title: '',
  message: '',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  variant: 'danger',
  iconType: 'warning',
  alert: false,
  loading: false,
  resolve: null,
})

export function useConfirm() {
  function confirm(message, options = {}) {
    state.message = message
    state.title = options.title ?? 'Are you sure?'
    state.confirmText = options.confirmText ?? 'Confirm'
    state.cancelText = options.cancelText ?? 'Cancel'
    state.variant = options.variant ?? 'danger'
    state.iconType = options.iconType ?? 'warning'
    state.alert = false
    state.show = true
    state.loading = false

    return new Promise((resolve) => {
      state.resolve = resolve
    })
  }

  function alert(message, options = {}) {
    state.message = message
    state.title = options.title ?? 'Notice'
    state.confirmText = options.confirmText ?? 'OK'
    state.cancelText = ''
    state.variant = options.variant ?? 'primary'
    state.iconType = options.iconType ?? 'info'
    state.alert = true
    state.show = true
    state.loading = false

    return new Promise((resolve) => {
      state.resolve = resolve
    })
  }

  function onConfirm() {
    state.show = false
    if (state.resolve) state.resolve(true)
  }

  function onCancel() {
    state.show = false
    if (state.resolve) state.resolve(false)
  }

  function setLoading(val) {
    state.loading = val
  }

  return { state, confirm, alert, onConfirm, onCancel, setLoading }
}
