import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import budgetService from '@/services/budgetService'

export const useBudgetStore = defineStore('budget', () => {
  // State
  const budgets = ref([])
  const currentBudgets = ref([])
  const budgetSummary = ref(null)
  const budgetAlerts = ref([])
  const isLoading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1
  })

  // Getters
  const activeBudgets = computed(() => budgets.value.filter(budget => budget.is_active))

  const budgetsByCategory = computed(() => {
    const grouped = {}
    budgets.value.forEach(budget => {
      const categoryName = budget.category?.name || 'Uncategorized'
      if (!grouped[categoryName]) {
        grouped[categoryName] = []
      }
      grouped[categoryName].push(budget)
    })
    return grouped
  })

  const criticalAlerts = computed(() => budgetAlerts.value.filter(alert => alert.level === 'critical'))

  const warningAlerts = computed(() => budgetAlerts.value.filter(alert => alert.level === 'warning'))

  const totalBudgetAmount = computed(() => activeBudgets.value.reduce((sum, budget) => sum + budget.amount, 0))

  const totalSpentAmount = computed(() => activeBudgets.value.reduce((sum, budget) => sum + budget.spent_amount, 0))

  const totalRemainingAmount = computed(() => totalBudgetAmount.value - totalSpentAmount.value)

  const overallBudgetProgress = computed(() => {
    if (totalBudgetAmount.value === 0) return 0
    return Math.round((totalSpentAmount.value / totalBudgetAmount.value) * 100)
  })

  // Actions
  // Helper to normalize budget shape coming from API
  function normalizeBudget(b) {
    if (!b) return b

    // API returns amount/spent_amount/remaining_amount as { raw, formatted }
    const amount = b.amount && typeof b.amount === 'object' ? (b.amount.raw ?? 0) : (b.amount ?? 0)
    const spent =
      b.spent_amount && typeof b.spent_amount === 'object' ? (b.spent_amount.raw ?? 0) : (b.spent_amount ?? 0)
    const remaining =
      b.remaining_amount && typeof b.remaining_amount === 'object'
        ? (b.remaining_amount.raw ?? 0)
        : (b.remaining_amount ?? amount - spent)

    // Handle period object - API might return period as object with type, label, etc.
    let period = b.period
    let periodLabel = ''
    let daysRemaining = null
    let startDate = null
    let endDate = null

    if (period && typeof period === 'object') {
      periodLabel = period.label || period.type || ''
      daysRemaining = period.days_remaining || null
      startDate = period.start_date || null
      endDate = period.end_date || null
      period = period.type || 'monthly' // Extract the actual period type for backward compatibility
    }

    return {
      ...b,
      amount,
      spent_amount: spent,
      remaining_amount: remaining,
      period,
      period_label: periodLabel,
      days_remaining: daysRemaining,
      // PRESERVE the original period object AND add individual date fields
      period_object: b.period, // Keep original period object with dates
      start_date: startDate || b.start_date, // Extract dates to root level too
      end_date: endDate || b.end_date
    }
  }

  function normalizeBudgets(arr) {
    if (!arr || !Array.isArray(arr)) return []
    return arr.map(normalizeBudget)
  }

  async function fetchBudgets(params = {}) {
    try {
      isLoading.value = true
      error.value = null

      const response = await budgetService.getBudgets(params)
      budgets.value = normalizeBudgets(response.data) || []

      if (response.meta) {
        pagination.value = response.meta
      }

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch budgets'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function fetchCurrentBudgets() {
    try {
      isLoading.value = true
      error.value = null

      const response = await budgetService.getCurrentBudgets()
      currentBudgets.value = normalizeBudgets(response.data) || []

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch current budgets'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function fetchBudgetSummary() {
    try {
      error.value = null

      const response = await budgetService.getBudgetSummary()
      // Normalize budget summary data in case it contains budget objects
      const summary = response.data
      if (summary && typeof summary === 'object') {
        // If summary contains budget objects, normalize them
        if (summary.budgets && Array.isArray(summary.budgets)) {
          summary.budgets = normalizeBudgets(summary.budgets)
        }
        // If summary contains current_period object, normalize it
        if (summary.current_period && typeof summary.current_period === 'object') {
          summary.current_period = normalizeBudget(summary.current_period)
        }
      }
      budgetSummary.value = summary

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch budget summary'
      throw err
    }
  }

  async function fetchBudgetAlerts() {
    try {
      error.value = null

      const response = await budgetService.getBudgetAlerts()
      // Normalize alerts data in case it contains budget objects
      const alerts = response.data || []
      budgetAlerts.value = Array.isArray(alerts)
        ? alerts.map(alert => {
            if (alert.budget) {
              alert.budget = normalizeBudget(alert.budget)
            }
            return alert
          })
        : []

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch budget alerts'
      throw err
    }
  }

  async function createBudget(budgetData) {
    try {
      isLoading.value = true
      error.value = null

      const response = await budgetService.createBudget(budgetData)

      // Add to budgets array (normalize shape)
      budgets.value.push(normalizeBudget(response.data))

      // If it's an active budget, add to current budgets
      if (response.data.is_active) {
        currentBudgets.value.push(normalizeBudget(response.data))
      }

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create budget'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function updateBudget(id, budgetData) {
    try {
      isLoading.value = true
      error.value = null

      const response = await budgetService.updateBudget(id, budgetData)

      // Update in budgets array
      const index = budgets.value.findIndex(budget => budget.id === id)
      if (index !== -1) {
        budgets.value[index] = normalizeBudget(response.data)
      }

      // Update in current budgets array if exists
      const currentIndex = currentBudgets.value.findIndex(budget => budget.id === id)
      if (currentIndex !== -1) {
        if (response.data.is_active) {
          currentBudgets.value[currentIndex] = normalizeBudget(response.data)
        } else {
          currentBudgets.value.splice(currentIndex, 1)
        }
      } else if (response.data.is_active) {
        currentBudgets.value.push(normalizeBudget(response.data))
      }

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update budget'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function deleteBudget(id) {
    try {
      isLoading.value = true
      error.value = null

      await budgetService.deleteBudget(id)

      // Remove from budgets array
      budgets.value = budgets.value.filter(budget => budget.id !== id)

      // Remove from current budgets array
      currentBudgets.value = currentBudgets.value.filter(budget => budget.id !== id)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete budget'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function duplicateBudget(id, overrides = {}) {
    try {
      isLoading.value = true
      error.value = null

      const response = await budgetService.duplicateBudget(id, overrides)

      // Add duplicated budget to array
      budgets.value.push(normalizeBudget(response.data))

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to duplicate budget'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function createDefaultBudgets() {
    try {
      isLoading.value = true
      error.value = null

      const response = await budgetService.createDefaultBudgets()

      // Add new budgets to array
      if (response.data && Array.isArray(response.data)) {
        const normalized = normalizeBudgets(response.data)
        budgets.value.push(...normalized)

        // Add active ones to current budgets
        const activeBudgets = normalized.filter(budget => budget.is_active)
        currentBudgets.value.push(...activeBudgets)
      }

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create default budgets'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function recalculateBudgets(budgetId = null) {
    try {
      error.value = null

      const response = await budgetService.recalculateBudgets(budgetId)

      // Refresh current data
      await Promise.all([fetchCurrentBudgets(), fetchBudgetSummary(), fetchBudgetAlerts()])

      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to recalculate budgets'
      throw err
    }
  }

  function clearError() {
    error.value = null
  }

  function resetPagination() {
    pagination.value = {
      current_page: 1,
      per_page: 10,
      total: 0,
      last_page: 1
    }
  }

  function $reset() {
    budgets.value = []
    currentBudgets.value = []
    budgetSummary.value = null
    budgetAlerts.value = []
    isLoading.value = false
    error.value = null
    resetPagination()
  }

  return {
    // State
    budgets,
    currentBudgets,
    budgetSummary,
    budgetAlerts,
    isLoading,
    error,
    pagination,

    // Getters
    activeBudgets,
    budgetsByCategory,
    criticalAlerts,
    warningAlerts,
    totalBudgetAmount,
    totalSpentAmount,
    totalRemainingAmount,
    overallBudgetProgress,

    // Actions
    fetchBudgets,
    fetchCurrentBudgets,
    fetchBudgetSummary,
    fetchBudgetAlerts,
    createBudget,
    updateBudget,
    deleteBudget,
    duplicateBudget,
    createDefaultBudgets,
    recalculateBudgets,
    clearError,
    resetPagination,
    $reset
  }
})
