// src/lib/auth.ts

import api from './api'
import type { User } from '../types'

/**
 * Sanctum SPA authentication helpers.
 *
 * All auth flows require the CSRF cookie first — this is a Sanctum requirement.
 */

/**
 * Fetch the CSRF cookie from Sanctum.
 * Must be called before any POST/PUT/DELETE to /api.
 */
export async function fetchCsrfCookie(): Promise<void> {
  await api.get('/sanctum/csrf-cookie', { baseURL: '' })
}

/**
 * Fetch the current authenticated user.
 * Returns null if unauthenticated (401).
 */
export async function me(): Promise<User | null> {
  try {
    const response = await api.get('/auth/me')
    return response.data
  } catch (error: unknown) {
    if (
      typeof error === 'object' &&
      error !== null &&
      'response' in error &&
      (error as { response?: { status?: number } }).response?.status === 401
    ) {
      return null
    }
    throw error
  }
}

/**
 * Register a new user and return the authenticated session.
 */
export async function register(
  name: string,
  email: string,
  password: string,
  passwordConfirmation: string
): Promise<User> {
  await fetchCsrfCookie()

  await api.post('/auth/register', {
    name,
    email,
    password,
    password_confirmation: passwordConfirmation,
  })

  const user = await me()

  if (!user) {
    throw new Error('Registration succeeded but user could not be fetched')
  }

  return user
}

/**
 * Login with email and password.
 */
export async function login(email: string, password: string): Promise<User> {
  await fetchCsrfCookie()

  await api.post('/auth/login', { email, password })

  const user = await me()

  if (!user) {
    throw new Error('Login succeeded but user could not be fetched')
  }

  return user
}

/**
 * Logout — revoke the current session.
 */
export async function logout(): Promise<void> {
  await fetchCsrfCookie()
  await api.post('/auth/logout')
}