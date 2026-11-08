// src/components/StatsCards.tsx

import type { Url } from '../types'

interface StatsCardsProps {
  urls: Url[]
}

export function StatsCards({ urls }: StatsCardsProps) {
  const totalUrls = urls.length
  const totalClicks = urls.reduce((sum, u) => sum + u.clicks_count, 0)
  const activeUrls = urls.filter((u) => u.is_active).length

  const stats = [
    {
      label: 'Total URLs',
      value: totalUrls,
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
        </svg>
      ),
      gradient: 'from-blue-500 to-blue-600',
      bg: 'bg-blue-50',
      text: 'text-blue-600',
    },
    {
      label: 'Total Clicks',
      value: totalClicks,
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
      ),
      gradient: 'from-purple-500 to-indigo-600',
      bg: 'bg-indigo-50',
      text: 'text-indigo-600',
    },
    {
      label: 'Active URLs',
      value: activeUrls,
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      ),
      gradient: 'from-green-500 to-emerald-600',
      bg: 'bg-emerald-50',
      text: 'text-emerald-600',
    },
  ]

  return (
    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
      {stats.map((stat) => (
        <div key={stat.label} className="card p-6">
          <div className="flex items-center justify-between mb-3">
            <div className={`w-10 h-10 ${stat.bg} rounded-xl flex items-center justify-center ${stat.text}`}>
              {stat.icon}
            </div>
            <span className={`text-3xl font-bold bg-gradient-to-r ${stat.gradient} bg-clip-text text-transparent`}>
              {stat.value}
            </span>
          </div>
          <p className="text-sm text-gray-500">{stat.label}</p>
        </div>
      ))}
    </div>
  )
}