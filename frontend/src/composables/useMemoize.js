import { computed, ref, watch } from 'vue'

/**
 * Memoization composable for expensive computations
 * @param {Function} computeFn - The function to memoize
 * @param {Array} dependencies - Array of reactive dependencies
 * @param {Object} options - Options for cache management
 */
export function useMemoize(computeFn, dependencies = [], options = {}) {
  const {
    maxCacheSize = 100,
    ttl = 300000, // 5 minutes default TTL
    keyGenerator = null
  } = options

  const cache = ref(new Map())
  const lastAccess = ref(new Map())

  // Generate cache key from dependencies
  const generateKey =
    keyGenerator ||
    (() => {
      return JSON.stringify(
        dependencies.map(dep => (typeof dep === 'object' && dep.value !== undefined ? dep.value : dep))
      )
    })

  // Clean expired entries
  const cleanCache = () => {
    const now = Date.now()
    const expiredKeys = []

    for (const [key, timestamp] of lastAccess.value.entries()) {
      if (now - timestamp > ttl) {
        expiredKeys.push(key)
      }
    }

    expiredKeys.forEach(key => {
      cache.value.delete(key)
      lastAccess.value.delete(key)
    })
  }

  // Manage cache size
  const manageCacheSize = () => {
    if (cache.value.size > maxCacheSize) {
      // Remove oldest entries
      const entries = Array.from(lastAccess.value.entries()).sort((a, b) => a[1] - b[1])

      const toRemove = entries.slice(0, cache.value.size - maxCacheSize)
      toRemove.forEach(([key]) => {
        cache.value.delete(key)
        lastAccess.value.delete(key)
      })
    }
  }

  const memoizedValue = computed(() => {
    cleanCache()

    const key = generateKey()
    const now = Date.now()

    if (cache.value.has(key)) {
      lastAccess.value.set(key, now)
      return cache.value.get(key)
    }

    const result = computeFn()

    cache.value.set(key, result)
    lastAccess.value.set(key, now)

    manageCacheSize()

    return result
  })

  // Watch dependencies for changes
  watch(
    dependencies,
    () => {
      // Dependencies changed, cache will be invalidated on next access
    },
    { deep: true }
  )

  // Clear cache method
  const clearCache = () => {
    cache.value.clear()
    lastAccess.value.clear()
  }

  // Get cache stats
  const getCacheStats = () => ({
    size: cache.value.size,
    maxSize: maxCacheSize,
    hitRate: cache.value.size > 0 ? 1 : 0 // Simplified hit rate
  })

  return {
    value: memoizedValue,
    clearCache,
    getCacheStats
  }
}
