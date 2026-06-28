<template>
  <AdminLayout title="Vacancies">

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
      <div class="flex gap-2 flex-wrap">
        <input v-model="filters.search" @input="onSearch" type="text"
          placeholder="Search position..."
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:border-[#2a338f] focus:outline-none w-52" />
        <select v-model="filters.status" @change="fetchVacancies"
          class="px-3 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="closed">Closed</option>
        </select>
      </div>
      <button @click="openCreate"
        class="flex items-center gap-2 px-4 py-2 bg-[#2a338f] hover:bg-[#1e2570] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New Vacancy
      </button>
    </div>

    <!-- Bulk action bar -->
    <div v-if="selectedIds.length" class="flex items-center gap-3 flex-wrap px-5 py-3 bg-[#2a338f]/5 border border-[#2a338f]/20 rounded-lg mb-4">
      <span class="text-sm font-medium text-gray-700">{{ selectedIds.length }} selected</span>
      <div class="flex-1"></div>
      <select v-model="bulkStatus"
        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-[#2a338f] focus:outline-none">
        <option value="">Change status to…</option>
        <option value="draft">Draft</option>
        <option value="published">Published</option>
        <option value="closed">Closed</option>
      </select>
      <button @click="bulkApply" :disabled="!bulkStatus || bulkLoading"
        class="px-4 py-1.5 text-sm font-semibold text-white bg-[#2a338f] hover:bg-[#1e2570] disabled:opacity-50 rounded-lg transition-colors">
        <span v-if="bulkLoading" class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
          Applying…
        </span>
        <span v-else>Apply</span>
      </button>
      <button @click="selectedIds = []"
        class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
        Clear
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-8 space-y-3">
        <div v-for="n in 5" :key="n" class="h-12 bg-gray-100 rounded animate-pulse"></div>
      </div>

      <div v-else class="overflow-x-auto">
      <table class="w-full min-w-[700px] text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider">
            <th class="px-5 py-3 w-10">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll"
                class="w-4 h-4 rounded border-gray-300 text-[#2a338f] focus:ring-[#2a338f]" />
            </th>
            <th class="px-5 py-3">Position</th>
            <th class="px-5 py-3">SG</th>
            <th class="px-5 py-3">Office</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3">Published</th>
            <th class="px-5 py-3">Deadline</th>
            <th class="px-5 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="v in vacancies" :key="v.id" class="hover:bg-gray-50 transition-colors"
            :class="selectedIds.includes(v.id) ? 'bg-[#2a338f]/5' : ''">
            <td class="px-5 py-3.5">
              <input type="checkbox" :checked="selectedIds.includes(v.id)" @change="toggleSelect(v.id)"
                class="w-4 h-4 rounded border-gray-300 text-[#2a338f] focus:ring-[#2a338f]" />
            </td>
            <td class="px-5 py-3.5 font-medium text-gray-900">{{ v.position_title }}</td>
            <td class="px-5 py-3.5 text-gray-600">SG-{{ v.salary_grade }}</td>
            <td class="px-5 py-3.5 text-gray-600 max-w-[160px] truncate">{{ v.place_of_assignment }}</td>
            <td class="px-5 py-3.5"><StatusBadge :status="v.status" /></td>
            <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">{{ formatDate(v.published_at) }}</td>
            <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">{{ formatDate(v.deadline_at) }}</td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-2">
                <button @click="openEdit(v)"
                  class="px-2.5 py-1 text-xs font-medium text-[#2a338f] bg-[#2a338f]/10 hover:bg-[#2a338f]/20 rounded-md transition-colors">
                  Edit
                </button>
                <Link :href="`/vacancies/${v.id}/apply`" target="_blank"
                  class="px-2.5 py-1 text-xs font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-md transition-colors inline-flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                  </svg>
                  Preview
                </Link>
                <button v-if="v.status === 'draft'" @click="changeStatus(v, 'publish')" :disabled="statusLoading === v.id"
                  class="px-2.5 py-1 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-md transition-colors disabled:opacity-60">
                  <span v-if="statusLoading === v.id" class="flex items-center gap-1">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Publishing…
                  </span>
                  <span v-else>Publish</span>
                </button>
                <button v-if="v.status === 'published'" @click="changeStatus(v, 'archive')" :disabled="statusLoading === v.id"
                  class="px-2.5 py-1 text-xs font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 rounded-md transition-colors disabled:opacity-60">
                  <span v-if="statusLoading === v.id" class="flex items-center gap-1">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Archiving…
                  </span>
                  <span v-else>Archive</span>
                </button>
                <button @click="confirmDelete(v)"
                  class="px-2.5 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!vacancies.length">
            <td colspan="9" class="px-5 py-16 text-center">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm font-medium text-gray-400">No vacancies found</p>
                <button v-if="filters.search || filters.status" @click="filters.search = ''; filters.status = ''; fetchVacancies()"
                  class="text-xs text-[#2a338f] hover:underline">Clear filters</button>
                <button v-else @click="openCreate"
                  class="text-xs text-[#2a338f] hover:underline">Create your first vacancy</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span class="text-xs">
          Showing <strong class="text-gray-700">{{ meta.from }}</strong>–<strong class="text-gray-700">{{ meta.to }}</strong>
          of <strong class="text-gray-700">{{ meta.total }}</strong>
        </span>
        <div class="flex items-center gap-1">
          <button :disabled="meta.current_page === 1" @click="goPage(meta.current_page - 1)"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
          </button>
          <button v-for="p in visibleVacancyPages" :key="p" @click="typeof p === 'number' && goPage(p)"
            :disabled="p === '…'"
            :class="['px-2.5 py-1 rounded-lg text-xs font-medium transition-colors',
              p === meta.current_page ? 'bg-[#2a338f] text-white' : p === '…' ? 'text-gray-300 cursor-default' : 'text-gray-600 hover:bg-gray-100']">
            {{ p }}
          </button>
          <button :disabled="meta.current_page === meta.last_page" @click="goPage(meta.current_page + 1)"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- ── Create / Edit Modal ──────────────────────────────────────────────── -->
    <Teleport to="body">
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="showModal = false"></div>

      <!-- Modal shell: fixed height so tabs scroll, not the modal itself -->
      <form @submit.prevent="submitVacancy"
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col"
        style="max-height: 88vh;">

        <!-- ── Header ── -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
          <div>
            <h3 class="text-base font-semibold text-gray-900">
              {{ editingId ? 'Edit Vacancy' : 'New Vacancy' }}
            </h3>
            <p v-if="form.position_title" class="text-xs text-gray-400 mt-0.5 truncate max-w-sm">
              {{ form.position_title }}
            </p>
          </div>
          <button type="button" @click="showModal = false" aria-label="Close modal"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- ── Tab bar ── -->
        <div class="flex border-b border-gray-100 px-6 flex-shrink-0 bg-gray-50/50">
          <button v-for="tab in activeTabs" :key="tab.id" type="button" @click="modalTab = tab.id"
            class="relative px-4 py-3 text-sm font-medium transition-colors -mb-px"
            :class="modalTab === tab.id
              ? 'text-[#2a338f] border-b-2 border-[#2a338f]'
              : 'text-gray-500 hover:text-gray-800'">
            <span class="flex items-center gap-1.5">
              {{ tab.label }}
              <span v-if="tab.id === 'competencies' && draftAssignments.length"
                class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-[#2a338f] text-white text-[9px] font-bold">
                {{ draftAssignments.length }}
              </span>
            </span>
          </button>
        </div>

        <!-- ── Tab content (scrollable) ── -->
        <div class="flex-1 overflow-y-auto">

          <!-- Tab 1: Position Info -->
          <div v-if="modalTab === 'position'" class="p-6">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Position Title <span class="text-red-500">*</span></label>
                <input v-model="form.position_title" required type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plantilla Item No. <span class="text-red-500">*</span></label>
                <input v-model="form.plantilla_no" required type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Salary Grade <span class="text-red-500">*</span></label>
                <select v-model="form.salary_grade" required
                  class="w-full px-3 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none bg-white">
                  <option value="">Select SG</option>
                  <option v-for="n in 33" :key="n" :value="n">SG-{{ n }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Salary (₱)</label>
                <input v-model="form.monthly_salary" type="number" min="0" step="0.01"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Position Level <span class="text-red-500">*</span></label>
                <select v-model="form.position_level" required
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none">
                  <option value="" disabled>Select position level</option>
                  <option value="Supervisory">Supervisory</option>
                  <option value="Technical or Non-Supervisory">Technical or Non-Supervisory</option>
                  <option value="Administrative Support">Administrative Support</option>
                  <option value="Skills, Trades and Craft">Skills, Trades and Craft</option>
                </select>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Place of Assignment <span class="text-red-500">*</span></label>
                <input v-model="form.place_of_assignment" required type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Application Deadline <span class="text-red-500">*</span></label>
                <input v-model="form.deadline_at" required type="date"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
              </div>
              <div class="flex items-center gap-2.5 pt-7">
                <input v-model="form.is_anticipated_vacancy" id="anticipated" type="checkbox"
                  class="w-4 h-4 rounded border-gray-300 text-[#2a338f] focus:ring-[#2a338f]" />
                <label for="anticipated" class="text-sm text-gray-700 cursor-pointer">Anticipated Vacancy</label>
              </div>
            </div>
          </div>

          <!-- Tab 2: Qualification Standards -->
          <div v-else-if="modalTab === 'qualifications'" class="p-6 space-y-4">
            <p class="text-xs text-gray-400">
              Describe the minimum qualification standards required for this position per CSC guidelines.
            </p>
            <div v-for="field in requirementFields" :key="field.key">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ field.label }} <span class="text-red-500">*</span>
              </label>
              <textarea v-model="form[field.key]" required rows="3"
                :placeholder="field.placeholder"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none resize-none"></textarea>
            </div>
          </div>

          <!-- Tab 3: Competencies (edit only) -->
          <div v-else-if="modalTab === 'competencies'" class="grid grid-cols-2 divide-x divide-gray-100" style="min-height: 400px;">

            <!-- Left: master list -->
            <div class="flex flex-col overflow-hidden" style="max-height: 56vh;">
              <div class="p-3 border-b border-gray-100 flex-shrink-0">
                <div class="relative">
                  <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                  <input v-model="compSearch" type="text" placeholder="Search competencies…"
                    class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#2a338f] focus:outline-none" />
                </div>
              </div>
              <div class="p-4 overflow-y-auto flex-1">
              <p class="text-xs text-gray-400 mb-3">Click a competency to toggle it. Assigned ones are highlighted.</p>
              <div v-for="groupName in groupOrder" :key="groupName" class="mb-4"
                v-show="filteredCompetenciesByGroup[groupName]?.length">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ groupName }}</span>
                  <span class="flex-1 h-px bg-gray-100"></span>
                </div>
                <div class="space-y-1">
                  <div v-for="comp in filteredCompetenciesByGroup[groupName]" :key="comp.competency_key"
                    @click="toggleCompetency(comp)"
                    :class="[
                      'flex items-center gap-2.5 px-2.5 py-2 rounded-lg cursor-pointer transition-all text-sm select-none',
                      isAssigned(comp.competency_key)
                        ? 'bg-[#2a338f]/8 border border-[#2a338f]/25 text-gray-900 font-medium'
                        : 'hover:bg-gray-50 border border-transparent text-gray-600'
                    ]">
                    <div :class="isAssigned(comp.competency_key) ? 'bg-[#2a338f]' : 'bg-gray-200'"
                      class="w-4 h-4 rounded flex-shrink-0 flex items-center justify-center transition-colors">
                      <svg v-if="isAssigned(comp.competency_key)" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                    </div>
                    <span class="truncate leading-tight">{{ comp.competency_name }}</span>
                  </div>
                </div>
              </div>
              <p v-if="compSearch && !Object.values(filteredCompetenciesByGroup).some(g => g.length)"
                class="text-xs text-gray-400 text-center py-6">No competencies match "{{ compSearch }}"</p>
              </div>
            </div>

            <!-- Right: assigned list -->
            <div class="flex flex-col p-5">
              <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-800">
                  Assigned
                  <span class="ml-1 text-xs font-normal text-gray-400">({{ draftAssignments.length }})</span>
                </p>
              </div>

              <div v-if="!draftAssignments.length"
                class="flex-1 flex flex-col items-center justify-center text-center py-10">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                  <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                </div>
                <p class="text-sm text-gray-400 font-medium">None assigned yet</p>
                <p class="text-xs text-gray-300 mt-0.5">Select from the list on the left.</p>
              </div>

              <div v-else class="flex-1 overflow-y-auto space-y-1.5" style="max-height: 50vh;">
                <div v-for="item in draftAssignments" :key="item.competency_key"
                  class="group flex items-center gap-2 px-2.5 py-2 rounded-lg border border-gray-100 bg-gray-50/80 hover:bg-white hover:border-gray-200 transition-colors">
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-800 truncate leading-tight">{{ item.competency_name }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ item.competency_group }}</p>
                  </div>
                  <select v-model="item.level"
                    class="text-xs border border-gray-200 rounded-md px-1.5 pr-7 py-1 bg-white text-gray-700 focus:ring-1 focus:ring-[#2a338f] focus:outline-none flex-shrink-0">
                    <option :value="1">Basic</option>
                    <option :value="2">Intermediate</option>
                    <option :value="3">Advanced</option>
                    <option :value="4">Superior</option>
                  </select>
                  <button type="button" @click="removeAssignment(item.competency_key)" aria-label="Remove competency"
                    class="p-1 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors flex-shrink-0 opacity-0 group-hover:opacity-100">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>


            </div>

          </div>

        </div>

        <!-- ── Footer ── -->
        <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl flex-shrink-0">
          <button type="button" @click="showModal = false"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
            {{ modalTab === 'competencies' ? 'Close' : 'Cancel' }}
          </button>

          <!-- Next button (Position Info → Qualifications) -->
          <div class="flex gap-2">
            <button v-if="modalTab !== 'position'" type="button"
              @click="modalTab = modalTab === 'competencies' ? 'qualifications' : 'position'"
              class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
              ← Back
            </button>

            <!-- On Position tab: Next → -->
            <button v-if="modalTab === 'position'" type="button" @click="modalTab = 'qualifications'"
              class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] transition-colors">
              Next: Qualifications →
            </button>

            <!-- On Qualifications tab: Save (or Next if editing) -->
            <template v-else-if="modalTab === 'qualifications'">
              <button type="submit" :disabled="saving"
                class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-60 transition-colors">
                <span v-if="saving" class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  Saving…
                </span>
                <span v-else>{{ editingId ? 'Save Changes' : 'Create Vacancy' }}</span>
              </button>
            </template>

            <!-- On Competencies tab: Save competencies -->
            <button v-else-if="modalTab === 'competencies'" type="button"
              @click="saveCompetencies" :disabled="compSaving"
              class="px-4 py-2 text-sm bg-[#2a338f] text-white font-semibold rounded-lg hover:bg-[#1e2570] disabled:opacity-60 transition-colors">
              <span v-if="compSaving" class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Saving…
              </span>
              <span v-else>Save Competencies</span>
            </button>
          </div>
        </div>

      </form>
    </div>
    </Teleport>

    <!-- Delete confirm modal -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="deleteTarget = null"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-2">Delete Vacancy?</h3>
        <p class="text-sm text-gray-500 mb-6">
          "<strong>{{ deleteTarget.position_title }}</strong>" will be permanently deleted.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="deleteTarget = null"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="doDelete" :disabled="saving"
            class="px-4 py-2 text-sm bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-60">
            {{ saving ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

// ── State ────────────────────────────────────────────────────────────────────────
const loading       = ref(true)
const saving        = ref(false)
const statusLoading = ref(null)
const vacancies     = ref([])
const meta          = ref({})
const showModal     = ref(false)
const editingId     = ref(null)
const deleteTarget  = ref(null)
const modalTab      = ref('position')
const selectedIds   = ref([])
const bulkStatus    = ref('')
const bulkLoading   = ref(false)

const selectAll = computed(() =>
  vacancies.value.length > 0 && selectedIds.value.length === vacancies.value.length
)

const visibleVacancyPages = computed(() => {
  const total = meta.value.last_page ?? 1
  const cur   = meta.value.current_page ?? 1
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  if (cur <= 4)   return [1, 2, 3, 4, 5, '…', total]
  if (cur >= total - 3) return [1, '…', total - 4, total - 3, total - 2, total - 1, total]
  return [1, '…', cur - 1, cur, cur + 1, '…', total]
})

function toggleSelectAll() {
  if (selectAll.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = vacancies.value.map(v => v.id)
  }
}

function toggleSelect(id) {
  const i = selectedIds.value.indexOf(id)
  if (i === -1) {
    selectedIds.value.push(id)
  } else {
    selectedIds.value.splice(i, 1)
  }
}

async function bulkApply() {
  if (!bulkStatus.value || !selectedIds.value.length) return
  bulkLoading.value = true
  try {
    await axios.patch('/api/vacancies/bulk/status', {
      ids: selectedIds.value,
      status: bulkStatus.value,
    }, { headers: authHeaders() })
    toast.success(`Status updated to "${bulkStatus.value}" for ${selectedIds.value.length} vacancy(ies).`)
    selectedIds.value = []
    bulkStatus.value = ''
    fetchVacancies()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Bulk update failed.')
  } finally {
    bulkLoading.value = false
  }
}

const activeTabs = computed(() => {
  const base = [
    { id: 'position',       label: 'Position Info' },
    { id: 'qualifications', label: 'Qualification Standards' },
  ]
  if (editingId.value) base.push({ id: 'competencies', label: 'Competencies' })
  return base
})

const filters = reactive({ search: '', status: '', page: 1 })

const blankForm = {
  position_title: '', plantilla_no: '', salary_grade: '',
  monthly_salary: '', position_level: '', is_anticipated_vacancy: false,
  place_of_assignment: '', deadline_at: '',
  education_req: '', experience_req: '', training_req: '', eligibility_req: '',
}
const form = reactive({ ...blankForm })

const requirementFields = [
  { key: 'education_req',   label: 'Education',   placeholder: 'e.g. Bachelor\'s Degree in any field' },
  { key: 'experience_req',  label: 'Experience',  placeholder: 'e.g. 1 year of relevant experience' },
  { key: 'training_req',    label: 'Training',    placeholder: 'e.g. 4 hours of relevant training' },
  { key: 'eligibility_req', label: 'Eligibility', placeholder: 'e.g. Career Service (Professional) / Second Level Eligibility' },
]

// ── Competency state ─────────────────────────────────────────────────────────────
const groupOrder        = ['Core', 'Organizational', 'Leadership', 'Technical']
const allCompetencies   = ref([])
const draftAssignments  = ref([])
const compSaving        = ref(false)
const compSaveSuccess   = ref(false)
const compSearch        = ref('')

const competenciesByGroup = computed(() => {
  const map = {}
  for (const g of groupOrder) {
    map[g] = allCompetencies.value.filter(c => c.competency_group === g)
  }
  return map
})

const filteredCompetenciesByGroup = computed(() => {
  const q = compSearch.value.toLowerCase().trim()
  if (!q) return competenciesByGroup.value
  const map = {}
  for (const g of groupOrder) {
    map[g] = (competenciesByGroup.value[g] ?? []).filter(c =>
      c.competency_name.toLowerCase().includes(q)
    )
  }
  return map
})

function isAssigned(key) {
  return draftAssignments.value.some(d => d.competency_key === key)
}

function toggleCompetency(comp) {
  if (isAssigned(comp.competency_key)) {
    removeAssignment(comp.competency_key)
  } else {
    draftAssignments.value.push({
      competency_key:   comp.competency_key,
      competency_name:  comp.competency_name,
      competency_group: comp.competency_group,
      level:            1,
    })
  }
}

function removeAssignment(key) {
  draftAssignments.value = draftAssignments.value.filter(d => d.competency_key !== key)
}

async function loadVacancyCompetencies(vacancyId) {
  draftAssignments.value = []
  compSearch.value = ''
  try {
    const { data } = await axios.get(`/api/admin/competencies/vacancy/${vacancyId}`, {
      headers: authHeaders(),
    })
    draftAssignments.value = (data.data ?? []).map(vc => ({
      competency_key:   vc.competency_key,
      competency_name:  vc.competency_name,
      competency_group: vc.competency_group,
      level:            vc.competency_level,
    }))
  } catch (e) {
    console.error('Failed to load competencies', e)
  }
}

async function saveCompetencies() {
  compSaving.value = true
  compSaveSuccess.value = false
  try {
    await axios.post(`/api/admin/competencies/vacancy/${editingId.value}/sync`, {
      competencies: draftAssignments.value.map(d => ({
        competency_key: d.competency_key,
        level: d.level,
      })),
    }, { headers: authHeaders() })
    toast.success('Competencies saved.')
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to save competencies.')
  } finally {
    compSaving.value = false
  }
}

// ── Auth ─────────────────────────────────────────────────────────────────────────
function authHeaders() {
  const token = localStorage.getItem('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

// ── Vacancies CRUD ────────────────────────────────────────────────────────────────
async function fetchVacancies() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/vacancies', {
      params: { search: filters.search || undefined, status: filters.status || undefined, page: filters.page },
      headers: authHeaders(),
    })
    vacancies.value = data.data ?? data
    meta.value      = data.meta ?? {}
  } finally {
    loading.value = false
  }
}

