import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { usePerformance } from '@/composables/usePerformance'

// Create axios instance with optimized defaults
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request cache for GET requests
const requestCache = new Map()
const CACHE_TTL = 5 * 60 * 1000 // 5 minutes

// Performance tracking
const { trackApiCall } = usePerformance()

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    
    // Add auth token
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }

    // Add request timestamp for performance tracking
    config.metadata = { startTime: Date.now() }

    // Check cache for GET requests
    if (config.method === 'get' && config.cache !== false) {
      const cacheKey = `${config.url}?${JSON.stringify(config.params)}`
      const cachedResponse = requestCache.get(cacheKey)
      
      if (cachedResponse && Date.now() - cachedResponse.timestamp < CACHE_TTL) {
        // Return cached response as a resolved promise
        return Promise.reject({
          __cached: true,
          data: cachedResponse.data
        })
      }
    }

    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => {
    const config = response.config
    const duration = Date.now() - config.metadata.startTime

    // Track API performance
    trackApiCall(config.url, duration, response.status)

    // Cache GET responses
    if (config.method === 'get' && config.cache !== false) {
      const cacheKey = `${config.url}?${JSON.stringify(config.params)}`
      requestCache.set(cacheKey, {
        data: response.data,
        timestamp: Date.now()
      })

      // Clean old cache entries
      if (requestCache.size > 100) {
        const oldestKey = requestCache.keys().next().value
        requestCache.delete(oldestKey)
      }
    }

    return response
  },
  (error) => {
    // Handle cached responses
    if (error.__cached) {
      return Promise.resolve({ data: error.data })
    }

    const config = error.config
    const duration = Date.now() - (config?.metadata?.startTime || Date.now())

    // Track API errors
    trackApiCall(config?.url || 'unknown', duration, error.response?.status || 0)

    // Handle auth errors
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
      window.location.href = '/login'
    }

    return Promise.reject(error)
  }
)

// Optimized request methods
export const apiService = {
  // GET with caching
  get: (url, params = {}, options = {}) => {
    return api.get(url, { 
      params, 
      cache: options.cache !== false,
      ...options 
    })
  },

  // POST with optimistic updates support
  post: (url, data = {}, options = {}) => {
    return api.post(url, data, { cache: false, ...options })
  },

  // PUT with optimistic updates support
  put: (url, data = {}, options = {}) => {
    return api.put(url, data, { cache: false, ...options })
  },

  // DELETE with cache invalidation
  delete: (url, options = {}) => {
    // Invalidate related cache entries
    const urlPattern = url.split('/')[0]
    for (const [key] of requestCache.entries()) {
      if (key.includes(urlPattern)) {
        requestCache.delete(key)
      }
    }
    
    return api.delete(url, { cache: false, ...options })
  },

  // Batch requests
  batch: async (requests) => {
    const promises = requests.map(({ method, url, data, params }) => {
      switch (method.toLowerCase()) {
        case 'get':
          return apiService.get(url, params)
        case 'post':
          return apiService.post(url, data)
        case 'put':
          return apiService.put(url, data)
        case 'delete':
          return apiService.delete(url)
        default:
          throw new Error(`Unsupported method: ${method}`)
      }
    })

    return Promise.allSettled(promises)
  },

  // Clear cache
  clearCache: (pattern = null) => {
    if (pattern) {
      for (const [key] of requestCache.entries()) {
        if (key.includes(pattern)) {
          requestCache.delete(key)
        }
      }
    } else {
      requestCache.clear()
    }
  },

  // Get cache stats
  getCacheStats: () => ({
    size: requestCache.size,
    keys: Array.from(requestCache.keys())
  })
}

export default api