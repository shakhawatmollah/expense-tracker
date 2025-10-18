import api from './api'

export const authService = {
  async login(credentials) {
    const response = await api.post('/auth/login', credentials)
    // Store the token in localStorage
    if (response.data.token) {
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
    }
    return response.data
  },

  async register(userData) {
    const response = await api.post('/auth/register', userData)
    // Store the token in localStorage
    if (response.data.token) {
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
    }
    return response.data
  },

  async logout() {
    try {
      const response = await api.post('/auth/logout')
      // Clear stored data after successful logout
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      return response.data
    } catch (error) {
      // Clear stored data even if logout fails (token might be expired)
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      throw error
    }
  },

  async me() {
    const response = await api.get('/auth/me')
    return response.data
  }
}