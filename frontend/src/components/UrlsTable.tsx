// src/components/UrlsTable.tsx

import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import type { Url } from '../types'
import { useToast } from './Toast'

interface UrlsTableProps {
  urls: Url[]
  loading: boolean
  onDelete: (id: number) => Promise<void>
  onToggle: (id: number, isActive: boolean) => Promise<void>
}

export function UrlsTable({ urls, loading, onDelete, onToggle }: UrlsTableProps) {
  const { showToast } = useToast()
  const navigate = useNavigate()
  const [copiedId, setCopiedId] = useState<number | null>(null)
  const [qrUrl, setQrUrl] = useState<Url | null>(null)
  const [deleting, setDeleting] = useState<number | null>(null)

  async function copyToClipboard(url: Url) {
    try {
      await navigator.clipboard.writeText(url.short_url)
      setCopiedId(url.id)
      showToast('URL copied to clipboard', 'success')
      setTimeout(() => setCopiedId(null), 2000)
    } catch (err) {
      showToast('Failed to copy URL', 'error')
    }
  }

  async function handleDelete(url: Url) {
    if (!confirm(`Delete this URL? ${url.short_url}`)) return
    setDeleting(url.id)
    try {
      await onDelete(url.id)
      showToast('URL deleted', 'success')
    } catch (err) {
      showToast('Failed to delete URL', 'error')
    } finally {
      setDeleting(null)
    }
  }

  async function handleToggle(url: Url) {
    try {
      await onToggle(url.id, !url.is_active)
      showToast(url.is_active ? 'URL deactivated' : 'URL activated', 'success')
    } catch (err) {
      showToast('Failed to update URL', 'error')
    }
  }

  if (loading) {
    return (
      <div className="card p-12 text-center">
        <div className="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
        <p className="mt-4 text-gray-500">Loading URLs...</p>
      </div>
    )
  }

  if (urls.length === 0) {
    return (
      <div className="card p-12 text-center">
        <div className="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-2xl mb-4">
          <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
        </div>
        <h3 className="text-lg font-semibold text-gray-900 mb-1">No URLs yet</h3>
        <p className="text-gray-500 text-sm">Create your first short URL above to get started.</p>
      </div>
    )
  }

  return (
    <>
      <div className="card overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-100">
          <h2 className="text-lg font-semibold text-gray-900">Your URLs</h2>
          <p className="text-sm text-gray-500 mt-0.5">{urls.length} total</p>
        </div>

        <div className="divide-y divide-gray-100">
          {urls.map((url) => (
            <div key={url.id} className="px-6 py-4 hover:bg-gray-50 transition-colors">
              <div className="flex items-start justify-between gap-4">
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <a
                      href={url.short_url}
                      target="_blank"
                      rel="nofollow noopener noreferrer"
                      className="font-mono text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline truncate"
                    >
                      /{url.public_code}
                    </a>
                    {!url.is_active && (
                      <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        Inactive
                      </span>
                    )}
                  </div>
                  <p className="text-sm text-gray-500 truncate">{url.original_url}</p>
                </div>

                <div className="flex items-center gap-4 shrink-0">
                  <div className="text-right">
                    <p className="text-lg font-bold text-gray-900">{url.clicks_count}</p>
                    <p className="text-xs text-gray-400">clicks</p>
                  </div>

                  <div className="flex items-center gap-1">
                    {/* Analytics button — NEW */}
                    <button
                      type="button"
                      onClick={() => navigate(`/dashboard/urls/${url.id}`)}
                      title="View analytics"
                      className="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-blue-600 transition-colors"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                      </svg>
                    </button>

                    {/* Copy button */}
                    <button
                      type="button"
                      onClick={(e) => {
                        e.preventDefault()
                        e.stopPropagation()
                        copyToClipboard(url)
                      }}
                      title="Copy to clipboard"
                      className="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-blue-600 transition-colors"
                    >
                      {copiedId === url.id ? (
                        <svg className="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                      ) : (
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                      )}
                    </button>

                    {/* QR button */}
                    <button
                      type="button"
                      onClick={() => setQrUrl(url)}
                      title="Show QR code"
                      className="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-blue-600 transition-colors"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                      </svg>
                    </button>

                    {/* Toggle button */}
                    <button
                      type="button"
                      onClick={() => handleToggle(url)}
                      title={url.is_active ? 'Deactivate' : 'Activate'}
                      className="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-yellow-600 transition-colors"
                    >
                      {url.is_active ? (
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      ) : (
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      )}
                    </button>

                    {/* Delete button */}
                    <button
                      type="button"
                      onClick={() => handleDelete(url)}
                      disabled={deleting === url.id}
                      title="Delete"
                      className="p-2 rounded-lg hover:bg-red-50 text-gray-500 hover:text-red-600 transition-colors disabled:opacity-50"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* QR Modal */}
      {qrUrl && (
        <div
          className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
          onClick={() => setQrUrl(null)}
        >
          <div
            className="bg-white rounded-2xl p-6 max-w-sm w-full"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold">QR Code</h3>
              <button
                type="button"
                onClick={() => setQrUrl(null)}
                className="p-1 rounded-lg hover:bg-gray-100 text-gray-500"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div className="flex flex-col items-center">
              <img
                src={`/api/v1/urls/${qrUrl.id}/qr?format=svg&size=300`}
                alt={`QR code for ${qrUrl.short_url}`}
                className="w-64 h-64 border border-gray-200 rounded-lg"
              />
              <p className="mt-4 font-mono text-sm text-blue-600">{qrUrl.short_url}</p>
              <a
                href={`/api/v1/urls/${qrUrl.id}/qr?format=png&size=600`}
                download={`qr-${qrUrl.public_code}.png`}
                className="mt-3 text-sm text-blue-600 hover:text-blue-700 hover:underline"
              >
                Download PNG
              </a>
            </div>
          </div>
        </div>
      )}
    </>
  )
}