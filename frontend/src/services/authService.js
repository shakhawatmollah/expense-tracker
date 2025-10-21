import api from './api'
import storage from '@/utils/storage'

export const authService = {
  async login(credentials) {
    const response = await api.post('/auth/login', credentials)
    // Store the token in localStorage using safe storage utility
    if (response.data?.data?.token) {
      storage.setItem('token', response.data.data.token)
      storage.setItem('user', response.data.data.user)
    }
    return response.data
  },

  async register(userData) {
    const response = await api.post('/auth/register', userData)
    // Store the token in localStorage using safe storage utility
    if (response.data?.data?.token) {
      storage.setItem('token', response.data.data.token)
      storage.setItem('user', response.data.data.user)
    }
    return response.data
  },

  async logout() {
    try {
      const response = await api.post('/auth/logout')
      // Clear stored data after successful logout
      storage.removeItem('token')
      storage.removeItem('user')
      return response.data
    } catch (error) {
      // Clear stored data even if logout fails (token might be expired)
      storage.removeItem('token')
      storage.removeItem('user')
      throw error
    }
  },

  async me() {
    const response = await api.get('/auth/me')
    return response.data
  }
}
