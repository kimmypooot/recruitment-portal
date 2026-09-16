<template>
  <PublicLayout>

    <Head>
      <title>Help &amp; FAQ</title>
      <meta name="description" content="Guides and answers for using the CSC RO VIII Recruitment Portal — creating an account, applying, uploading documents, and tracking your application." />
    </Head>

    <!-- Hero -->
    <section class="relative text-white overflow-hidden min-h-[240px]"
      style="background-image: url('/images/cscbg_facade.jpeg'); background-size: cover; background-position: center;">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(30,37,112,0.88) 0%, rgba(42,51,143,0.85) 50%, rgba(26,31,94,0.90) 100%);"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="max-w-2xl">
          <p class="text-white/60 text-sm font-medium uppercase tracking-wider mb-3">Applicant Guide</p>
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">Help &amp; FAQ</h1>
          <p class="text-white/80 text-base leading-relaxed">
            Everything you need to create an account, apply for a position, and track your progress — explained simply.
          </p>
        </div>
      </div>
      <div class="relative h-10 overflow-hidden z-10 pointer-events-none">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full" fill="#F9FAFB" aria-hidden="true">
          <path d="M0 40L60 33.3C120 26.7 240 13.3 360 10C480 6.7 600 13.3 720 20C840 26.7 960 33.3 1080 33.3C1200 33.3 1320 26.7 1380 23.3L1440 20V40H0Z"/>
        </svg>
      </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 lg:grid-cols-[220px_1fr] gap-10">

        <!-- Section nav -->
        <nav aria-label="Help topics" class="lg:sticky lg:top-20 lg:self-start bg-white border border-gray-200 rounded-2xl shadow-sm p-4">
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">On this page</p>
          <ul class="space-y-1 text-sm">
            <li v-for="s in sections" :key="s.id">
              <a :href="`#${s.id}`" class="block px-3 py-1.5 rounded-lg text-gray-600 hover:text-primary hover:bg-primary/5 transition-colors">{{ s.label }}</a>
            </li>
          </ul>
        </nav>

        <div class="space-y-6 min-w-0">

          <!-- 1. Getting Started -->
          <section id="getting-started" :class="sectionCard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">1. Creating Your Account</h2>
            <p class="text-sm text-gray-500 mb-6">You need a free applicant account to apply and track your application.</p>
            <ol class="space-y-3">
              <li v-for="(step, i) in accountSteps" :key="i" class="flex gap-3">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center mt-0.5">{{ i + 1 }}</span>
                <p class="text-sm text-gray-700 leading-relaxed">{{ step }}</p>
              </li>
            </ol>
          </section>

          <!-- 2. Profile -->
          <section id="profile" :class="sectionCard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">2. Completing Your Profile</h2>
            <p class="text-sm text-gray-500 mb-6">
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

          <!-- 3. Finding a vacancy -->
          <section id="finding" :class="sectionCard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">3. Finding &amp; Checking Eligibility</h2>
            <p class="text-sm text-gray-500 mb-4">Browse open positions on the <Link href="/" class="text-primary hover:underline font-medium">homepage</Link>. Each vacancy lists its own requirements — compare them against your background before applying:</p>
            <ul class="space-y-2 text-sm text-gray-700">
              <li class="flex gap-2"><Icon name="check" :size="4" class="text-primary flex-shrink-0 mt-0.5" /><span><b>Education</b> — the minimum degree/level required</span></li>
              <li class="flex gap-2"><Icon name="check" :size="4" class="text-primary flex-shrink-0 mt-0.5" /><span><b>Eligibility</b> — the civil service eligibility or license required (e.g. CSC Professional, PRC license)</span></li>
              <li class="flex gap-2"><Icon name="check" :size="4" class="text-primary flex-shrink-0 mt-0.5" /><span><b>Training</b> — relevant training hours, if specified</span></li>
              <li class="flex gap-2"><Icon name="check" :size="4" class="text-primary flex-shrink-0 mt-0.5" /><span><b>Experience</b> — years of relevant work experience required</span></li>
            </ul>
            <p class="text-sm text-gray-500 mt-4">Only <b>published</b> vacancies with a deadline that hasn't passed accept applications — check the deadline shown on each listing.</p>
          </section>

          <!-- 4. Applying -->
          <section id="applying" :class="sectionCard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">4. Submitting Your Application</h2>
            <p class="text-sm text-gray-500 mb-4">
              Once your profile is complete and documents are uploaded, open a vacancy and select <b>Apply Now</b>. For the full step-by-step walkthrough, see the
              <Link href="/how-to-apply" class="text-primary hover:underline font-medium">How to Apply</Link> guide.
            </p>
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex gap-2.5 text-sm text-amber-800">
              <Icon name="alert" :size="4" class="flex-shrink-0 mt-0.5" />
              <span>Applications cannot be edited after submission — review everything carefully before you confirm.</span>
            </div>
          </section>

          <!-- 5. Documents -->
          <section id="documents" :class="sectionCard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">5. Uploading Documents</h2>
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
              <li>• Don't have a filled-out PDS on hand? You can generate one from the details you've already entered in your profile — see the Documents tab.</li>
            </ul>
          </section>

          <!-- 6. Tracking -->
          <section id="tracking" :class="sectionCard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">6. Tracking Your Application</h2>
            <p class="text-sm text-gray-500 mb-4">Two ways to check your status:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="p-4 rounded-xl border border-gray-200">
                <p class="text-sm font-semibold text-gray-900 mb-1">Signed in</p>
                <p class="text-xs text-gray-500 leading-relaxed">Go to <Link href="/applicant/applications" class="text-primary hover:underline font-medium">My Applications</Link> in your dashboard for full details, documents, and history.</p>
              </div>
              <div class="p-4 rounded-xl border border-gray-200">
                <p class="text-sm font-semibold text-gray-900 mb-1">Without signing in</p>
                <p class="text-xs text-gray-500 leading-relaxed">
                  Use <Link href="/track-application" class="text-primary hover:underline font-medium">Track My Application</Link> with the reference code emailed to you at submission, plus your email address.
                </p>
              </div>
            </div>
          </section>

          <!-- 7. Statuses -->
          <section id="statuses" :class="sectionCard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">7. What Your Status Means</h2>
            <p class="text-sm text-gray-500 mb-4">Statuses appear in the order they typically occur — not every application passes through every one.</p>
            <div class="rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
              <div v-for="s in statuses" :key="s.key" class="flex items-start gap-4 px-4 py-3">
                <StatusBadge :status="s.key" class="flex-shrink-0 mt-0.5" />
                <p class="text-sm text-gray-600 leading-relaxed">{{ s.meaning }}</p>
              </div>
            </div>
          </section>

          <!-- 8. Notifications -->
          <section id="notifications" :class="sectionCard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">8. Notifications</h2>
            <p class="text-sm text-gray-600 leading-relaxed">
              You'll receive an email whenever your application status changes, an exam or interview is scheduled, or action is needed from you.
              The same updates also appear in the notification bell at the top of your dashboard. Make sure <b>ro08.hrd@csc.gov.ph</b> isn't filtered to spam,
              and keep your email address current under Profile settings.
            </p>
          </section>

          <!-- 9. Troubleshooting -->
          <section id="troubleshooting" :class="sectionCard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">9. Common Problems</h2>
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
            <h2 class="text-xl font-bold text-gray-900 mb-1">10. Frequently Asked Questions</h2>
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
      </div>
    </div>

  </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import Icon from '@/Components/UI/Icon.vue'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

