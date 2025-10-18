import api from './api'

export const budgetService = {
  // Get all budgets with filters and pagination
  async getBudgets(params = {}) {
    const response = await api.get('/budgets', { params })
    return response.data
  },

  // Get budget by ID
  async getBudget(id) {
    const response = await api.get(`/budgets/${id}`)
    return response.data
  },

  // Create new budget
  async createBudget(budgetData) {
    const response = await api.post('/budgets', budgetData)
    return response.data
  },

  // Update existing budget
  async updateBudget(id, budgetData) {
    const response = await api.put(`/budgets/${id}`, budgetData)
    return response.data
  },

  // Delete budget
  async deleteBudget(id) {
    const response = await api.delete(`/budgets/${id}`)
    return response.data
  },

  // Get current active budgets
  async getCurrentBudgets() {
    const response = await api.get('/budgets/current')
    return response.data
  },

  // Get budget summary with analytics
  async getBudgetSummary() {
    try {
      console.log('🔍 [budgetService] getBudgetSummary called')
      const response = await api.get('/budgets/summary')
      console.log('✅ [budgetService] getBudgetSummary response:', response)
      return response.data
    } catch (error) {
      console.error('❌ [budgetService] getBudgetSummary error:', error)
      throw error
    }
  },

  // Get budget alerts
  async getBudgetAlerts() {
    try {
      console.log('🔍 [budgetService] getBudgetAlerts called')
      const response = await api.get('/budgets/alerts')
      console.log('✅ [budgetService] getBudgetAlerts response:', response)
      return response.data
    } catch (error) {
      console.error('❌ [budgetService] getBudgetAlerts error:', error)
      throw error
    }
  },

  // Get budget analytics
  async getBudgetAnalytics(period = 'monthly') {
    const response = await api.get('/budgets/analytics', { 
      params: { period } 
    })
    return response.data
  },

  // Get available budget periods
  async getBudgetPeriods() {
    const response = await api.get('/budgets/periods')
    return response.data
  },

  // Get budgets by period
  async getBudgetsByPeriod(period) {
    const response = await api.get('/budgets/by-period', { 
      params: { period } 
    })
    return response.data
  },

  // Search budgets with filters
  async searchBudgets(filters = {}) {
    const response = await api.get('/budgets/search', { 
      params: filters 
    })
    return response.data
  },

  // Get budgets by category
  async getBudgetsByCategory(categoryId) {
    const response = await api.get(`/budgets/category/${categoryId}`)
    return response.data
  },

  // Duplicate budget
  async duplicateBudget(id, overrides = {}) {
    const response = await api.post(`/budgets/${id}/duplicate`, overrides)
    return response.data
  },

  // Create default budgets
  async createDefaultBudgets() {
    const response = await api.post('/budgets/create-defaults')
    return response.data
  },

  // Recalculate budget spending
  async recalculateBudgets(budgetId = null) {
    const response = await api.post('/budgets/recalculate', { 
      budget_id: budgetId 
    })
    return response.data
  }
}

export default budgetService