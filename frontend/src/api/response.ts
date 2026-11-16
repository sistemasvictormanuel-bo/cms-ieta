import type { ApiCollection, PaginationMeta } from './types'

export function unwrapResource<T>(response: unknown): T | null {
  if (!response || typeof response !== 'object') return null
  const payload = response as { data?: unknown }
  return ('data' in payload ? payload.data : response) as T | null
}

export function unwrapCollection<T>(response: unknown): ApiCollection<T> {
  if (Array.isArray(response)) return { data: response as T[] }
  if (!response || typeof response !== 'object') return { data: [] }

  const payload = response as Partial<ApiCollection<T>>
  return {
    data: Array.isArray(payload.data) ? payload.data : [],
    meta: payload.meta,
    links: payload.links,
  }
}

export function paginationHasMore(meta: PaginationMeta | undefined, page: number, itemCount: number) {
  if (typeof meta?.last_page === 'number') return page < meta.last_page
  if (typeof meta?.total === 'number' && typeof meta?.per_page === 'number') {
    return page * meta.per_page < meta.total
  }
  return itemCount > 0
}

export function mediaUrl(path?: string | null) {
  if (!path) return undefined

  if (/^https?:\/\//i.test(path)) {
    return path
  }

  const baseUrl = import.meta.env.VITE_API_URL
  if (!baseUrl) return path

  try {
    const url = new URL(path, baseUrl)
    return url.protocol === 'http:' || url.protocol === 'https:' ? url.href : undefined
  } catch {
    return path
  }
}
