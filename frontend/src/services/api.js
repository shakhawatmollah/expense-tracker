import axios from 'axios'
import storage from '@/utils/storage'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
const API_VERSION = 'v1'

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-API-Version': API_VERSION
  }
})

// Request interceptor to add auth token and handle v1 prefix
api.interceptors.request.use(
  config => {
    const token = storage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // Add v1 prefix for non-auth routes
    if (config.url && !config.url.startsWith('/auth') && !config.url.startsWith('/v1')) {
      config.url = `/v1${config.url}`
    }
    
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle auth errors
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      const authStore = useAuthStore()
      authStore.logout()
      
      // Use router for navigation instead of window.location
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

export default api
