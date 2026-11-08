// src/lib/stats.ts

import api from './api'
import type { UrlStats } from '../types/stats'

export async function fetchUrlStats(id: number): Promise<UrlStats> {
  const response = await api.get(`/urls/${id}/stats`)
  return response.data.data
}