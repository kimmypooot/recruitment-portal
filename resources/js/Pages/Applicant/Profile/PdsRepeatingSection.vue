<template>
  <div>
    <div class="flex items-start justify-between gap-3 mb-3 flex-wrap">
      <div class="min-w-0">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ title }}</p>
        <p v-if="description" class="text-xs text-gray-400 mt-0.5">{{ description }}</p>
      </div>
      <button type="button" @click="add" :disabled="atLimit"
        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-dashed border-gray-300 text-xs font-semibold text-gray-600 hover:border-primary hover:text-primary transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
        + {{ addLabel }}
      </button>
    </div>

    <p v-if="!rows.length" class="text-sm text-gray-400 italic">{{ emptyText }}</p>

    <ul v-else class="space-y-4">
      <li v-for="row in rows" :key="row.id"
        class="p-4 rounded-lg border border-gray-200 bg-gray-50/60">
        <div class="grid gap-3" :style="gridStyle">
          <div v-for="field in fields" :key="field.key">
            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
              {{ field.label }}
              <span v-if="field.required" class="text-red-500 normal-case">*</span>
            </label>

            <select v-if="field.options" v-model="row[field.key]" @change="save(row)" :class="inputClass">
              <option v-for="option in field.options" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>

            <input v-else v-model="row[field.key]" :type="field.type || 'text'"
              :step="field.step" :placeholder="field.placeholder"
              @change="save(row)" :class="inputClass" />
          </div>
        </div>

        <div class="mt-3 flex justify-end">
          <button type="button" @click="remove(row)"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors">
            Remove
          </button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { pdsApi } from '@/services/api'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'

const props = defineProps({
  /** API section key: eligibilities, voluntary-works, other-info, … */
  section:     { type: String, required: true },
  title:       { type: String, required: true },
  description: { type: String, default: '' },
  addLabel:    { type: String, default: 'Add row' },
  emptyText:   { type: String, default: 'Nothing recorded yet.' },
  /** [{ key, label, type?, step?, placeholder?, options?, required?, span? }] */
  fields:      { type: Array, required: true },
  items:       { type: Array, default: () => [] },
  max:         { type: Number, default: null },
  /** Values merged into every newly created row. */
  defaults:    { type: Object, default: () => ({}) },
})

const emit = defineEmits(['changed'])

const toast = useToast()
const { confirm } = useConfirm()

const inputClass = 'w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none transition'

const rows = ref([])

/** Date inputs need a bare yyyy-mm-dd, not the ISO timestamp the API returns. */
function normalise(row) {
  const copy = { ...row }
  props.fields.forEach(({ key, type }) => {
    if (type === 'date' && copy[key]) copy[key] = String(copy[key]).substring(0, 10)
    if (copy[key] === null) copy[key] = ''
  })
  return copy
}

watch(() => props.items, (items) => { rows.value = (items ?? []).map(normalise) }, { immediate: true })

const atLimit = computed(() => props.max !== null && rows.value.length >= props.max)

const gridStyle = computed(() => ({
  gridTemplateColumns: `repeat(auto-fit, minmax(${props.fields.length > 4 ? '180px' : '220px'}, 1fr))`,
}))

function payload(row) {
  return props.fields.reduce((data, { key }) => {
    data[key] = row[key] === '' ? null : row[key]
    return data
  }, {})
}

async function add() {
  const required = props.fields.find(f => f.required)

  try {
    const { data } = await pdsApi.addItem(props.section, {
      ...props.defaults,
      // The API requires its mandatory field on create, so seed a placeholder
      // the applicant immediately overwrites.
      ...(required ? { [required.key]: required.placeholderValue ?? 'Untitled' } : {}),
    })
    rows.value.push(normalise(data))
    emit('changed')
  } catch {
    toast.error(`Could not add to ${props.title}.`)
  }
}

async function save(row) {
  const required = props.fields.find(f => f.required)
  if (required && !String(row[required.key] ?? '').trim()) return

  try {
    await pdsApi.updateItem(props.section, row.id, payload(row))
    emit('changed')
  } catch {
    toast.error('Could not save that entry.')
  }
}

async function remove(row) {
  if (! await confirm('Remove this entry?')) return

  try {
    await pdsApi.deleteItem(props.section, row.id)
    rows.value = rows.value.filter(r => r.id !== row.id)
    emit('changed')
  } catch {
    toast.error('Could not remove that entry.')
  }
}
</script>
