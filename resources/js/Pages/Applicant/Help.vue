<template>
  <ApplicantLayout>
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-2 space-y-4">

      <!-- Page header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Help &amp; FAQ</h1>
        <p class="text-sm text-gray-500 mt-1">
          Everything you need to complete your profile, apply for a position, and track your progress.
        </p>
      </div>

      <!-- Section nav -->
      <nav aria-label="Help topics" class="flex flex-wrap gap-2 bg-white border border-gray-200 rounded-2xl shadow-sm p-4">
        <a v-for="s in sections" :key="s.id" :href="`#${s.id}`"
          class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-gray-100 hover:bg-primary/10 hover:text-primary transition-colors">
          {{ s.label }}
        </a>
      </nav>

      <!-- 1. Profile -->
      <section id="profile" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">1. Completing Your Profile</h2>
        <p class="text-sm text-gray-500 mb-4">
          A complete profile is required before you can apply. From <Link href="/applicant/complete-profile" class="text-primary hover:underline font-medium">Complete Profile</Link>, fill in:
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div v-for="item in profileSections" :key="item" class="flex items-center gap-2.5 p-3 rounded-lg bg-gray-50 border border-gray-200 text-sm text-gray-700">
            <Icon name="check" :size="4" class="text-primary flex-shrink-0" />
            {{ item }}
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">Your progress is saved automatically as you type, so you can leave and come back without losing work.</p>
      </section>

      <!-- 2. Finding a vacancy -->
      <section id="finding" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">2. Finding &amp; Checking Eligibility</h2>
        <p class="text-sm text-gray-500 mb-4">Browse open positions from <Link href="/applicant/dashboard?tab=vacancies" class="text-primary hover:underline font-medium">Browse Vacancies</Link>. Each vacancy lists its own requirements — compare them against your background before applying:</p>
        <ul class="space-y-2 text-sm text-gray-700">
          <li class="flex gap-2"><Icon name="check" :size="4" class="text-primary flex-shrink-0 mt-0.5" /><span><b>Education</b> — the minimum degree/level required</span></li>
          <li class="flex gap-2"><Icon name="check" :size="4" class="text-primary flex-shrink-0 mt-0.5" /><span><b>Eligibility</b> — the civil service eligibility or license required (e.g. CSC Professional, PRC license)</span></li>
          <li class="flex gap-2"><Icon name="check" :size="4" class="text-primary flex-shrink-0 mt-0.5" /><span><b>Training</b> — relevant training hours, if specified</span></li>
          <li class="flex gap-2"><Icon name="check" :size="4" class="text-primary flex-shrink-0 mt-0.5" /><span><b>Experience</b> — years of relevant work experience required</span></li>
        </ul>
        <p class="text-sm text-gray-500 mt-4">Only <b>published</b> vacancies with a deadline that hasn't passed accept applications — check the deadline shown on each listing.</p>
      </section>

      <!-- 3. Applying -->
      <section id="applying" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">3. Submitting Your Application</h2>
        <p class="text-sm text-gray-500 mb-4">
          Once your profile is complete and documents are uploaded, open a vacancy and select <b>Apply Now</b>. For the full step-by-step walkthrough, see the
          <Link href="/how-to-apply" class="text-primary hover:underline font-medium">How to Apply</Link> guide.
        </p>
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex gap-2.5 text-sm text-amber-800">
          <Icon name="alert" :size="4" class="flex-shrink-0 mt-0.5" />
          <span>Applications cannot be edited after submission — review everything carefully before you confirm.</span>
        </div>
      </section>

      <!-- 4. Documents -->
      <section id="documents" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">4. Uploading Documents</h2>
        <p class="text-sm text-gray-500 mb-4">Required documents (Profile → Documents):</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
          <div v-for="doc in requiredDocs" :key="doc" class="flex items-center gap-2.5 p-3 rounded-lg bg-gray-50 border border-gray-200 text-sm text-gray-700">
            <Icon name="document" :size="4" class="text-primary flex-shrink-0" />
            {{ doc }}
          </div>
        </div>
        <ul class="space-y-1.5 text-sm text-gray-600">
          <li>• Documents must be <b>PDF</b> format, up to <b>5 MB</b> each.</li>
          <li>• Your profile photo must be <b>JPG or PNG</b>, up to <b>3 MB</b>.</li>
          <li>• Re-uploading a document replaces the previous file.</li>
        </ul>
      </section>

      <!-- 5. Tracking -->
      <section id="tracking" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">5. Tracking Your Application</h2>
        <p class="text-sm text-gray-500 mb-4">
          Go to <Link href="/applicant/applications" class="text-primary hover:underline font-medium">My Applications</Link> for full details, documents, and history on everything you've submitted.
        </p>
      </section>

      <!-- 6. Statuses -->
      <section id="statuses" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">6. What Your Status Means</h2>
        <p class="text-sm text-gray-500 mb-4">Statuses appear in the order they typically occur — not every application passes through every one.</p>
        <div class="rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
          <div v-for="s in statuses" :key="s.key" class="flex items-start gap-4 px-4 py-3">
            <StatusBadge :status="s.key" class="flex-shrink-0 mt-0.5" />
            <p class="text-sm text-gray-600 leading-relaxed">{{ s.meaning }}</p>
          </div>
        </div>
      </section>

      <!-- 7. Notifications -->
      <section id="notifications" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">7. Notifications</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
          You'll receive an email whenever your application status changes, an exam or interview is scheduled, or action is needed from you.
          The same updates also appear in the notification bell at the top of this dashboard. Make sure <b>ro08.hrd@csc.gov.ph</b> isn't filtered to spam,
          and keep your email address current under Profile settings.
        </p>
      </section>

      <!-- 8. Troubleshooting -->
      <section id="troubleshooting" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">8. Common Problems</h2>
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

      <!-- 9. FAQ -->
      <section id="faq" :class="sectionCard">
        <h2 class="text-lg font-bold text-gray-900 mb-1">9. Frequently Asked Questions</h2>
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

      <!-- Contact -->
      <section id="contact" class="rounded-2xl bg-primary/5 border border-primary/10 p-6 text-center">
        <h2 class="text-base font-bold text-gray-900 mb-1">Still need help?</h2>
        <p class="text-sm text-gray-500 mb-4">Reach the HRMPSB Secretariat directly.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm text-gray-700">
          <a href="mailto:ro08.hrd@csc.gov.ph" class="flex items-center gap-2 hover:text-primary transition-colors">
            <Icon name="mail" :size="4" class="text-primary" /> ro08.hrd@csc.gov.ph
          </a>
          <span class="hidden sm:inline text-gray-300">|</span>
          <span class="flex items-center gap-2">
            <Icon name="phone" :size="4" class="text-primary" /> (053) 888-1811
          </span>
        </div>
      </section>

    </div>
  </ApplicantLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import ApplicantLayout from '@/Layouts/ApplicantLayout.vue'
