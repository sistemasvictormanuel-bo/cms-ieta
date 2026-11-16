import { computed, ref, shallowRef } from 'vue'
import { paginationHasMore, unwrapCollection } from '@/api/response'

export interface PaginatedResourceOptions {
  autoFetchAll?: boolean
}

export function usePaginatedResource<T>(
  fetchPage: (page: number) => Promise<unknown>,
  options: PaginatedResourceOptions = {},
) {
  const items = shallowRef<T[]>([])
  const page = ref(0)
  const loading = ref(false)
  const loadingMore = ref(false)
  const isFetchingAll = ref(false)
  const error = ref(false)
  const hasMore = ref(false)
  const total = ref<number | null>(null)
  const isEmpty = computed(() => !loading.value && !error.value && items.value.length === 0)

  async function load(nextPage: number) {
    if (loading.value || loadingMore.value) return
    if (nextPage === 1) loading.value = true
    else loadingMore.value = true
    error.value = false

    try {
      const response = unwrapCollection<T>(await fetchPage(nextPage))
      items.value = nextPage === 1 ? response.data : [...items.value, ...response.data]
      page.value = response.meta?.current_page ?? nextPage
      hasMore.value = paginationHasMore(response.meta, page.value, response.data.length)
      if (typeof response.meta?.total === 'number') {
        total.value = response.meta.total
      }

      // If autoFetchAll is enabled and more pages exist, fetch remaining pages smoothly in background
      if (options.autoFetchAll && hasMore.value && !isFetchingAll.value) {
        void fetchAllRemaining(response.meta?.last_page)
      }
    } catch {
      error.value = true
    } finally {
      loading.value = false
      loadingMore.value = false
    }
  }

  async function fetchAllRemaining(lastPage?: number | null) {
    if (isFetchingAll.value) return
    isFetchingAll.value = true
    try {
      let currentPage = page.value
      const maxPages = lastPage && lastPage > currentPage ? lastPage : 30
      while (currentPage < maxPages && hasMore.value) {
        const next = currentPage + 1
        const response = unwrapCollection<T>(await fetchPage(next))
        if (!response.data || response.data.length === 0) {
          hasMore.value = false
          break
        }
        // Deduplicate items by ID if available
        const currentIds = new Set(items.value.map((i: any) => i?.id).filter(Boolean))
        const newItems = currentIds.size > 0
          ? response.data.filter((i: any) => !i?.id || !currentIds.has(i.id))
          : response.data

        items.value = [...items.value, ...newItems]
        currentPage = response.meta?.current_page ?? next
        page.value = currentPage
        hasMore.value = paginationHasMore(response.meta, currentPage, response.data.length)
      }
    } catch {
      // Graceful error for background fetching
    } finally {
      isFetchingAll.value = false
    }
  }

  function refresh() {
    return load(1)
  }

  function loadMore() {
    if (!hasMore.value) return Promise.resolve()
    return load(page.value + 1)
  }

  return { items, page, loading, loadingMore, isFetchingAll, error, hasMore, total, isEmpty, refresh, loadMore }
}