const onSearch = debounce(() => { filters.page = 1; fetchVacancies() }, 350)

function goPage(p) { filters.page = p; fetchVacancies() }

function openCreate() {
  editingId.value = null
  modalTab.value  = 'position'
  Object.assign(form, { ...blankForm })
  showModal.value = true
}

async function openEdit(vacancy) {
  editingId.value = vacancy.id
  modalTab.value  = 'position'
  Object.assign(form, {
    position_title:         vacancy.position_title ?? '',
    plantilla_no:           vacancy.plantilla_no ?? '',
    salary_grade:           vacancy.salary_grade ?? '',
    monthly_salary:         vacancy.monthly_salary ?? '',
    position_level:         vacancy.position_level ?? '',
    is_anticipated_vacancy: vacancy.is_anticipated_vacancy ?? false,
    place_of_assignment:    vacancy.place_of_assignment ?? '',
    deadline_at:            vacancy.deadline_at ? vacancy.deadline_at.slice(0, 10) : '',
    education_req:          vacancy.education_req ?? '',
    experience_req:         vacancy.experience_req ?? '',
    training_req:           vacancy.training_req ?? '',
    eligibility_req:        vacancy.eligibility_req ?? '',
  })
  showModal.value = true
  await loadVacancyCompetencies(vacancy.id)
}

