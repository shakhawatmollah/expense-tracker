import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { dashboardService } from '@/services/dashboardService'

export const useDashboardStore = defineStore('dashboard', () => {
  const overview = ref(null)
  const monthlySummary = ref(null)
  const yearlySummary = ref(null)
  const trends = ref([])
  const dailySpending = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchDashboardOverview = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await dashboardService.getDashboardOverview()
      overview.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard overview'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchMonthlySummary = async (period = 'current_month') => {
    loading.value = true
    error.value = null
    
    try {
      const response = await dashboardService.getMonthlySummary(period)
      monthlySummary.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch monthly summary'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchYearlySummary = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await dashboardService.getYearlySummary()
      yearlySummary.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch yearly summary'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchTrends = async (months = 6) => {
    loading.value = true
    error.value = null
    
    try {
      console.log('Fetching trends for', months, 'months...')
      const response = await dashboardService.getTrends(months)
      console.log('Raw trends response:', response)
      trends.value = response.data
      console.log('Trends value set:', trends.value)
    } catch (err) {
      console.error('Trends fetch error:', err)
      console.error('Error response:', err.response)
      error.value = err.response?.data?.message || 'Failed to fetch trends'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchDailySpending = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await dashboardService.getDailySpending()
      dailySpending.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch daily spending'
      throw err
    } finally {
      loading.value = false
    }
  }

  const refreshAll = async () => {
    await Promise.all([
      fetchDashboardOverview(),
      fetchTrends(),
      fetchDailySpending()
    ])
  }

  // Computed properties for safe numeric calculations
  const totalBalance = computed(() => {
    if (!overview.value) return 0
    return parseFloat(overview.value.total_balance || 0)
  })

  const todaysExpenses = computed(() => {
    if (!overview.value) return 0
    return parseFloat(overview.value.todays_expenses || 0)
  })

  const monthlyExpenses = computed(() => {
    if (!overview.value) return 0
    return parseFloat(overview.value.monthly_expenses || 0)
  })

  return {
    overview,
    monthlySummary,
    yearlySummary,
    trends,
    dailySpending,
    loading,
    error,
    // Computed properties
    totalBalance,
    todaysExpenses,
    monthlyExpenses,
    // Methods
    fetchDashboardOverview,
    fetchMonthlySummary,
    fetchYearlySummary,
    fetchTrends,
    fetchDailySpending,
    refreshAll
  }
})