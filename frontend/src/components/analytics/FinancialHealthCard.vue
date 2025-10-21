<template>
  <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-white">Financial Health Score</h3>
          <p class="text-blue-100 text-sm">Overall financial wellness assessment</p>
        </div>
        <div class="text-right">
          <div :class="['text-3xl font-bold', healthScoreColor]">
            {{ displayScore }}
          </div>
          <div class="text-blue-100 text-sm">{{ healthStatus }}</div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="p-6">
      <div class="animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
        <div class="h-8 bg-gray-200 rounded w-full"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="p-6">
      <div class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Unable to load financial health data</h3>
            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
            <button @click="retryLoad" class="mt-2 bg-red-100 hover:bg-red-200 text-red-800 text-sm px-3 py-1 rounded">
              Try Again
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="financialHealth" class="p-6">
      <!-- Score Visualization -->
      <div class="mb-6">
        <div class="flex items-center justify-center mb-4">
          <div class="relative">
            <!-- Circular Progress -->
            <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 120 120">
              <!-- Background circle -->
              <circle cx="60" cy="60" r="50" stroke="#e5e7eb" stroke-width="8" fill="none" />
              <!-- Progress circle -->
              <circle
                cx="60"
                cy="60"
                r="50"
                :stroke="progressColor"
                stroke-width="8"
                fill="none"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="dashOffset"
                stroke-linecap="round"
                class="transition-all duration-1000 ease-out"
              />
            </svg>
            <!-- Score text -->
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="text-center">
                <div :class="['text-2xl font-bold', textScoreColor]">
                  {{ Math.round(overallScore) }}
                </div>
                <div class="text-gray-500 text-sm">out of 100</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Status Badge -->
        <div class="text-center">
          <span :class="['inline-flex px-3 py-1 rounded-full text-sm font-medium', statusBadgeClass]">
            {{ healthStatus }}
          </span>
        </div>
      </div>

      <!-- Component Scores -->
      <div class="space-y-4">
        <h4 class="font-medium text-gray-900 mb-3">Score Breakdown</h4>

        <div v-for="(score, key) in componentScores" :key="key" class="flex items-center justify-between">
          <span class="text-sm text-gray-600 capitalize">{{ formatScoreLabel(key) }}</span>
          <div class="flex items-center space-x-2">
            <div class="w-20 bg-gray-200 rounded-full h-2">
              <div
                :class="['h-2 rounded-full transition-all duration-500', getScoreBarColor(score)]"
                :style="{ width: `${Math.min(score, 100)}%` }"
              ></div>
            </div>
            <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ Math.round(score) }}</span>
          </div>
        </div>
      </div>

      <!-- Score Details -->
      <div v-if="scoreBreakdown && Object.keys(scoreBreakdown).length" class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="font-medium text-gray-900 mb-3">Financial Summary</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div v-if="scoreBreakdown.total_expenses">
            <div class="text-gray-600">Total Expenses</div>
            <div class="font-medium">${{ formatCurrency(scoreBreakdown.total_expenses) }}</div>
          </div>
          <div v-if="scoreBreakdown.total_budget">
            <div class="text-gray-600">Total Budget</div>
            <div class="font-medium">${{ formatCurrency(scoreBreakdown.total_budget) }}</div>
          </div>
          <div v-if="scoreBreakdown.budget_remaining !== undefined">
            <div class="text-gray-600">Budget Remaining</div>
            <div :class="['font-medium', scoreBreakdown.budget_remaining >= 0 ? 'text-green-600' : 'text-red-600']">
              ${{ formatCurrency(Math.abs(scoreBreakdown.budget_remaining)) }}
              {{ scoreBreakdown.budget_remaining < 0 ? 'over' : 'left' }}
            </div>
          </div>
          <div v-if="scoreBreakdown.period">
            <div class="text-gray-600">Period</div>
            <div class="font-medium capitalize">{{ scoreBreakdown.period }}</div>
          </div>
        </div>
      </div>

      <!-- Last Updated -->
      <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-between text-xs text-gray-500">
        <span>Last updated: {{ lastUpdated }}</span>
        <button
          @click="refreshData"
          :disabled="refreshing"
          class="text-blue-600 hover:text-blue-800 disabled:opacity-50"
        >
          <svg
            class="w-4 h-4 inline-block mr-1"
            :class="{ 'animate-spin': refreshing }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- No Data State -->
    <div v-else class="p-6 text-center">
      <div class="text-gray-400 mb-4">
        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
          />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Financial Health Data</h3>
      <p class="text-gray-600 mb-4">Add some expenses and budgets to see your financial health score.</p>
      <button @click="loadData" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
        Load Data
      </button>
    </div>
  </div>
</template>

