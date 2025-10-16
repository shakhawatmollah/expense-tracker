import { defineStore } from 'pinia'
import { ref } from 'vue'
import { expenseService } from '@/services/expenseService'

export const useExpensesStore = defineStore('expenses', () => {
  const expenses = ref([])
  const loading = ref(false)
  const error = ref(null)
  const totalExpenses = ref(0)

  const fetchExpenses = async (filters = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await expenseService.getExpenses(filters)
      expenses.value = response.data
      totalExpenses.value = response.data.length
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch expenses'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createExpense = async (expenseData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await expenseService.createExpense(expenseData)
      expenses.value.unshift(response.data)
      totalExpenses.value++
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create expense'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateExpense = async (id, expenseData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await expenseService.updateExpense(id, expenseData)
      const index = expenses.value.findIndex(expense => expense.id === id)
      if (index !== -1) {
        expenses.value[index] = response.data
      }
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update expense'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteExpense = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      await expenseService.deleteExpense(id)
      expenses.value = expenses.value.filter(expense => expense.id !== id)
      totalExpenses.value--
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete expense'
      throw err
    } finally {
      loading.value = false
    }
  }

  const searchExpenses = async (query) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await expenseService.searchExpenses(query)
      expenses.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to search expenses'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getExpensesByDateRange = async (startDate, endDate) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await expenseService.getExpensesByDateRange(startDate, endDate)
      expenses.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch expenses by date range'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    expenses,
    loading,
    error,
    totalExpenses,
    fetchExpenses,
    createExpense,
    updateExpense,
    deleteExpense,
    searchExpenses,
    getExpensesByDateRange
  }
})