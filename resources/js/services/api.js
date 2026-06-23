// resources/js/services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

api.defaults.withCredentials = true;

// Attach stored Bearer token on every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Global error interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      window.location.href = '/login';
    }
    if (error.response?.status === 403) {
      console.error('Forbidden — insufficient permissions');
    }
    return Promise.reject(error);
  }
);

// Typed API service for Vacancies
export const vacancyApi = {
  index: (params) => api.get('/vacancies', { params }),
  show: (id) => api.get(`/vacancies/${id}`),
  store: (data) => api.post('/vacancies', data),
  update: (id, data) => api.patch(`/vacancies/${id}`, data),
  destroy: (id) => api.delete(`/vacancies/${id}`),
};

export const applicationApi = {
  myApplications: () => api.get('/my-applications'),
  submit: (data) => api.post('/applications', data),
  uploadDocument: (appId, formData) =>
    api.post(`/applications/${appId}/documents`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),
};

export default api;

export const profileApi = {
  show:   ()     => api.get('/profile'),
  update: (data) => api.put('/profile', data),
  uploadPhoto: (formData) =>
    api.post('/profile/photo', formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
  uploadDocuments: (formData) =>
    api.post('/profile/documents', formData, { headers: { 'Content-Type': 'multipart/form-data' } }),

  addExperience:    (data)     => api.post('/profile/experiences', data),
  updateExperience: (id, data) => api.put(`/profile/experiences/${id}`, data),
  deleteExperience: (id)       => api.delete(`/profile/experiences/${id}`),

  addEducation:    (data)     => api.post('/profile/education', data),
  updateEducation: (id, data) => api.put(`/profile/education/${id}`, data),
  deleteEducation: (id)       => api.delete(`/profile/education/${id}`),

  addTraining:    (data)     => api.post('/profile/trainings', data),
  updateTraining: (id, data) => api.put(`/profile/trainings/${id}`, data),
  deleteTraining: (id)       => api.delete(`/profile/trainings/${id}`),
};