<script>
  import { computed, onMounted, onUnmounted, ref } from 'vue'
  import { useAnalyticsStore } from '@/stores/analytics'

  export default {
    name: 'FinancialHealthCard',
    props: {
      period: {
        type: String,
        default: 'monthly'
      },
      autoRefresh: {
        type: Boolean,
        default: false
      }
    },
    setup(props) {
      const analyticsStore = useAnalyticsStore()
      const refreshing = ref(false)
      let autoRefreshInterval = null

      // Computed properties
      const loading = computed(() => analyticsStore.loading)
      const error = computed(() => analyticsStore.error)
      const financialHealth = computed(() => analyticsStore.financialHealth)

      const overallScore = computed(() => {
        return financialHealth.value?.data?.current?.overall_score || 0
      })

      const displayScore = computed(() => {
        return Math.round(overallScore.value)
      })

      const componentScores = computed(() => {
        if (!financialHealth.value?.data?.current) return {}

        const current = financialHealth.value.data.current
        return {
          budget_adherence: current.budget_adherence_score || 0,
          spending_consistency: current.spending_consistency_score || 0,
          savings_rate: current.savings_rate_score || 0,
          category_balance: current.category_balance_score || 0
        }
      })

      const scoreBreakdown = computed(() => {
        return financialHealth.value?.data?.current?.score_breakdown || {}
      })

      const healthStatus = computed(() => {
        const score = overallScore.value
        if (score >= 90) return 'Excellent'
        if (score >= 80) return 'Very Good'
        if (score >= 70) return 'Good'
        if (score >= 60) return 'Fair'
        if (score >= 50) return 'Poor'
        return 'Critical'
      })

      const healthScoreColor = computed(() => {
        const score = overallScore.value
        if (score >= 80) return 'text-white'
        if (score >= 60) return 'text-white'
        if (score >= 40) return 'text-white'
        return 'text-white'
      })

      const textScoreColor = computed(() => {
        const score = overallScore.value
        if (score >= 80) return 'text-green-600'
        if (score >= 60) return 'text-yellow-600'
        if (score >= 40) return 'text-orange-600'
        return 'text-red-600'
      })

      const progressColor = computed(() => {
        const score = overallScore.value
        if (score >= 80) return '#22c55e'
        if (score >= 60) return '#eab308'
        if (score >= 40) return '#f97316'
        return '#ef4444'
      })

      const statusBadgeClass = computed(() => {
        const score = overallScore.value
        if (score >= 80) return 'bg-green-100 text-green-800'
        if (score >= 60) return 'bg-yellow-100 text-yellow-800'
        if (score >= 40) return 'bg-orange-100 text-orange-800'
        return 'bg-red-100 text-red-800'
      })

      const circumference = computed(() => {
        return 2 * Math.PI * 50 // radius = 50
      })

      const dashOffset = computed(() => {
        const score = overallScore.value
        const offset = circumference.value - (score / 100) * circumference.value
        return offset
      })

      const lastUpdated = computed(() => {
        if (!financialHealth.value) return 'Never'
        const now = new Date()
        const diff = now - new Date(financialHealth.value.timestamp || Date.now())
        const minutes = Math.floor(diff / 60000)
        if (minutes < 1) return 'Just now'
        if (minutes < 60) return `${minutes} minutes ago`
        const hours = Math.floor(minutes / 60)
        if (hours < 24) return `${hours} hours ago`
        const days = Math.floor(hours / 24)
        return `${days} days ago`
      })

      // Methods
      const formatScoreLabel = key => {
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
      }

      const getScoreBarColor = score => {
        if (score >= 80) return 'bg-green-500'
        if (score >= 60) return 'bg-yellow-500'
        if (score >= 40) return 'bg-orange-500'
        return 'bg-red-500'
      }

      const formatCurrency = amount => {
        return new Intl.NumberFormat('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(Math.abs(amount))
      }

      const loadData = async () => {
        await analyticsStore.loadFinancialHealth(props.period)
      }

      const retryLoad = async () => {
        await loadData()
      }

      const refreshData = async () => {
        refreshing.value = true
        try {
          await analyticsStore.loadFinancialHealth(props.period)
        } finally {
          refreshing.value = false
        }
      }

      // Lifecycle
      onMounted(() => {
        loadData()

        // Auto refresh if enabled (with cleanup)
        if (props.autoRefresh) {
          autoRefreshInterval = setInterval(
            () => {
              if (!refreshing.value && !loading.value) {
                loadData()
              }
            },
            5 * 60 * 1000
          ) // 5 minutes
        }
      })

      onUnmounted(() => {
        // Clean up the auto-refresh interval to prevent memory leaks
        if (autoRefreshInterval) {
          clearInterval(autoRefreshInterval)
          autoRefreshInterval = null
        }
      })

      return {
        // Computed
        loading,
        error,
        financialHealth,
        overallScore,
        displayScore,
        componentScores,
        scoreBreakdown,
        healthStatus,
        healthScoreColor,
        textScoreColor,
        progressColor,
        statusBadgeClass,
        circumference,
        dashOffset,
        lastUpdated,

        // Reactive
        refreshing,

        // Methods
        formatScoreLabel,
        getScoreBarColor,
        formatCurrency,
        loadData,
        retryLoad,
        refreshData
      }
    }
  }
</script>

<style scoped>
  /* Additional custom styles if needed */
  .animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }

  @keyframes pulse {
    0%,
    100% {
      opacity: 1;
    }
    50% {
      opacity: 0.5;
    }
  }
</style>
