// src/lib/api.ts

import axios from 'axios'

/**
 * Axios instance configured for Sanctum SPA authentication.
 *
 * - `withCredentials: true` sends cookies on every request
 * - `X-Requested-With: XMLHttpRequest` makes Laravel treat it as AJAX (returns JSON)
 * - CSRF token is automatically attached from the XSRF-TOKEN cookie
 */
const api = axios.create({
  baseURL: '/api/v1',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

/**
 * Read the XSRF-TOKEN cookie set by Sanctum's /sanctum/csrf-cookie endpoint.
 */
function getCsrfToken(): string | null {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : null
}

/**
 * Request interceptor — attach CSRF token if present.
 */
api.interceptors.request.use((config) => {
  const token = getCsrfToken()
  if (token) {
    config.headers['X-XSRF-TOKEN'] = token
  }
  return config
})

/**
 * Response interceptor — handle 401 globally.
 * If we get 401, the session expired. Future: redirect to login.
 */
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // The auth hook will handle redirect; we just propagate
      console.warn('Unauthenticated request:', error.config?.url)
    }
    return Promise.reject(error)
  }
)

export default api