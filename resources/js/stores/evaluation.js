import { defineStore } from 'pinia'
import { ref } from 'vue'

/**
 * Draft state for evaluation forms.
 * Persists unsaved QS / BEI entries to localStorage so a page refresh
 * does not lose in-progress ratings.
 */
export const useEvaluationStore = defineStore('evaluation', () => {
  // { [applicationId]: { education_meets, experience_meets, training_meets, eligibility_meets, remarks } }
  const qsDrafts = ref(loadFromStorage('qs_drafts'))

  // { [applicationId]: { competency_scores: {...}, remarks } }
  const beiDrafts = ref(loadFromStorage('bei_drafts'))

  function saveQsDraft(applicationId, data) {
    qsDrafts.value[applicationId] = { ...data }
    persistToStorage('qs_drafts', qsDrafts.value)
  }

  function getQsDraft(applicationId) {
    return qsDrafts.value[applicationId] ?? null
  }

  function clearQsDraft(applicationId) {
    delete qsDrafts.value[applicationId]
    persistToStorage('qs_drafts', qsDrafts.value)
  }

  function saveBeiDraft(applicationId, data) {
    beiDrafts.value[applicationId] = { ...data }
    persistToStorage('bei_drafts', beiDrafts.value)
  }

  function getBeiDraft(applicationId) {
    return beiDrafts.value[applicationId] ?? null
  }

  function clearBeiDraft(applicationId) {
    delete beiDrafts.value[applicationId]
    persistToStorage('bei_drafts', beiDrafts.value)
  }

  function clearAllDrafts() {
    qsDrafts.value = {}
    beiDrafts.value = {}
    localStorage.removeItem('qs_drafts')
    localStorage.removeItem('bei_drafts')
  }

  return {
    qsDrafts, beiDrafts,
    saveQsDraft, getQsDraft, clearQsDraft,
    saveBeiDraft, getBeiDraft, clearBeiDraft,
    clearAllDrafts,
  }
})

function loadFromStorage(key) {
  try {
    return JSON.parse(localStorage.getItem(key) ?? '{}')
  } catch {
    return {}
  }
}

function persistToStorage(key, data) {
  localStorage.setItem(key, JSON.stringify(data))
}
