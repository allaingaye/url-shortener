// src/types/stats.ts

export interface UrlStats {
  url: {
    id: number
    short_code: string
    short_url: string
    original_url: string
  }
  total_clicks: number
  unique_visitors: number
  by_device: Record<string, number>
  by_browser: Record<string, number>
  by_platform: Record<string, number>
  top_referers: Record<string, number>
  by_day: Array<{ date: string; count: number }>
}