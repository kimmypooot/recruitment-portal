<template>
  <AdminLayout title="Help & Guide">
    <div class="space-y-4">

      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Help &amp; Guide</h1>
        <p class="text-sm text-gray-500 mt-1">
          A reference for administering the Recruitment Portal — vacancy publishing, application review, HRMPSB
          composition, and the evaluation pipeline.
        </p>
      </div>

      <!-- Section nav -->
      <nav aria-label="Help topics" class="flex flex-wrap gap-2 bg-white border border-gray-200 rounded-2xl shadow-sm p-4">
        <a v-for="s in sections" :key="s.id" :href="`#${s.id}`"
          class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-gray-100 hover:bg-primary/10 hover:text-primary transition-colors">
          {{ s.label }}
        </a>
      </nav>

      <!-- 1. Roles & Access -->
      <section id="roles" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">1. Roles &amp; Access</h2>
        <p class="text-sm text-gray-500 mb-4">Three account roles exist. HRMPSB board seats are a separate layer on top of the <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">hrmpsb</code> role, not additional account roles.</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="p-4 rounded-xl border border-gray-200">
            <p class="text-sm font-semibold text-gray-900 mb-1">Applicant</p>
            <p class="text-xs text-gray-500 leading-relaxed">Public-facing account for browsing vacancies and submitting applications. No admin module access.</p>
          </div>
          <div class="p-4 rounded-xl border border-gray-200">
            <p class="text-sm font-semibold text-gray-900 mb-1">HRMPSB</p>
            <p class="text-xs text-gray-500 leading-relaxed">Board member access to the evaluation pipeline (QS, exams, BEI, deliberation, etc.). Reaches the Admin module <b>only</b> with an active Secretariat or HR Chief board designation.</p>
          </div>
          <div class="p-4 rounded-xl border border-gray-200">
            <p class="text-sm font-semibold text-gray-900 mb-1">Admin</p>
            <p class="text-xs text-gray-500 leading-relaxed">Full access to the Admin module: vacancies, users, HRMPSB composition, reports, and configuration.</p>
          </div>
        </div>
        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex gap-2.5 text-sm text-amber-800">
          <Icon name="alert" :size="4" class="flex-shrink-0 mt-0.5" />
          <span>Only a true <b>Admin</b> account can grant the Admin role or manage another admin's account — a delegated Secretariat/HR Chief user cannot promote themselves or anyone else, even though they can otherwise use this module.</span>
        </div>
      </section>

      <!-- 2. Vacancies -->
      <section id="vacancies" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">2. Managing Vacancies</h2>
        <p class="text-sm text-gray-500 mb-4">From <Link href="/admin/vacancies" class="text-primary hover:underline font-medium">Vacancies</Link>:</p>
        <ol class="space-y-2.5">
          <li v-for="(step, i) in vacancySteps" :key="i" class="flex gap-3 text-sm text-gray-700">
            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center mt-0.5">{{ i + 1 }}</span>
            {{ step }}
          </li>
        </ol>
        <p class="text-xs text-gray-400 mt-3">A vacancy only accepts applications while its status is <b>Published</b> and its deadline hasn't passed. Closing or archiving stops new applications without deleting existing ones.</p>
      </section>

      <!-- 3. Applications -->
      <section id="applications" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">3. Reviewing Applications</h2>
        <p class="text-sm text-gray-600 leading-relaxed mb-3">
          <Link href="/admin/applications" class="text-primary hover:underline font-medium">Applications</Link> lists everyone who has applied, filterable by vacancy and status.
          Opening an application shows the applicant's profile, documents, and history — use <b>Update Status</b> to move it forward and optionally schedule an exam or interview in the same step.
        </p>
        <p class="text-sm text-gray-600 leading-relaxed">Verified documents get a checkmark once HR confirms authenticity; this doesn't affect the applicant's status on its own.</p>
      </section>

      <!-- 4. HRMPSB Composition -->
      <section id="hrmpsb" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">4. HRMPSB Composition</h2>
        <p class="text-sm text-gray-600 leading-relaxed mb-3">
          <Link href="/admin/hrmpsb" class="text-primary hover:underline font-medium">HRMPSB</Link> assigns users to board roles — Chairperson, Secretariat,
          HR Chief, PINTIG Representatives, and Appointing Authority. These assignments are currently <b>global</b> (one board composition applies system-wide), not scoped to an individual vacancy or recruitment cycle.
        </p>
        <p class="text-sm text-gray-600 leading-relaxed">
          The <b>Head of Unit</b> role is the one exception — it's resolved automatically per vacancy from its place of assignment, using <b>Place of Assignment Heads</b> mappings, also managed here.
          Deactivate a composition row (rather than deleting it) to remove someone from active duty while keeping the historical audit trail intact.
        </p>
      </section>

      <!-- 5. Evaluation pipeline -->
      <section id="pipeline" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">5. The Evaluation Pipeline</h2>
        <p class="text-sm text-gray-500 mb-4">
          Once applications are screened, the HRMPSB board carries out these stages in order, in their own portal (not the Admin module) — each stage unlocks the next:
        </p>
        <div class="flex flex-wrap items-center gap-1.5">
          <template v-for="(stage, i) in pipeline" :key="stage">
            <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-700 whitespace-nowrap">{{ stage }}</span>
            <Icon v-if="i < pipeline.length - 1" name="chevronRight" :size="3.5" class="text-gray-300 flex-shrink-0" />
          </template>
        </div>
        <p class="text-xs text-gray-400 mt-3">
          Deliberation uses blinded applicant identities by default — a board member can unmask them (Admin-level access only) once ready to finalize rankings.
          After the Appointing Authority's decision, CS Form No. 33-A/33-B and Form 1 are generated from the application record.
        </p>
      </section>

      <!-- 6. Reports -->
      <section id="reports" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">6. Reports &amp; Exports</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
          <Link href="/admin/reports" class="text-primary hover:underline font-medium">Reports</Link> covers qualified-applicant lists, comparative assessment summaries, appointment reports,
          pipeline/demographic breakdowns. Applicant lists and audit logs can also be exported as CSV from their respective pages for offline record-keeping.
        </p>
      </section>

      <!-- 7. Email templates -->
      <section id="email-templates" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">7. Email Templates</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
          <Link href="/admin/email-templates" class="text-primary hover:underline font-medium">Email Templates</Link> lets you edit the wording of automated notifications (application received, exam scheduled, status changes, etc.)
          without touching code. Placeholders like <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">{{ placeholderExample }}</code> are filled in automatically — use <b>Preview</b> before saving to check the result.
        </p>
      </section>

      <!-- 8. Audit logs -->
      <section id="audit-logs" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">8. Audit Logs</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
          <Link href="/admin/audit-logs" class="text-primary hover:underline font-medium">Audit Logs</Link> records who did what and when — vacancy publishing, status changes, user management, and more.
          Use it to investigate a disputed decision or confirm when an action actually happened; it does not expose document contents or evaluation scores.
        </p>
      </section>

      <!-- 9. Troubleshooting -->
      <section id="troubleshooting" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">9. Common Problems</h2>
        <div class="space-y-2 mt-4">
          <details v-for="(item, i) in troubleshooting" :key="i" class="group rounded-xl border border-gray-200 open:border-primary/30 open:bg-primary/5 transition-colors">
            <summary class="flex items-center justify-between gap-3 px-4 py-3 cursor-pointer text-sm font-medium text-gray-800 select-none">
              {{ item.q }}
              <Icon name="chevronDown" :size="4" class="flex-shrink-0 text-gray-400 group-open:rotate-180 transition-transform" />
            </summary>
            <p class="px-4 pb-4 text-sm text-gray-600 leading-relaxed">{{ item.a }}</p>
          </details>
        </div>
      </section>

      <!-- 10. FAQ -->
      <section id="faq" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">10. Frequently Asked Questions</h2>
        <div class="space-y-2 mt-4">
          <details v-for="(item, i) in faqs" :key="i" class="group rounded-xl border border-gray-200 open:border-primary/30 open:bg-primary/5 transition-colors">
            <summary class="flex items-center justify-between gap-3 px-4 py-3 cursor-pointer text-sm font-medium text-gray-800 select-none">
              {{ item.q }}
              <Icon name="chevronDown" :size="4" class="flex-shrink-0 text-gray-400 group-open:rotate-180 transition-transform" />
            </summary>
            <p class="px-4 pb-4 text-sm text-gray-600 leading-relaxed">{{ item.a }}</p>
          </details>
        </div>
      </section>

    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Icon from '@/Components/UI/Icon.vue'

