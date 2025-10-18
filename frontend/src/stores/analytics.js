import { defineStore } from 'pinia'
import analyticsService from '@/services/analytics'

export const useAnalyticsStore = defineStore('analytics', {
  state: () => ({
    // Dashboard data
    dashboardData: null,
    
    // Spending patterns
    patterns: [],
    
    // Financial health
    financialHealth: null,
    
    // User insights
    insights: [],
    
    // Forecasts
    forecasts: null,
    
    // Recommendations
    recommendations: [],
    
    // Trends
    trends: null,
    
    // UI state
    loading: false,
    error: null,
    currentPeriod: 'monthly'
  }),

  getters: {
    // Get patterns by type
    getPatternsByType: (state) => (type) => {
      return state.patterns.filter(pattern => pattern.pattern_type === type)
    },

    // Get active patterns
    getActivePatterns: (state) => {
      return state.patterns.filter(pattern => pattern.is_active)
    },

    // Get high confidence patterns
    getHighConfidencePatterns: (state) => {
      return state.patterns.filter(pattern => pattern.confidence_score >= 80)
    },

    // Get financial health status
    getHealthStatus: (state) => {
      if (!state.financialHealth?.data?.current) return null
      
      const score = state.financialHealth.data.current.overall_score
      if (score >= 90) return { status: 'Excellent', color: '#22c55e' }
      if (score >= 80) return { status: 'Very Good', color: '#84cc16' }
      if (score >= 70) return { status: 'Good', color: '#eab308' }
      if (score >= 60) return { status: 'Fair', color: '#f97316' }
      if (score >= 50) return { status: 'Poor', color: '#ef4444' }
      return { status: 'Critical', color: '#dc2626' }
    },

    // Get recent insights
    getRecentInsights: (state) => {
      return state.insights.slice(0, 5)
    },

    // Get priority recommendations
    getPriorityRecommendations: (state) => {
      return state.recommendations.slice(0, 3)
    }
  },

  actions: {
    // Load dashboard data
    async loadDashboard(period = 'monthly') {
      this.loading = true
      this.error = null
      
      try {
        const response = await analyticsService.getDashboard(period)
        this.dashboardData = response.data
        this.currentPeriod = period
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load dashboard'
        console.error('Error loading dashboard:', error)
      } finally {
        this.loading = false
      }
    },

    // Load spending patterns
    async loadPatterns(type = null) {
      this.loading = true
      this.error = null
      
      try {
        const response = await analyticsService.getPatterns(type)
        this.patterns = response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load patterns'
        console.error('Error loading patterns:', error)
      } finally {
        this.loading = false
      }
    },

    // Load financial health
    async loadFinancialHealth(period = 'monthly') {
      this.loading = true
      this.error = null
      
      try {
        const response = await analyticsService.getFinancialHealth(period)
        this.financialHealth = response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load financial health'
        console.error('Error loading financial health:', error)
      } finally {
        this.loading = false
      }
    },

    // Load insights
    async loadInsights(period = 'monthly', type = null) {
      this.loading = true
      this.error = null
      
      try {
        const response = await analyticsService.getInsights(period, type)
        this.insights = response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load insights'
        console.error('Error loading insights:', error)
      } finally {
        this.loading = false
      }
    },

    // Load forecasts
    async loadForecasts(period = 'monthly') {
      this.loading = true
      this.error = null
      
      try {
        const response = await analyticsService.getForecasts(period)
        this.forecasts = response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load forecasts'
        console.error('Error loading forecasts:', error)
      } finally {
        this.loading = false
      }
    },

    // Load recommendations
    async loadRecommendations(period = 'monthly') {
      this.loading = true
      this.error = null
      
      try {
        const response = await analyticsService.getRecommendations(period)
        this.recommendations = response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load recommendations'
        console.error('Error loading recommendations:', error)
      } finally {
        this.loading = false
      }
    },

    // Load trends
    async loadTrends(period = 'monthly', categoryId = null) {
      this.loading = true
      this.error = null
      
      try {
        const response = await analyticsService.getTrends(period, categoryId)
        this.trends = response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load trends'
        console.error('Error loading trends:', error)
      } finally {
        this.loading = false
      }
    },

    // Refresh analytics
    async refreshAnalytics(period = 'monthly') {
      this.loading = true
      this.error = null
      
      try {
        const response = await analyticsService.refreshAnalytics(period)
        this.dashboardData = response.data
        
        // Reload other data as well
        await Promise.all([
          this.loadPatterns(),
          this.loadFinancialHealth(period),
          this.loadInsights(period),
          this.loadRecommendations(period)
        ])
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to refresh analytics'
        console.error('Error refreshing analytics:', error)
      } finally {
        this.loading = false
      }
    },

    // Load all analytics data
    async loadAllAnalytics(period = 'monthly') {
      this.loading = true
      this.error = null
      
      try {
        await Promise.all([
          this.loadDashboard(period),
          this.loadPatterns(),
          this.loadFinancialHealth(period),
          this.loadInsights(period),
          this.loadForecasts(period),
          this.loadRecommendations(period)
        ])
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load analytics'
        console.error('Error loading all analytics:', error)
      } finally {
        this.loading = false
      }
    },

    // Clear all data
    clearAnalytics() {
      this.dashboardData = null
      this.patterns = []
      this.financialHealth = null
      this.insights = []
      this.forecasts = null
      this.recommendations = []
      this.trends = null
      this.error = null
    },

    // Set period
    setPeriod(period) {
      this.currentPeriod = period
    }
  }
})