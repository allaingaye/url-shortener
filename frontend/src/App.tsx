import { useState, useEffect } from 'react'
import { useLiveClicks } from './hooks/useLiveClicks'
import { me } from './lib/auth'
import type { User } from './types'

function App() {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)
  const { clicks, isConnected, error } = useLiveClicks(user?.id ?? null)

  useEffect(() => {
    me()
      .then(setUser)
      .catch(console.error)
      .finally(() => setLoading(false))
  }, [])

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <p className="text-gray-600">Loading...</p>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold mb-6">WebSocket Test</h1>

        <div className="card p-6 mb-6">
          <h2 className="text-lg font-semibold mb-2">Status</h2>
          <p>
            <strong>User:</strong> {user ? `${user.name} (${user.email})` : 'Not logged in'}
          </p>
          <p>
            <strong>WebSocket:</strong>{' '}
            <span className={isConnected ? 'text-green-600' : 'text-red-600'}>
              {isConnected ? '🟢 Connected' : '🔴 Disconnected'}
            </span>
          </p>
          {error && <p className="text-red-600 text-sm mt-2">Error: {error}</p>}
        </div>

        <div className="card p-6">
          <h2 className="text-lg font-semibold mb-2">
            Live Clicks ({clicks.length})
          </h2>
          {clicks.length === 0 ? (
            <p className="text-gray-500">
              No clicks yet. Hit <code className="bg-gray-100 px-1">/Zph5sRI</code> in another tab.
            </p>
          ) : (
            <ul className="space-y-2">
              {clicks.map((c) => (
                <li key={c.click_id} className="text-sm border-b pb-2">
                  <span className="font-mono text-blue-600">/{c.public_code}</span> · {c.browser} on {c.platform} ·{' '}
                  <span className="text-gray-500">{new Date(c.recorded_at).toLocaleTimeString()}</span>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </div>
  )
}

export default App