// src/pages/UrlAnalytics.tsx

import { Link, useParams } from 'react-router-dom'
import { Bar, Doughnut } from 'react-chartjs-2'
import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js'
import { useUrlStats } from '../hooks/useUrlStats'

ChartJS.register(ArcElement, BarElement, CategoryScale, LinearScale, Legend, Tooltip)

const COLORS = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4']

export function UrlAnalytics() {
  const { id } = useParams<{ id: string }>()
  const urlId = id ? parseInt(id, 10) : null
  const { stats, loading, error } = useUrlStats(urlId)

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
      </div>
    )
  }

  if (error || !stats) {
    return (
      <div className="card p-8 text-center">
        <p className="text-red-600">{error || 'No stats available'}</p>
        <Link to="/dashboard" className="btn-secondary mt-4 inline-flex">
          Back to dashboard
        </Link>
      </div>
    )
  }

  // ── Daily clicks chart data ──
  const dailyData = {
    labels: stats.by_day.map((d) => d.date),
    datasets: [
      {
        label: 'Clicks',
        data: stats.by_day.map((d) => d.count),
        backgroundColor: 'rgba(59, 130, 246, 0.6)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 1,
        borderRadius: 6,
      },
    ],
  }

  const dailyOptions = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { stepSize: 1 } },
      x: { grid: { display: false } },
    },
  }

  // ── Device breakdown doughnut ──
  const deviceEntries = Object.entries(stats.by_device)
  const deviceData = {
    labels: deviceEntries.map(([k]) => k),
    datasets: [
      {
        data: deviceEntries.map(([, v]) => v),
        backgroundColor: COLORS,
        borderWidth: 0,
      },
    ],
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <Link to="/dashboard" className="text-sm text-gray-500 hover:text-gray-900 inline-flex items-center gap-1 mb-3">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to dashboard
        </Link>
        <h1 className="text-3xl font-bold text-gray-900">Analytics</h1>
        <div className="mt-2 flex items-center gap-2">
          <a
            href={stats.url.short_url}
            target="_blank"
            rel="noopener noreferrer"
            className="font-mono text-sm text-blue-600 hover:underline"
          >
            {stats.url.short_url}
          </a>
          <span className="text-gray-300">→</span>
          <span className="text-sm text-gray-500 truncate">{stats.url.original_url}</span>
        </div>
      </div>

      {/* Summary cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div className="card p-6">
          <p className="text-sm text-gray-500">Total clicks</p>
          <p className="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mt-1">
            {stats.total_clicks}
          </p>
        </div>
        <div className="card p-6">
          <p className="text-sm text-gray-500">Unique visitors</p>
          <p className="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mt-1">
            {stats.unique_visitors}
          </p>
        </div>
      </div>

      {/* Daily chart */}
      <div className="card p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Clicks per day</h2>
        {stats.by_day.length > 0 ? (
          <Bar data={dailyData} options={dailyOptions} height={120} />
        ) : (
          <p className="text-gray-400 text-sm py-8 text-center">No clicks yet</p>
        )}
      </div>

      {/* Breakdowns */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="card p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">By device</h2>
          {deviceEntries.length > 0 ? (
            <div className="h-64">
              <Doughnut
                data={deviceData}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: { legend: { position: 'bottom' } },
                }}
              />
            </div>
          ) : (
            <p className="text-gray-400 text-sm">No data</p>
          )}
        </div>

        <div className="card p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">By browser</h2>
          <BreakdownList data={stats.by_browser} />
        </div>

        <div className="card p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">By platform</h2>
          <BreakdownList data={stats.by_platform} />
        </div>

        <div className="card p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Top referrers</h2>
          <BreakdownList data={stats.top_referers} emptyMessage="No referrers yet" />
        </div>
      </div>
    </div>
  )
}

function BreakdownList({
  data,
  emptyMessage = 'No data',
}: {
  data: Record<string, number>
  emptyMessage?: string
}) {
  const entries = Object.entries(data)
  const total = entries.reduce((sum, [, v]) => sum + v, 0)

  if (entries.length === 0) {
    return <p className="text-gray-400 text-sm">{emptyMessage}</p>
  }

  return (
    <ul className="space-y-3">
      {entries.slice(0, 6).map(([label, count], index) => {
        const percent = total > 0 ? (count / total) * 100 : 0
        return (
          <li key={label}>
            <div className="flex items-center justify-between text-sm mb-1">
              <span className="text-gray-700 truncate max-w-[60%]">{label || '(unknown)'}</span>
              <span className="text-gray-500">
                {count} <span className="text-gray-400">({percent.toFixed(0)}%)</span>
              </span>
            </div>
            <div className="h-2 bg-gray-100 rounded-full overflow-hidden">
              <div
                className="h-full rounded-full transition-all"
                style={{
                  width: `${percent}%`,
                  backgroundColor: COLORS[index % COLORS.length],
                }}
              />
            </div>
          </li>
        )
      })}
    </ul>
  )
}