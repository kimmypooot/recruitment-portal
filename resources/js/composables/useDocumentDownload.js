import { ref } from 'vue'
import axios from 'axios'

export const docTypes = [
  { key: 'pds',        label: 'PDS',        color: 'bg-blue-500' },
  { key: 'app_letter', label: 'App Letter', color: 'bg-purple-500' },
  { key: 'tor',        label: 'TOR',        color: 'bg-orange-500' },
  { key: 'ipcr',       label: 'IPCR',       color: 'bg-teal-500' },
  { key: 'coe',        label: 'COE',        color: 'bg-green-500' },
]

export const downloadOptions = [
  { value: 'pds',        label: 'Personal Data Sheet x Work Experience', hint: 'PDS with attached work experience records' },
  { value: 'app_letter', label: 'Application Letter',                   hint: null },
  { value: 'ipcr',       label: 'IPCR',                                 hint: 'Individual Performance Commitment Rating' },
  { value: 'coe',        label: 'Certificate of Eligibility',          hint: null },
  { value: 'tor',        label: 'Transcript of Records (TOR)',         hint: null },
  { value: 'all',        label: 'All Requirements',                    hint: 'Download all available documents for each applicant' },
]

const btnClasses = {
  pds:        'text-blue-600 bg-blue-50 hover:bg-blue-100',
  app_letter: 'text-purple-600 bg-purple-50 hover:bg-purple-100',
  tor:        'text-orange-600 bg-orange-50 hover:bg-orange-100',
  ipcr:       'text-teal-600 bg-teal-50 hover:bg-teal-100',
  coe:        'text-green-600 bg-green-50 hover:bg-green-100',
}

export function docBtnClass(key) {
  return btnClasses[key] ?? 'text-green-600 bg-green-50 hover:bg-green-100'
}

export function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

export async function openDoc(url) {
  try {
    const { data, headers } = await axios.get(url, { headers: authHeaders(), responseType: 'blob' })
    const blob = new Blob([data], { type: headers['content-type'] })
    const blobUrl = URL.createObjectURL(blob)
    window.open(blobUrl, '_blank')
    setTimeout(() => URL.revokeObjectURL(blobUrl), 60000)
  } catch {
    // silently fail
  }
}

export function useDownloadModal({ selectedIds, downloadEndpoint }) {
  const showDownloadModal = ref(false)
  const downloadType      = ref('')
  const downloading       = ref(false)
  const downloadError     = ref('')

  function openDownloadModal() {
    downloadType.value = ''
    downloadError.value = ''
    showDownloadModal.value = true
  }

  async function doDownload() {
    if (!downloadType.value || !selectedIds.value.length) return

    downloading.value = true
    downloadError.value = ''

    try {
      const response = await axios.post(
        downloadEndpoint,
        { application_ids: [...selectedIds.value], document_type: downloadType.value },
        { headers: { ...authHeaders() }, responseType: 'blob' }
      )

      const disposition = response.headers['content-disposition'] ?? ''
      const match = disposition.match(/filename=(.+)/)
      const filename = match ? match[1] : 'requirements.zip'

      const blob = new Blob([response.data], { type: 'application/zip' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = filename
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      URL.revokeObjectURL(url)

      showDownloadModal.value = false
    } catch (e) {
      if (e.response?.data instanceof Blob) {
        try {
          const text = await e.response.data.text()
          const json = JSON.parse(text)
          downloadError.value = json.message ?? 'Download failed.'
        } catch {
          downloadError.value = 'Download failed. Please try again.'
        }
      } else {
        downloadError.value = e.response?.data?.message ?? 'Download failed. Please try again.'
      }
    } finally {
      downloading.value = false
    }
  }

  return {
    showDownloadModal,
    downloadType,
    downloading,
    downloadError,
    openDownloadModal,
    doDownload,
  }
}
