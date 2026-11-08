// src/hooks/useUrls.ts

import { useCallback, useEffect, useState } from 'react'
import * as urlsApi from '../lib/urls'
import type { Url } from '../types'

export function useUrls() {
  const [urls, setUrls] = useState<Url[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const refresh = useCallback(async () => {
    setLoading(true)
    try {
      const response = await urlsApi.fetchUrls(1, 50)
      setUrls(response.data)
      setError(null)
    } catch (err) {
      console.error('[useUrls] Failed to load URLs:', err)
      setError('Failed to load URLs')
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    refresh()
  }, [refresh])

  async function create(originalUrl: string, customAlias?: string) {
    try {
      const newUrl = await urlsApi.createUrl(originalUrl, customAlias)
      setUrls((prev) => [newUrl, ...prev])
      return newUrl
    } catch (err) {
      throw err
    }
  }

  async function remove(id: number) {
    await urlsApi.deleteUrl(id)
    setUrls((prev) => prev.filter((u) => u.id !== id))
  }

  async function toggle(id: number, isActive: boolean) {
    const updated = await urlsApi.toggleUrl(id, isActive)
    setUrls((prev) => prev.map((u) => (u.id === id ? updated : u)))
  }

  /**
   * Increment the click counter for a URL (for real-time updates).
   */
  function incrementClicks(id: number) {
    setUrls((prev) =>
      prev.map((u) =>
        u.id === id ? { ...u, clicks_count: u.clicks_count + 1 } : u
      )
    )
  }

  return { urls, loading, error, refresh, create, remove, toggle, incrementClicks }
}