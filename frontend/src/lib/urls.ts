// src/lib/urls.ts

import api from './api'
import type { Url, PaginatedResponse } from '../types'

/**
 * Fetch the authenticated user's URLs.
 */
export async function fetchUrls(page = 1, perPage = 50): Promise<PaginatedResponse<Url>> {
  const response = await api.get('/urls', {
    params: { page, per_page: perPage },
  })
  return response.data
}

/**
 * Create a new shortened URL.
 */
export async function createUrl(originalUrl: string, customAlias?: string): Promise<Url> {
  const response = await api.post('/urls', {
    original_url: originalUrl,
    custom_alias: customAlias || null,
  })
  return response.data.data
}

/**
 * Delete a URL by ID.
 */
export async function deleteUrl(id: number): Promise<void> {
  await api.delete(`/urls/${id}`)
}

/**
 * Toggle URL active status.
 */
export async function toggleUrl(id: number, isActive: boolean): Promise<Url> {
  const response = await api.patch(`/urls/${id}`, { is_active: isActive })
  return response.data.data
}

/**
 * Get QR code URL for a URL.
 */
export function getQrCodeUrl(id: number, format = 'png', size = 300): string {
  return `/api/v1/urls/${id}/qr?format=${format}&size=${size}`
}