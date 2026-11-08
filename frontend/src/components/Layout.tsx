// src/components/Layout.tsx

import type { ReactNode } from 'react'
import { Sidebar } from './Sidebar'

export function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen bg-gray-50">
      <Sidebar />
      <main className="pl-64">
        <div className="max-w-6xl mx-auto p-8">{children}</div>
      </main>
    </div>
  )
}