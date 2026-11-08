// src/pages/AllAnalytics.tsx

import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { Bar } from 'react-chartjs-2'
import {
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js'
import { useUrls } from '../hooks/useUrls'
import { fetchUrlStats } from '../lib/stats'
import type { UrlStats } from '../types/stats'

ChartJS.register(BarElement, CategoryScale, LinearScale, Legend, Tooltip)

export function AllAnalytics() {
  const { urls, loading } = useUrls()
  const [allStats, setAllStats] = useState<UrlStats[]>([])
  const [statsLoading, setStatsLoading] = useState(true)

  // Fetch stats for every URL
  useEffect(() => {
    if (loading || urls.length === 0) {
      setStatsLoading(loading)
      return
    }

    Promise.all(urls.map((u) => fetchUrlStats(u.id).catch(() => null)))
      .then((results) => {
        setAllStats(results.filter((r): r is UrlStats => r !== null))
      })
      .finally(() => setStatsLoading(false))
  }, [urls, loading])

  // Aggregate across all URLs
  const aggregated = useMemo(() => {
    const byDay: Record<string, number> = {}
    let totalClicks = 0
    let totalVisitors = 0

    for (const stats of allStats) {
      totalClicks += stats.total_clicks
      totalVisitors += stats.unique_visitors

      // by_day is an ARRAY of { date, count } objects
      for (const entry of stats.by_day) {
        byDay[entry.date] = (byDay[entry.date] || 0) + entry.count
      }
    }

    return { totalClicks, totalVisitors, byDay }
  }, [allStats])

  const sortedDays = Object.keys(aggregated.byDay).sort()

  const chartData = {
    labels: sortedDays,
    datasets: [
      {
        label: 'Clicks',
        data: sortedDays.map((d) => aggregated.byDay[d]),
        backgroundColor: 'rgba(59, 130, 246, 0.6)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 1,
        borderRadius: 6,
      },
    ],
  }

  const topUrls = [...allStats].sort((a, b) => b.total_clicks - a.total_clicks).slice(0, 5)

  if (loading || statsLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Analytics Overview</h1>
        <p className="text-gray-500 mt-1">Aggregate performance across all your URLs.</p>
      </div>

      {/* Summary cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div className="card p-6">
          <p className="text-sm text-gray-500">Total clicks</p>
          <p className="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mt-1">
            {aggregated.totalClicks}
          </p>
        </div>
        <div className="card p-6">
          <p className="text-sm text-gray-500">Unique visitors</p>
          <p className="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mt-1">
            {aggregated.totalVisitors}
          </p>
        </div>
      </div>

      {/* Chart */}
      <div className="card p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Clicks per day</h2>
        {sortedDays.length > 0 ? (
          <Bar data={chartData} height={120} />
        ) : (
          <p className="text-gray-400 text-sm py-8 text-center">No click data yet</p>
        )}
      </div>

      {/* Top URLs */}
      <div className="card p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Top performing URLs</h2>
        {topUrls.length === 0 ? (
          <p className="text-gray-400 text-sm">No URLs yet</p>
        ) : (
          <div className="space-y-3">
            {topUrls.map((s, i) => (
              <Link
                key={s.url.id}
                to={`/dashboard/urls/${s.url.id}`}
                className="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors"
              >
                <div className="flex items-center gap-3">
                  <span className="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">
                    {i + 1}
                  </span>
                  <div>
                    <p className="font-mono text-sm text-blue-600">/{s.url.short_code}</p>
                    <p className="text-xs text-gray-500 truncate max-w-xs">
                      {s.url.original_url}
                    </p>
                  </div>
                </div>
                <span className="text-lg font-bold text-gray-900">{s.total_clicks}</span>
              </Link>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}