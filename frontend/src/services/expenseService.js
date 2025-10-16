import api from './api'

export const expenseService = {
  async getExpenses(filters = {}) {
    // Add pagination by default
    const params = new URLSearchParams({
      paginate: 'true',
      ...filters
    })
    const response = await api.get(`/expenses?${params}`)
    return response.data
  },

  async getAllExpenses(filters = {}) {
    // Get all expenses without pagination
    const params = new URLSearchParams({
      paginate: 'false',
      ...filters
    })
    const response = await api.get(`/expenses?${params}`)
    return response.data
  },

  async getExpense(id) {
    const response = await api.get(`/expenses/${id}`)
    return response.data
  },

  async createExpense(expenseData) {
    const response = await api.post('/expenses', expenseData)
    return response.data
  },

  async updateExpense(id, expenseData) {
    const response = await api.put(`/expenses/${id}`, expenseData)
    return response.data
  },

  async deleteExpense(id) {
    const response = await api.delete(`/expenses/${id}`)
    return response.data
  },

  async searchExpenses(query) {
    const response = await api.get(`/expenses/search?query=${encodeURIComponent(query)}`)
    return response.data
  },

  async getExpensesByDateRange(startDate, endDate) {
    const response = await api.get(`/expenses/date-range?start_date=${startDate}&end_date=${endDate}`)
    return response.data
  }
}