<template>
  <div class="budget-summary-widget">
    <!-- Widget Header -->
    <div class="widget-header">
      <div class="header-left">
        <h3 class="widget-title">Budget Overview</h3>
        <p class="widget-subtitle">Current period budget status</p>
      </div>
      <div class="header-right">
        <router-link to="/budgets" class="view-all-link">
          View All
          <i class="fas fa-arrow-right ml-1"></i>
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="budgetStore.isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading budget data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="budgetStore.error" class="error-state">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ budgetStore.error }}</p>
    </div>

    <!-- Content -->
    <div v-else class="widget-content">
      <!-- Summary Stats -->
      <div class="summary-stats">
        <div class="stat-item">
          <div class="stat-value">${{ formatAmount(budgetStore.totalBudgetAmount) }}</div>
          <div class="stat-label">Total Budget</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">${{ formatAmount(budgetStore.totalSpentAmount) }}</div>
          <div class="stat-label">Total Spent</div>
        </div>
        <div class="stat-item">
          <div class="stat-value" :class="remainingAmountClass">
            ${{ formatAmount(budgetStore.totalRemainingAmount) }}
          </div>
          <div class="stat-label">Remaining</div>
        </div>
      </div>

      <!-- Overall Progress -->
      <div class="overall-progress">
        <div class="progress-header">
          <span class="progress-label">Overall Progress</span>
          <span class="progress-percentage">{{ budgetStore.overallBudgetProgress }}%</span>
        </div>
        <div class="progress-bar">
          <div 
            class="progress-fill"
            :class="overallProgressClass"
            :style="{ width: `${Math.min(budgetStore.overallBudgetProgress, 100)}%` }"
          ></div>
        </div>
      </div>

      <!-- Top Budgets -->
      <div v-if="topBudgets.length > 0" class="top-budgets">
        <h4 class="section-title">Active Budgets</h4>
        <div class="budget-list">
          <div 
            v-for="budget in topBudgets" 
            :key="budget.id"
            class="budget-item"
          >
            <div class="budget-info">
              <div class="budget-name">{{ budget.name }}</div>
              <div class="budget-category">{{ budget.category?.name }}</div>
            </div>
            <div class="budget-progress">
              <div class="amount-info">
                <span class="spent">${{ formatAmount(budget.spent_amount) }}</span>
                <span class="separator">/</span>
                <span class="total">${{ formatAmount(budget.amount) }}</span>
              </div>
              <div class="mini-progress-bar">
                <div 
                  class="mini-progress-fill"
                  :class="getBudgetProgressClass(budget)"
                  :style="{ width: `${Math.min(getBudgetProgress(budget), 100)}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-chart-pie"></i>
        <h4>No Active Budgets</h4>
        <p>Create your first budget to start tracking spending</p>
        <router-link to="/budgets" class="btn btn-primary btn-sm">
          Create Budget
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useBudgetStore } from '@/stores/budget'
import { formatCurrency } from '@/utils/formatters'

export default {
  name: 'BudgetSummaryWidget',
  setup() {
    const budgetStore = useBudgetStore()
    
    // Computed properties
    const topBudgets = computed(() => {
      return budgetStore.activeBudgets
        .slice()
        .sort((a, b) => b.amount - a.amount)
        .slice(0, 5) // Show top 5 budgets
    })
    
    const remainingAmountClass = computed(() => {
      const remaining = budgetStore.totalRemainingAmount
      if (remaining < 0) return 'text-red-600'
      if (remaining < budgetStore.totalBudgetAmount * 0.2) return 'text-orange-600'
      return 'text-green-600'
    })
    
    const overallProgressClass = computed(() => {
      const progress = budgetStore.overallBudgetProgress
      if (progress >= 100) return 'bg-red-500'
      if (progress >= 80) return 'bg-orange-500'
      if (progress >= 60) return 'bg-yellow-500'
      return 'bg-green-500'
    })
    
    // Methods
    const formatAmount = (amount) => {
      return formatCurrency(amount)
    }
    
    const getBudgetProgress = (budget) => {
      if (budget.amount === 0) return 0
      return Math.round((budget.spent_amount / budget.amount) * 100)
    }
    
    const getBudgetProgressClass = (budget) => {
      const progress = getBudgetProgress(budget)
      if (progress >= 100) return 'bg-red-500'
      if (progress >= 80) return 'bg-orange-500'
      if (progress >= 60) return 'bg-yellow-500'
      return 'bg-green-500'
    }
    
    // Initialize
    onMounted(async () => {
      try {
        await Promise.all([
          budgetStore.fetchCurrentBudgets(),
          budgetStore.fetchBudgetSummary()
        ])
      } catch (error) {
        console.error('Failed to load budget data:', error)
      }
    })
    
    return {
      budgetStore,
      topBudgets,
      remainingAmountClass,
      overallProgressClass,
      formatAmount,
      getBudgetProgress,
      getBudgetProgressClass
    }
  }
}
</script>

<style scoped>
.budget-summary-widget {
  @apply bg-white rounded-lg shadow-sm border p-6;
}

.widget-header {
  @apply flex justify-between items-start mb-6;
}

.widget-title {
  @apply text-lg font-semibold text-gray-800;
}

.widget-subtitle {
  @apply text-sm text-gray-600 mt-1;
}

.view-all-link {
  @apply text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center;
}

.loading-state {
  @apply flex flex-col items-center justify-center py-8;
}

.loading-spinner {
  @apply w-6 h-6 border-2 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-2;
}

.error-state {
  @apply flex flex-col items-center justify-center py-8 text-center text-gray-500;
}

.widget-content {
  @apply space-y-6;
}

.summary-stats {
  @apply grid grid-cols-3 gap-4;
}

.stat-item {
  @apply text-center;
}

.stat-value {
  @apply text-xl font-bold text-gray-800;
}

.stat-label {
  @apply text-sm text-gray-600 mt-1;
}

.overall-progress {
  @apply space-y-2;
}

.progress-header {
  @apply flex justify-between items-center;
}

.progress-label {
  @apply text-sm font-medium text-gray-700;
}

.progress-percentage {
  @apply text-sm font-semibold text-gray-800;
}

.progress-bar {
  @apply w-full bg-gray-200 rounded-full h-3;
}

.progress-fill {
  @apply h-3 rounded-full transition-all duration-300;
}

.section-title {
  @apply text-base font-medium text-gray-800 mb-3;
}

.budget-list {
  @apply space-y-3;
}

.budget-item {
  @apply flex justify-between items-center;
}

.budget-info {
  @apply flex-1 min-w-0;
}

.budget-name {
  @apply text-sm font-medium text-gray-800 truncate;
}

.budget-category {
  @apply text-xs text-gray-500;
}

.budget-progress {
  @apply flex-shrink-0 text-right min-w-24;
}

.amount-info {
  @apply text-xs text-gray-600 mb-1;
}

.spent {
  @apply font-medium text-gray-800;
}

.separator {
  @apply mx-1;
}

.mini-progress-bar {
  @apply w-16 bg-gray-200 rounded-full h-1;
}

.mini-progress-fill {
  @apply h-1 rounded-full transition-all duration-300;
}

.empty-state {
  @apply text-center py-8;
}

.empty-state i {
  @apply text-4xl text-gray-400 mb-3;
}

.empty-state h4 {
  @apply text-lg font-medium text-gray-700 mb-2;
}

.empty-state p {
  @apply text-gray-600 mb-4;
}
</style>