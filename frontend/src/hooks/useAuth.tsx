// src/hooks/useAuth.tsx

import { createContext, useContext, useEffect, useState } from 'react'
import type { ReactNode } from 'react'
import * as authApi from '../lib/auth'
import type { User } from '../types'

interface AuthContextType {
  user: User | null
  loading: boolean
  error: string | null
  login: (email: string, password: string) => Promise<void>
  register: (
    name: string,
    email: string,
    password: string,
    confirmation: string
  ) => Promise<void>
  logout: () => Promise<void>
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  // Check existing session on mount
  useEffect(() => {
    authApi
      .me()
      .then((u) => setUser(u))
      .catch(() => setUser(null))
      .finally(() => setLoading(false))
  }, [])

  async function login(email: string, password: string) {
    setError(null)
    try {
      const u = await authApi.login(email, password)
      setUser(u)
    } catch (err: unknown) {
      const message = extractErrorMessage(err, 'Login failed. Please try again.')
      setError(message)
      throw new Error(message)
    }
  }

  async function register(
    name: string,
    email: string,
    password: string,
    confirmation: string
  ) {
    setError(null)
    try {
      const u = await authApi.register(name, email, password, confirmation)
      setUser(u)
    } catch (err: unknown) {
      const message = extractErrorMessage(err, 'Registration failed. Please try again.')
      setError(message)
      throw new Error(message)
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch {
      // Ignore — even if the API call fails, we clear local state
    }
    setUser(null)
  }

  return (
    <AuthContext.Provider
      value={{ user, loading, error, login, register, logout }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}

/**
 * Safely extract a human-readable error message from an unknown value.
 */
function extractErrorMessage(err: unknown, fallback: string): string {
  if (
    typeof err === 'object' &&
    err !== null &&
    'response' in err &&
    typeof (err as { response?: unknown }).response === 'object'
  ) {
    const response = (err as { response?: { data?: { message?: unknown } } }).response
    const msg = response?.data?.message
    if (typeof msg === 'string') return msg
  }
  if (err instanceof Error) return err.message
  return fallback
}