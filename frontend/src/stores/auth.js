import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'
import storage from '@/utils/storage'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(storage.getItem('token'))
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  const initializeAuth = () => {
    try {
      const storedToken = storage.getItem('token')
      const storedUser = storage.getItem('user')

      if (storedToken && storedUser) {
        token.value = storedToken
        user.value = storedUser
      } else {
        token.value = null
        user.value = null
      }
    } catch (error) {
      console.error('Error initializing auth:', error)
      // Clear all auth data on error
      token.value = null
      user.value = null
      storage.removeItem('token')
      storage.removeItem('user')
    }
  }

  const login = async credentials => {
    loading.value = true
    error.value = null

    try {
      const response = await authService.login(credentials)

      // Handle response structure: { success, message, data: { token, user } }
      const tokenValue = response.data?.token || response.token
      const userData = response.data?.user || response.user

      if (!tokenValue || !userData) {
        throw new Error('Invalid response structure: missing token or user data')
      }

      token.value = tokenValue
      user.value = userData

      storage.setItem('token', tokenValue)
      storage.setItem('user', userData)

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const register = async userData => {
    loading.value = true
    error.value = null

    try {
      const response = await authService.register(userData)

      // Handle response structure: { success, message, data: { token, user } }
      const tokenValue = response.data?.token || response.token
      const userDataValue = response.data?.user || response.user

      if (!tokenValue || !userDataValue) {
        throw new Error('Invalid response structure: missing token or user data')
      }

      token.value = tokenValue
      user.value = userDataValue

      storage.setItem('token', tokenValue)
      storage.setItem('user', userDataValue)

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      await authService.logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      token.value = null
      user.value = null
      storage.removeItem('token')
      storage.removeItem('user')
    }
  }

  const fetchUser = async () => {
    if (!token.value) return

    try {
      const response = await authService.me()
      user.value = response.user
      
      // Safely store user data
      storage.setItem('user', response.user)
    } catch (err) {
      console.error('Fetch user error:', err)
      // If fetch fails, logout to clear invalid auth state
      await logout()
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    initializeAuth,
    login,
    register,
    logout,
    fetchUser
  }
})