async function submitVacancy() {
  saving.value = true
  try {
    if (editingId.value) {
      await axios.put(`/api/vacancies/${editingId.value}`, form, { headers: authHeaders() })
      toast.success('Vacancy updated successfully.')
    } else {
      await axios.post('/api/vacancies', form, { headers: authHeaders() })
      toast.success('Vacancy created successfully.')
    }
    showModal.value = false
    fetchVacancies()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to save vacancy.')
  } finally {
    saving.value = false
  }
}

async function changeStatus(vacancy, action) {
  statusLoading.value = vacancy.id
  try {
    await axios.patch(`/api/vacancies/${vacancy.id}/${action}`, {}, { headers: authHeaders() })
    const label = action === 'publish' ? 'published' : 'archived'
    toast.success(`Vacancy "${vacancy.position_title}" ${label}.`)
    fetchVacancies()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Status change failed.')
  } finally {
    statusLoading.value = null
  }
}

function confirmDelete(v) { deleteTarget.value = v }

async function doDelete() {
  saving.value = true
  try {
    await axios.delete(`/api/vacancies/${deleteTarget.value.id}`, { headers: authHeaders() })
    toast.success('Vacancy deleted successfully.')
    deleteTarget.value = null
    fetchVacancies()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to delete vacancy.')
  } finally {
    saving.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────────
function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

// ── Escape handler ─────────────────────────────────────────────────────────────────
function handleKeydown(e) {
  if (e.key === 'Escape') {
    showModal.value = false
    deleteTarget.value = null
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeydown)
})

// ── Init ──────────────────────────────────────────────────────────────────────────
onMounted(async () => {
  const [_, compRes] = await Promise.all([
    fetchVacancies(),
    axios.get('/api/competencies', { headers: authHeaders() }).catch(() => null),
  ])
  if (compRes) allCompetencies.value = compRes.data.data ?? []
})
</script>
