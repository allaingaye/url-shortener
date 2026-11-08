// src/pages/Dashboard.tsx

import { useAuth } from '../hooks/useAuth'
import { useLiveClicks } from '../hooks/useLiveClicks'

export function Dashboard() {
  const { user } = useAuth()
  const { clicks, isConnected, error } = useLiveClicks(user?.id ?? null)

  return (
    <div className="space-y-6">
      {/* Welcome */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Welcome back, {user?.name?.split(' ')[0]}</h1>
        <p className="text-gray-500 mt-1">Here's what's happening with your links.</p>
      </div>

      {/* Connection status */}
      <div className="card p-4 flex items-center justify-between">
        <div className="flex items-center gap-3">
          <span className={`inline-block w-2.5 h-2.5 rounded-full ${isConnected ? 'bg-green-500 animate-pulse' : 'bg-red-500'}`} />
          <span className="text-sm text-gray-700">
            {isConnected ? 'Live updates connected' : 'Connecting...'}
          </span>
        </div>
        {error && <span className="text-xs text-red-500">{error}</span>}
      </div>

      {/* Live clicks */}
      <div className="card p-6">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-semibold text-gray-900">Live Clicks</h2>
          <span className="text-sm text-gray-400">{clicks.length} recent</span>
        </div>

        {clicks.length === 0 ? (
          <div className="text-center py-12">
            <div className="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl mb-3">
              <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <p className="text-gray-500">No clicks yet</p>
            <p className="text-sm text-gray-400 mt-1">Click one of your short URLs to see it appear here in real time</p>
          </div>
        ) : (
          <ul className="divide-y divide-gray-100">
            {clicks.map((click) => (
              <li key={click.click_id} className="py-3 flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg className="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                    </svg>
                  </div>
                  <div>
                    <p className="font-mono text-sm text-blue-600">/{click.public_code}</p>
                    <p className="text-xs text-gray-500">{click.browser} · {click.platform}</p>
                  </div>
                </div>
                <span className="text-xs text-gray-400">{new Date(click.recorded_at).toLocaleTimeString()}</span>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  )
}