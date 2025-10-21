import { defineStore } from 'pinia'
import { ref } from 'vue'
import { expenseService } from '@/services/expenseService'
import { useNotificationsStore } from './notifications'

export const useExpensesStore = defineStore('expenses', () => {
  const expenses = ref([])
  const loading = ref(false)
  const error = ref(null)
  const totalExpenses = ref(0)
  
  // Get notifications store
  const notificationsStore = useNotificationsStore()

  // Pagination state
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
  })

  const links = ref({
    first: null,
    last: null,
    prev: null,
    next: null
  })

  const fetchExpenses = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await expenseService.getExpenses(filters)
      expenses.value = response.data

      // Update pagination info if available
      if (response.meta) {
        pagination.value = response.meta
      }
      if (response.links) {
        links.value = response.links
      }

      totalExpenses.value = response.meta?.total || response.data.length
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch expenses'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchAllExpenses = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await expenseService.getAllExpenses(filters)
      expenses.value = response.data
      totalExpenses.value = response.data.length
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch expenses'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createExpense = async expenseData => {
    loading.value = true
    error.value = null

    try {
      const response = await expenseService.createExpense(expenseData)
      expenses.value.unshift(response.data)
      totalExpenses.value++
      
      // Show notification
      notificationsStore.notifyExpenseAdded(response.data)
      
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create expense'
      
      // Show error notification
      notificationsStore.notifyError('Failed to Add Expense', err.response?.data?.message || 'An error occurred')
      
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
      
      // Show notification
      notificationsStore.notifySuccess('Expense Updated', `${response.data.description} has been updated`)
      
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update expense'
      
      // Show error notification
      notificationsStore.notifyError('Update Failed', err.response?.data?.message || 'Could not update expense')
      
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteExpense = async id => {
    loading.value = true
    error.value = null

    try {
      await expenseService.deleteExpense(id)
      expenses.value = expenses.value.filter(expense => expense.id !== id)
      totalExpenses.value--
      
      // Show notification
      notificationsStore.notifySuccess('Expense Deleted', 'The expense has been removed successfully')
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete expense'
      
      // Show error notification
      notificationsStore.notifyError('Delete Failed', err.response?.data?.message || 'Could not delete expense')
      
      throw err
    } finally {
      loading.value = false
    }
  }

  const searchExpenses = async query => {
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
    pagination,
    links,
    fetchExpenses,
    fetchAllExpenses,
    createExpense,
    updateExpense,
    deleteExpense,
    searchExpenses,
    getExpensesByDateRange
  }
})
