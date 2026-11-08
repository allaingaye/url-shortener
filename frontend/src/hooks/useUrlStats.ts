// src/hooks/useUrlStats.ts

import { useEffect, useState } from 'react'
import * as statsApi from '../lib/stats'
import type { UrlStats } from '../types/stats'

export function useUrlStats(urlId: number | null) {
  const [stats, setStats] = useState<UrlStats | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (!urlId) return

    setLoading(true)
    statsApi
      .fetchUrlStats(urlId)
      .then((data) => {
        setStats(data)
        setError(null)
      })
      .catch((err) => {
        console.error('[useUrlStats] Failed:', err)
        setError('Failed to load analytics')
      })
      .finally(() => setLoading(false))
  }, [urlId])

  return { stats, loading, error }
}