const sectionCard = 'scroll-mt-24 bg-white border border-gray-200 rounded-2xl shadow-sm p-6'

const sections = [
  { id: 'getting-started', label: '1. Creating an Account' },
  { id: 'profile', label: '2. Your Profile' },
  { id: 'finding', label: '3. Finding a Vacancy' },
  { id: 'applying', label: '4. Applying' },
  { id: 'documents', label: '5. Documents' },
  { id: 'tracking', label: '6. Tracking' },
  { id: 'statuses', label: '7. Statuses' },
  { id: 'notifications', label: '8. Notifications' },
  { id: 'troubleshooting', label: '9. Common Problems' },
  { id: 'faq', label: '10. FAQ' },
]

const accountSteps = [
  'Go to Register and sign up with your email, or use "Continue with Google" for faster signup.',
  'If you registered with email, check your inbox for a 6-digit verification code and enter it to activate your account.',
  'Read and acknowledge the Data Privacy notice — this is required before you can use the portal.',
  'Sign in any time from the Sign In page. Forgot your password? Use "Forgot password" to reset it by email.',
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
  { q: "I didn't receive my verification email", a: 'Check your spam/junk folder first. If it\'s not there, you can request a new code from the verification screen — codes expire after a short time, so request a fresh one rather than reusing an old email.' },
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
