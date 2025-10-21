import api from '@/services/api'

export const analyticsService = {
  // Get comprehensive analytics dashboard data
  async getDashboard(period = 'monthly') {
    const response = await api.get('/analytics/dashboard', {
      params: { period }
    })
    return response.data
  },

  // Get spending patterns
  async getPatterns(type = null) {
    const response = await api.get('/analytics/patterns', {
      params: type ? { type } : {}
    })
    return response.data
  },

  // Get financial health score
  async getFinancialHealth(period = 'monthly') {
    const response = await api.get('/analytics/financial-health', {
      params: { period }
    })
    return response.data
  },

  // Get user insights
  async getInsights(period = 'monthly', type = null) {
    const response = await api.get('/analytics/insights', {
      params: { period, ...(type && { type }) }
    })
    return response.data
  },

  // Get spending forecasts
  async getForecasts(period = 'monthly') {
    const response = await api.get('/analytics/forecasts', {
      params: { period }
    })
    return response.data
  },

  // Get recommendations
  async getRecommendations(period = 'monthly') {
    const response = await api.get('/analytics/recommendations', {
      params: { period }
    })
    return response.data
  },

  // Get spending trends
  async getTrends(period = 'monthly', categoryId = null) {
    const response = await api.get('/analytics/trends', {
      params: { period, ...(categoryId && { category_id: categoryId }) }
    })
    return response.data
  },

  // Refresh analytics (force regeneration)
  async refreshAnalytics(period = 'monthly') {
    const response = await api.post('/analytics/refresh', { period })
    return response.data
  }
}

export default analyticsService
