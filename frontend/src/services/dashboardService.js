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
    const response = await api.get('/dashboard/trends', {
      params: { months }
    })
    return response.data
  },

  async getDailySpending() {
    const response = await api.get('/dashboard/daily-spending')
    return response.data
  }
}