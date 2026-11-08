// src/pages/Dashboard.tsx

import { useEffect } from 'react'
import { useAuth } from '../hooks/useAuth'
import { useUrls } from '../hooks/useUrls'
import { useLiveClicks } from '../hooks/useLiveClicks'
import { StatsCards } from '../components/StatsCards'
import { CreateUrlForm } from '../components/CreateUrlForm'
import { UrlsTable } from '../components/UrlsTable'

export function Dashboard() {
  const { user } = useAuth()
  const { urls, loading, create, remove, toggle, incrementClicks } = useUrls()
  const { clicks, isConnected } = useLiveClicks(user?.id ?? null)

  // Bump the counter when a new click arrives
  useEffect(() => {
    if (clicks.length === 0) return
    const latest = clicks[0]
    if (latest?.url_id) {
      incrementClicks(latest.url_id)
    }
  }, [clicks, incrementClicks])

  return (
    <div className="space-y-6">
      {/* Welcome */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">
            Welcome back, {user?.name?.split(' ')[0]}
          </h1>
          <p className="text-gray-500 mt-1">Here's what's happening with your links.</p>
        </div>
        <div className="flex items-center gap-2 px-3 py-1.5 bg-white rounded-lg border border-gray-200">
          <span className={`w-2 h-2 rounded-full ${isConnected ? 'bg-green-500 animate-pulse' : 'bg-red-500'}`} />
          <span className="text-sm text-gray-600">
            {isConnected ? 'Live' : 'Connecting...'}
          </span>
        </div>
      </div>

      {/* Stats */}
      <StatsCards urls={urls} />

      {/* Create form */}
      <CreateUrlForm onCreate={create} />

      {/* URLs table */}
      <UrlsTable
        urls={urls}
        loading={loading}
        onDelete={remove}
        onToggle={toggle}
      />
    </div>
  )
}