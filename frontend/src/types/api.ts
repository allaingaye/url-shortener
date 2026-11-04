// src/types/api.ts

/**
 * Shared TypeScript types for API responses.
 */

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface Url {
  id: number;
  original_url: string;
  short_code: string;
  custom_alias: string | null;
  public_code: string;
  short_url: string;
  clicks_count: number;
  is_active: boolean;
  expires_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface Click {
  id: number;
  url_id: number;
  ip_address: string;
  user_agent: string;
  referer: string | null;
  device: string | null;
  browser: string | null;
  platform: string | null;
  country: string | null;
  created_at: string;
  updated_at: string;
}

export interface ClickEvent {
  click_id: number;
  url_id: number;
  public_code: string;
  browser: string | null;
  platform: string | null;
  device: string | null;
  referer: string | null;
  recorded_at: string;
}

export interface AuthResponse {
  user: User;
  token: string;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
  };
}