import Icon from '@/Components/UI/Icon.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

const sectionCard = 'scroll-mt-24 bg-white border border-gray-200 rounded-2xl shadow-sm p-6'

const sections = [
  { id: 'profile', label: 'Your Profile' },
  { id: 'finding', label: 'Finding a Vacancy' },
  { id: 'applying', label: 'Applying' },
  { id: 'documents', label: 'Documents' },
  { id: 'tracking', label: 'Tracking' },
  { id: 'statuses', label: 'Statuses' },
  { id: 'notifications', label: 'Notifications' },
  { id: 'troubleshooting', label: 'Common Problems' },
  { id: 'faq', label: 'FAQ' },
]

const profileSections = [
  'Personal Information', 'Contact Information & Address', 'Educational Background',
  'Civil Service Eligibility', 'Work Experience', 'Training & Development', 'Documents',
]

const requiredDocs = [
  'Personal Data Sheet (CS Form No. 212)', 'Application Letter',
  'Certificate of Eligibility', 'Transcript of Records', 'IPCR (optional)',
]

const statuses = [
  { key: 'submitted', meaning: 'Your application has been received and is waiting for initial review.' },
  { key: 'under_review', meaning: 'HR is reviewing your application and documents.' },
  { key: 'screened', meaning: 'Your documents and qualifications have been checked against the position requirements.' },
  { key: 'qualified', meaning: 'You meet the Qualification Standards for this position and will proceed to the next stage.' },
  { key: 'exam_scheduled', meaning: 'A written examination or assessment has been scheduled for you — watch your email for the date, time, and venue.' },
  { key: 'for_interview', meaning: 'You have been scheduled for a Behavioral Event Interview (BEI).' },
  { key: 'interviewed', meaning: 'Your interview has been completed and is being scored.' },
  { key: 'recommended', meaning: 'The HRMPSB has endorsed you as a candidate to the Appointing Authority.' },
  { key: 'appointed', meaning: 'Congratulations — you have been selected for the position.' },
  { key: 'disqualified', meaning: 'You did not meet the Qualification Standards for this position.' },
  { key: 'failed', meaning: 'You did not pass a required stage (e.g. the written exam) of the selection process.' },
  { key: 'withdrawn', meaning: 'You withdrew this application yourself.' },
  { key: 'completed', meaning: 'This application has reached the end of the recruitment process.' },
]

const troubleshooting = [
  { q: "I can't apply — the Apply button is disabled", a: 'This usually means your profile is incomplete or a required document is missing. Open Complete Profile and check for any sections still marked incomplete.' },
  { q: 'My file won\'t upload', a: 'Documents must be PDF and under 5 MB (photos: JPG/PNG, under 3 MB). If a scan is too large, try re-scanning at a lower resolution or compressing the PDF.' },
  { q: 'I made a mistake after submitting my application', a: 'Applications can\'t be edited after submission. Contact the HRMPSB Secretariat directly — your profile and document updates will still apply to future applications.' },
  { q: 'I want to withdraw my application', a: 'Open the application from My Applications and select Withdraw. This is only available while the application is still active (not yet appointed, recommended, or already withdrawn/closed).' },
  { q: "I'm signed in but keep getting signed out", a: 'Sessions expire after 2 hours of inactivity unless you checked "Remember me" at login. Signing in again resumes exactly where you left off.' },
]

const faqs = [
  { q: 'Is there a fee to apply?', a: 'No. Applying through the CSC RO VIII Recruitment Portal is completely free.' },
  { q: 'Can I apply for more than one position at a time?', a: 'Yes, you may apply to multiple open vacancies as long as you meet each position\'s requirements, using the same account and profile.' },
  { q: 'Can I apply again after being disqualified or not selected?', a: 'Yes — you\'re welcome to apply for other open positions, or the same position in a future posting, as long as you meet the requirements.' },
  { q: 'How long does the recruitment process take?', a: 'Timelines vary by position and number of applicants. Your application status and any notifications will keep you informed of where things stand — there\'s no need to follow up unless a stage has been pending for an unusually long time.' },
  { q: 'Who can see my documents and personal information?', a: 'Only authorized CSC RO VIII personnel involved in your application\'s review — HR staff and HRMPSB board members — can access your documents. During blind screening stages, your identity is masked from evaluators until final deliberation.' },
  { q: 'Can I update my profile after applying?', a: 'Yes, your profile can be updated any time. Changes apply to future applications; an application already submitted reflects your details at the time you applied.' },
]
</script>
