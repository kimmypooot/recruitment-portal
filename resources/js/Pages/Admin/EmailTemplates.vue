<template>
  <AdminLayout title="Email Templates">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Email Templates</h1>
          <p class="text-sm text-gray-500 mt-1">
            Manage the wording of emails sent to applicants throughout the recruitment pipeline.
          </p>
        </div>
        <div class="w-full sm:w-64">
          <select v-model="categoryFilter" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none">
            <option value="">All categories</option>
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>
      </div>

      <!-- Error -->
      <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
        {{ error }}
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="n in 6" :key="n" class="h-16 bg-gray-100 rounded-xl animate-pulse"></div>
      </div>

      <!-- Grouped list -->
      <div v-else class="space-y-6">
        <div v-for="cat in categories.filter(c => !categoryFilter || c === categoryFilter)" :key="cat">
          <div v-if="byCategory[cat]?.length">
            <div class="flex items-center gap-3 mb-3">
              <span class="text-xs font-bold uppercase tracking-widest px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700">
                {{ cat }}
              </span>
              <div class="flex-1 h-px bg-gray-100"></div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-50 mb-2">
              <div v-for="tpl in byCategory[cat]" :key="tpl.id"
                class="flex items-start gap-4 px-5 py-4 group hover:bg-gray-50/60 transition-colors">

                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-semibold text-gray-900">{{ tpl.name }}</p>
                    <span v-if="!tpl.is_active" class="text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded bg-gray-100 text-gray-400">Inactive</span>
                  </div>
                  <p class="text-xs text-gray-400 mt-0.5 leading-snug truncate">{{ tpl.subject }}</p>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                  <button @click="openPreview(tpl)"
                    class="px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:text-[#2a338f] hover:bg-indigo-50 rounded-lg transition-colors">
                    Preview
                  </button>
                  <button @click="openEdit(tpl)"
                    class="px-2.5 py-1.5 text-xs font-medium text-white bg-[#2a338f] hover:bg-[#1e2570] rounded-lg transition-colors">
                    Edit
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Edit modal ─────────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="modal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" @click.self="closeModal">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>

          <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div>
              <h2 class="text-base font-bold text-gray-900">{{ modal.template?.name }}</h2>
              <p class="text-xs text-gray-400 mt-0.5">{{ modal.template?.category }}</p>
            </div>
            <button @click="closeModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
              <Icon name="xmark" class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="saveTemplate" class="px-6 py-5 space-y-4 overflow-y-auto">

            <div v-if="modal.template?.placeholders?.length" class="p-3 bg-indigo-50 border border-indigo-100 rounded-lg">
              <p class="text-xs font-semibold text-indigo-900 mb-1.5">Available placeholders</p>
              <div class="flex flex-wrap gap-1.5">
                <code v-for="p in modal.template.placeholders" :key="p"
                  class="text-[11px] font-mono bg-white text-indigo-700 border border-indigo-200 px-1.5 py-0.5 rounded">
                  {{ wrapPlaceholder(p) }}
                </code>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Subject <span class="text-red-500">*</span></label>
              <input v-model="form.subject" type="text" required
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" :class="{ 'border-red-300': formErrors.subject }"/>
              <p v-if="formErrors.subject" class="text-xs text-red-600 mt-1">{{ formErrors.subject[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Greeting <span class="text-red-500">*</span></label>
              <input v-model="form.greeting" type="text" required
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none" :class="{ 'border-red-300': formErrors.greeting }"/>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Body <span class="text-red-500">*</span></label>
              <textarea v-model="form.body" rows="8" required
                class="w-full px-3 py-2 text-sm font-mono border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none"></textarea>
              <p class="text-[11px] text-gray-400 mt-1">Each line becomes a paragraph. Use **text** for bold. Empty placeholder lines are omitted automatically.</p>
              <p v-if="formErrors.body" class="text-xs text-red-600 mt-1">{{ formErrors.body[0] }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Button Text</label>
                <input v-model="form.action_text" type="text" placeholder="e.g. View Your Application"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none"/>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Button Link</label>
                <input v-if="!modal.template?.action_locked" v-model="form.action_url" type="text" placeholder="/applicant/applications"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none"/>
                <p v-else class="text-xs text-gray-400 mt-2.5 italic">Auto-generated per request — not editable.</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Footer</label>
              <input v-model="form.footer" type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none"/>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-[#2a338f] focus:ring-[#2a338f]"/>
              <span class="text-sm text-gray-700">Active — send this version of the email</span>
            </label>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 mt-4">
              <button type="button" @click="closeModal" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
              <button type="submit" :disabled="modal.saving" class="inline-flex items-center px-4 py-2 bg-[#2a338f] text-white text-sm font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-50 transition-colors">
                {{ modal.saving ? 'Saving…' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Preview modal ──────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="preview.open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" @click.self="preview.open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col" @click.stop>
          <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Preview</h2>
            <button @click="preview.open = false" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
              <Icon name="xmark" class="w-5 h-5" />
            </button>
          </div>
          <div class="px-6 py-5 overflow-y-auto">
            <div v-if="preview.loading" class="py-10 text-center text-sm text-gray-400">Loading…</div>
            <div v-else class="border border-gray-200 rounded-xl p-5 bg-gray-50">
              <p class="text-xs text-gray-400 mb-1">Subject</p>
              <p class="text-sm font-semibold text-gray-900 mb-4">{{ preview.data?.subject }}</p>
              <p class="text-sm text-gray-800 mb-3">{{ preview.data?.greeting }}</p>
              <p v-for="(line, i) in previewLines" :key="i" class="text-sm text-gray-700 mb-2 leading-relaxed" v-html="formatLine(line)"></p>
              <a v-if="preview.data?.action_text" href="#" @click.prevent
                class="inline-block mt-2 px-4 py-2 bg-[#2a338f] text-white text-sm font-semibold rounded-lg">
                {{ preview.data.action_text }}
              </a>
              <p class="text-xs text-gray-400 mt-5 pt-3 border-t border-gray-200">{{ preview.data?.footer }}</p>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Icon from '@/Components/UI/Icon.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

function authHeaders() {
  const t = localStorage.getItem('auth_token')
  return t ? { Authorization: `Bearer ${t}` } : {}
}

const loading   = ref(true)
const error     = ref(null)
const templates = ref([])
const categoryFilter = ref('')

const categories = computed(() => {
  const seen = []
  for (const t of templates.value) {
    if (!seen.includes(t.category)) seen.push(t.category)
  }
  return seen
})

const byCategory = computed(() => {
  const map = {}
  for (const c of categories.value) {
    map[c] = templates.value.filter(t => t.category === c)
  }
  return map
})

async function load() {
  loading.value = true
  error.value   = null
  try {
    const { data } = await axios.get('/api/admin/email-templates', { headers: authHeaders() })
    templates.value = data.data ?? []
  } catch (e) {
    error.value = e?.response?.data?.message ?? 'Failed to load email templates.'
  } finally {
    loading.value = false
  }
}

// ── Edit modal ────────────────────────────────────────────────────────────
const modal = reactive({ open: false, template: null, saving: false })
const form  = reactive({ subject: '', greeting: '', body: '', action_text: '', action_url: '', footer: '', is_active: true })
const formErrors = ref({})

function openEdit(tpl) {
  modal.template = tpl
  form.subject     = tpl.subject
  form.greeting    = tpl.greeting
  form.body        = tpl.body
  form.action_text = tpl.action_text ?? ''
  form.action_url  = tpl.action_url ?? ''
  form.footer      = tpl.footer
  form.is_active   = tpl.is_active
  formErrors.value = {}
  modal.open = true
}

function closeModal() {
  modal.open = false
}

async function saveTemplate() {
  modal.saving = true
  formErrors.value = {}
  try {
    const payload = {
      subject: form.subject,
      greeting: form.greeting,
      body: form.body,
      action_text: form.action_text || null,
      action_url: modal.template.action_locked ? undefined : (form.action_url || null),
      footer: form.footer,
      is_active: form.is_active,
    }
    const { data } = await axios.put(`/api/admin/email-templates/${modal.template.id}`, payload, { headers: authHeaders() })
    const idx = templates.value.findIndex(t => t.id === modal.template.id)
    if (idx !== -1) templates.value[idx] = data.data
    modal.open = false
    toast.success('Email template updated.')
  } catch (e) {
    formErrors.value = e?.response?.data?.errors ?? {}
    if (!Object.keys(formErrors.value).length) {
      toast.error(e?.response?.data?.message ?? 'Save failed.')
    }
  } finally {
    modal.saving = false
  }
}

// ── Preview ───────────────────────────────────────────────────────────────
const preview = reactive({ open: false, loading: false, data: null })
const previewLines = computed(() => (preview.data?.body ?? '').split(/\r?\n/).filter(l => l.trim() !== ''))

function formatLine(line) {
  return line.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
}

function wrapPlaceholder(name) {
  return `{{${name}}}`
}

async function openPreview(tpl) {
  preview.open = true
  preview.loading = true
  preview.data = null
  try {
    const { data } = await axios.get(`/api/admin/email-templates/${tpl.id}/preview`, { headers: authHeaders() })
    preview.data = data.data
  } catch (e) {
    toast.error(e?.response?.data?.message ?? 'Failed to load preview.')
    preview.open = false
  } finally {
    preview.loading = false
  }
}

onMounted(load)
</script>
