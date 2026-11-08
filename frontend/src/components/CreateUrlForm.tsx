// src/components/CreateUrlForm.tsx

import { FormEvent, useState } from 'react'
import type { Url } from '../types'

interface CreateUrlFormProps {
  onCreate: (originalUrl: string) => Promise<Url>
}

export function CreateUrlForm({ onCreate }: CreateUrlFormProps) {
  const [url, setUrl] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState<string | null>(null)

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    if (!url.trim()) return

    setError(null)
    setSuccess(null)
    setSubmitting(true)

    try {
      const newUrl = await onCreate(url.trim())
      setSuccess(`Created: ${newUrl.short_url}`)
      setUrl('')
      setTimeout(() => setSuccess(null), 3000)
    } catch (err: any) {
      const message =
        err?.response?.data?.errors?.original_url?.[0] ||
        err?.response?.data?.message ||
        'Failed to create URL'
      setError(message)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="card p-6">
      <div className="mb-4">
        <h2 className="text-lg font-semibold text-gray-900">Create new short URL</h2>
        <p className="text-sm text-gray-500 mt-0.5">Paste any URL to shorten it</p>
      </div>

      <form onSubmit={handleSubmit} className="flex flex-col sm:flex-row gap-3">
        <input
          type="url"
          className="input flex-1"
          placeholder="https://example.com/very/long/url"
          value={url}
          onChange={(e) => setUrl(e.target.value)}
          required
        />
        <button type="submit" className="btn-primary whitespace-nowrap" disabled={submitting}>
          {submitting ? 'Creating...' : 'Shorten'}
        </button>
      </form>

      {error && (
        <p className="mt-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
          {error}
        </p>
      )}
      {success && (
        <p className="mt-3 text-sm text-green-600 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
          {success}
        </p>
      )}
    </div>
  )
}