import debug from '@/utils/debug'

import api from './api'

export const dashboardService = {
  async getDashboardOverview() {
    const response = await api.get('/dashboard')
    return response.data
  },

  async getMonthlySummary(period = 'current_month') {
    const response = await api.get('/dashboard/monthly-summary', {
      params: { period }
    })
    return response.data
  },

  async getYearlySummary() {
    const response = await api.get('/dashboard/yearly-summary')
    return response.data
  },

  async getTrends(months = 6) {
    try {
      console.log('Making API call to /dashboard/trends with params:', { months })
      const response = await api.get('/dashboard/trends', {
        params: { months }
      })
      console.log('API response for trends:', response)
      return response.data
    } catch (error) {
      console.error('API error in getTrends:', error)
      console.error('Error response:', error.response)
      throw error
    }
  },

  async getDailySpending() {
    const response = await api.get('/dashboard/daily-spending')
    return response.data
  }
}
