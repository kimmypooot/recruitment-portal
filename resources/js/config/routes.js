export const ROUTES = {
  HOME: '/',
  LOGIN: '/login',
  REGISTER: '/register',
  FORGOT_PASSWORD: '/forgot-password',
  RESET_PASSWORD: '/reset-password',
  VERIFY_EMAIL: '/email/verify',
  PRIVACY_POLICY: '/privacy-policy',
  TERMS: '/terms-of-service',
  HOW_TO_APPLY: '/how-to-apply',
  ABOUT: '/about',

  APPLICANT_DASHBOARD: '/applicant/dashboard',
  APPLICANT_APPLICATIONS: '/applicant/applications',
  APPLICANT_PROFILE: '/applicant/complete-profile',

  ADMIN_DASHBOARD: '/admin/dashboard',
  ADMIN_VACANCIES: '/admin/vacancies',
  ADMIN_APPLICATIONS: '/admin/applications',
  ADMIN_USERS: '/admin/users',
  ADMIN_FEEDBACKS: '/admin/feedbacks',
  ADMIN_REPORTS: '/admin/reports',
  ADMIN_COMPETENCIES: '/admin/competencies',
  ADMIN_AUDIT_LOGS: '/admin/audit-logs',
  ADMIN_HRMPSB: '/admin/hrmpsb',

  HRMPSB_DASHBOARD: '/hrmpsb/dashboard',
  APPOINTING_AUTHORITY_DASHBOARD: '/appointing-authority/dashboard',

  API_LOGIN: '/api/login',
  API_REGISTER: '/api/register',
  API_LOGOUT: '/api/logout',
  API_PROFILE: '/api/profile',
  API_VACANCIES: '/api/vacancies',
  API_APPLICATIONS: '/api/applications',
  API_FEEDBACK: '/api/feedback',
  API_TESTIMONIALS: '/api/testimonials',
  API_CHANGE_PASSWORD: '/api/change-password',
  API_HRMPSB_MY_ROLE: '/api/hrmpsb/my-role',
  API_DASHBOARD_STATS: '/api/dashboard/stats',
  API_DASHBOARD_PIPELINE: '/api/dashboard/pipeline',
  API_DASHBOARD_RECENT: '/api/dashboard/recent-applications',
  API_DASHBOARD_UPCOMING: '/api/dashboard/upcoming',
}

export function vacancyApplyRoute(id) {
  return `/vacancies/${id}/apply`
}

export function vacancyDetailRoute(id) {
  return `/vacancies/${id}`
}