// Written as a variable, not inline in the template, so the literal {{ }}
// characters don't get parsed as a (broken, nested) Vue interpolation.
const placeholderExample = '{{position_title}}'

const sectionCard = 'scroll-mt-24 bg-white border border-gray-200 rounded-2xl shadow-sm p-6'

const sections = [
  { id: 'roles', label: 'Roles & Access' },
  { id: 'vacancies', label: 'Vacancies' },
  { id: 'applications', label: 'Applications' },
  { id: 'hrmpsb', label: 'HRMPSB Composition' },
  { id: 'pipeline', label: 'Evaluation Pipeline' },
  { id: 'reports', label: 'Reports' },
  { id: 'email-templates', label: 'Email Templates' },
  { id: 'audit-logs', label: 'Audit Logs' },
  { id: 'troubleshooting', label: 'Common Problems' },
  { id: 'faq', label: 'FAQ' },
]

const vacancySteps = [
  'Select "Add Vacancy" and fill in the position details, salary grade, place of assignment, and full Qualification Standards (education, eligibility, training, experience).',
  'Set the application deadline and, if applicable, a future publication date.',
  'Save as Draft to keep working on it, or Publish to make it visible and start accepting applications immediately.',
  'Use Edit for corrections while it\'s still open — Close or Archive once you\'re done accepting applications for it.',
]

