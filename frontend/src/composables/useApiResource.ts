import { computed, shallowRef, watch, type ComputedRef } from 'vue'
import { unwrapResource } from '@/api/response'

export function useApiResource<T>(
  resourceId: ComputedRef<string>,
  fetchResource: (id: string) => Promise<unknown>,
) {
  const data = shallowRef<T | null>(null)
  const loading = shallowRef(true)
  const error = shallowRef(false)
  const notFound = computed(() => !loading.value && !error.value && data.value === null)
  let requestSequence = 0

  async function load(id = resourceId.value) {
    const sequence = ++requestSequence
    data.value = null
    error.value = false
    loading.value = true

    if (!id.trim()) {
      loading.value = false
      return
    }

    try {
      const result = unwrapResource<T>(await fetchResource(id))
      if (sequence === requestSequence) data.value = result
    } catch {
      if (sequence === requestSequence) error.value = true
    } finally {
      if (sequence === requestSequence) loading.value = false
    }
  }

  watch(resourceId, (id) => void load(id), { immediate: true })

  return { data, loading, error, notFound, reload: () => load() }
}
