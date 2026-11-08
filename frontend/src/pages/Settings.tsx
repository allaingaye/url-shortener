// src/pages/Settings.tsx

import { useAuth } from '../hooks/useAuth'

export function Settings() {
  const { user } = useAuth()

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Settings</h1>
        <p className="text-gray-500 mt-1">Manage your account preferences.</p>
      </div>

      <div className="card p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Account</h2>
        <dl className="space-y-3">
          <div>
            <dt className="text-sm font-medium text-gray-500">Name</dt>
            <dd className="text-sm text-gray-900 mt-0.5">{user?.name}</dd>
          </div>
          <div>
            <dt className="text-sm font-medium text-gray-500">Email</dt>
            <dd className="text-sm text-gray-900 mt-0.5">{user?.email}</dd>
          </div>
          <div>
            <dt className="text-sm font-medium text-gray-500">Member since</dt>
            <dd className="text-sm text-gray-900 mt-0.5">
              {user?.created_at ? new Date(user.created_at).toLocaleDateString() : '—'}
            </dd>
          </div>
        </dl>
      </div>

      <div className="card p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-2">Coming soon</h2>
        <p className="text-sm text-gray-500">
          API key management, custom domains, team collaboration, and webhook integrations.
        </p>
      </div>
    </div>
  )
}