const pipeline = [
  'Pre-Assessment', 'QS Evaluation', 'Written Exam (TWE)', 'CBWE', 'BEI',
  'EOPT', 'Background Check', 'Deliberation', 'Appointing Authority Decision',
]

const troubleshooting = [
  { q: "A vacancy I just published isn't showing up publicly", a: 'Check its publication date — if it\'s set in the future, the vacancy stays hidden until that date arrives even though its status is "Published".' },
  { q: "I can't access a module I think I should have access to", a: 'Admin-module access requires either the Admin role, or the HRMPSB role with an active Secretariat/HR Chief board designation. Check Users and HRMPSB composition to confirm the account has one of these.' },
  { q: 'A board member says they can\'t see an evaluation page', a: 'Pipeline pages are gated by stage — e.g. the BEI Scheduler only unlocks once QS evaluations are locked for that vacancy. Confirm the prerequisite stage has actually been locked.' },
  { q: 'CS Form signing says PNPKI is not configured', a: 'Digital signing requires CSC-issued PNPKI credentials that haven\'t been configured on this environment yet. Forms can still be printed and wet-signed, or e-signed, in the meantime.' },
  { q: 'I need to correct a status change I made by mistake', a: 'Use Update Status again to correct it — the audit log preserves a record of the original change, so nothing is silently lost.' },
]

const faqs = [
  { q: 'Can I delete a vacancy that already has applicants?', a: 'Vacancy deletion is restricted to true Admin accounts specifically as a safeguard, separate from general Admin-module access. Archiving is usually the safer choice — it stops new applications while keeping the record and its applicants intact.' },
  { q: 'What\'s the difference between "Close" and "Archive" on a vacancy?', a: 'Closed vacancies stop accepting new applications but can be reopened; archived vacancies are treated as permanently concluded. Both preserve all existing application data.' },
  { q: 'Why is deliberation "blind"?', a: 'Applicant identities are masked from evaluators by default during screening and deliberation to reduce bias, in line with CSC\'s merit-selection principles. Identities are revealed only when an authorized user explicitly unmasks them, ahead of finalizing rankings — and that action is logged.' },
  { q: 'Can a Secretariat-designated user manage other users?', a: 'Yes, for non-admin accounts — creating/editing applicant and HRMPSB accounts is within their delegated scope. They cannot grant the Admin role or modify/delete an existing admin account; only a true Admin can do that.' },
]
</script>
