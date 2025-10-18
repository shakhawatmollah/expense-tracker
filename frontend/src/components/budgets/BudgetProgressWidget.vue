<template>
  <div class="budget-progress-widget">
    <!-- Widget Header -->
    <div class="widget-header">
      <div class="header-left">
        <h3 class="widget-title">Budget Progress</h3>
        <p class="widget-subtitle">Track your spending progress</p>
      </div>
      <div class="header-right">
        <select v-model="selectedPeriod" @change="loadBudgetData" class="period-select">
          <option value="current">Current Period</option>
          <option value="weekly">This Week</option>
          <option value="monthly">This Month</option>
          <option value="quarterly">This Quarter</option>
          <option value="yearly">This Year</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="budgetStore.isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading budget progress...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="budgetStore.error" class="error-state">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ budgetStore.error }}</p>
    </div>

    <!-- Content -->
    <div v-else class="widget-content">
      <!-- Overall Progress Ring -->
      <div class="overall-progress-ring">
        <div class="progress-ring">
          <svg class="progress-ring-svg" width="120" height="120">
            <circle
              class="progress-ring-circle-bg"
              stroke="#e5e7eb"
              stroke-width="8"
              fill="transparent"
              r="52"
              cx="60"
              cy="60"
            />
            <circle
              class="progress-ring-circle"
              :stroke="overallProgressColor"
              stroke-width="8"
              fill="transparent"
              r="52"
              cx="60"
              cy="60"
              :stroke-dasharray="circumference"
              :stroke-dashoffset="strokeDashoffset"
              stroke-linecap="round"
            />
          </svg>
          <div class="progress-ring-text">
            <div class="percentage">{{ budgetStore.overallBudgetProgress }}%</div>
            <div class="label">Used</div>
          </div>
        </div>
        <div class="progress-details">
          <div class="detail-item">
            <span class="amount">${{ formatAmount(budgetStore.totalSpentAmount) }}</span>
            <span class="label">Spent</span>
          </div>
          <div class="detail-item">
            <span class="amount">${{ formatAmount(budgetStore.totalBudgetAmount) }}</span>
            <span class="label">Budget</span>
          </div>
        </div>
      </div>

      <!-- Budget Breakdown -->
      <div v-if="displayBudgets.length > 0" class="budget-breakdown">
        <h4 class="section-title">Budget Breakdown</h4>
        <div class="budget-items">
          <div 
            v-for="budget in displayBudgets" 
            :key="budget.id"
            class="budget-item"
          >
            <div class="budget-header">
              <div class="budget-info">
                <span class="budget-name">{{ budget.name }}</span>
                <span class="budget-category">{{ budget.category?.name }}</span>
              </div>
              <div class="budget-amounts">
                <span class="spent-amount">${{ formatAmount(budget.spent_amount) }}</span>
                <span class="separator">/</span>
                <span class="total-amount">${{ formatAmount(budget.amount) }}</span>
              </div>
            </div>
            
            <div class="budget-progress-bar">
              <div class="progress-track">
                <div 
                  class="progress-fill"
                  :class="getBudgetProgressClass(budget)"
                  :style="{ width: `${Math.min(getBudgetProgress(budget), 100)}%` }"
                ></div>
              </div>
              <div class="progress-info">
                <span class="progress-percentage">{{ getBudgetProgress(budget) }}%</span>
                <span class="remaining-amount" :class="getRemainingAmountClass(budget)">
                  ${{ formatAmount(budget.remaining_amount) }} left
                </span>
              </div>
            </div>
            
            <!-- Status Indicators -->
            <div class="status-indicators">
              <span v-if="getBudgetProgress(budget) >= 100" class="status-badge critical">
                <i class="fas fa-exclamation-triangle"></i>
                Over Budget
              </span>
              <span v-else-if="getBudgetProgress(budget) >= 80" class="status-badge warning">
                <i class="fas fa-exclamation-circle"></i>
                Near Limit
              </span>
              <span v-else class="status-badge good">
                <i class="fas fa-check-circle"></i>
                On Track
              </span>
            </div>
          </div>
        </div>
        
        <!-- Show More Button -->
        <div v-if="budgetStore.activeBudgets.length > maxDisplayBudgets" class="show-more">
          <button @click="toggleShowAll" class="show-more-btn">
            {{ showAllBudgets ? 'Show Less' : `Show ${budgetStore.activeBudgets.length - maxDisplayBudgets} More` }}
            <i :class="showAllBudgets ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-chart-line"></i>
        <h4>No Active Budgets</h4>
        <p>Create budgets to track your spending progress</p>
        <router-link to="/budgets" class="btn btn-primary btn-sm">
          Create Budget
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useBudgetStore } from '@/stores/budget'
import { formatCurrency } from '@/utils/formatters'

export default {
  name: 'BudgetProgressWidget',
  setup() {
    const budgetStore = useBudgetStore()
    
    const selectedPeriod = ref('current')
    const showAllBudgets = ref(false)
    const maxDisplayBudgets = 4
    
    // Computed properties
    const circumference = computed(() => 2 * Math.PI * 52)
    
    const strokeDashoffset = computed(() => {
      const progress = budgetStore.overallBudgetProgress
      return circumference.value - (progress / 100) * circumference.value
    })
    
    const overallProgressColor = computed(() => {
      const progress = budgetStore.overallBudgetProgress
      if (progress >= 100) return '#ef4444' // red
      if (progress >= 80) return '#f97316' // orange
      if (progress >= 60) return '#eab308' // yellow
      return '#22c55e' // green
    })
    
    const displayBudgets = computed(() => {
      const budgets = budgetStore.activeBudgets.slice()
      
      // Sort by progress percentage (highest first)
      budgets.sort((a, b) => {
        const progressA = getBudgetProgress(a)
        const progressB = getBudgetProgress(b)
        return progressB - progressA
      })
      
      return showAllBudgets.value ? budgets : budgets.slice(0, maxDisplayBudgets)
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
    
    const getRemainingAmountClass = (budget) => {
      if (budget.remaining_amount < 0) return 'text-red-600'
      if (budget.remaining_amount < budget.amount * 0.2) return 'text-orange-600'
      return 'text-green-600'
    }
    
    const loadBudgetData = async () => {
      try {
        await Promise.all([
          budgetStore.fetchCurrentBudgets(),
          budgetStore.fetchBudgetSummary()
        ])
      } catch (error) {
        console.error('Failed to load budget data:', error)
      }
    }
    
    const toggleShowAll = () => {
      showAllBudgets.value = !showAllBudgets.value
    }
    
    // Initialize
    onMounted(async () => {
      await loadBudgetData()
    })
    
    return {
      budgetStore,
      selectedPeriod,
      showAllBudgets,
      maxDisplayBudgets,
      circumference,
      strokeDashoffset,
      overallProgressColor,
      displayBudgets,
      formatAmount,
      getBudgetProgress,
      getBudgetProgressClass,
      getRemainingAmountClass,
      loadBudgetData,
      toggleShowAll
    }
  }
}
</script>

<style scoped>
.budget-progress-widget {
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

.period-select {
  @apply text-sm border border-gray-300 rounded-md px-3 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
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

.overall-progress-ring {
  @apply flex flex-col items-center;
}

.progress-ring {
  @apply relative mb-4;
}

.progress-ring-svg {
  @apply transform -rotate-90;
}

.progress-ring-circle {
  @apply transition-all duration-300 ease-in-out;
}

.progress-ring-text {
  @apply absolute inset-0 flex flex-col items-center justify-center text-center;
}

.percentage {
  @apply text-2xl font-bold text-gray-800;
}

.progress-ring-text .label {
  @apply text-sm text-gray-600;
}

.progress-details {
  @apply flex gap-8;
}

.detail-item {
  @apply text-center;
}

.detail-item .amount {
  @apply block text-lg font-semibold text-gray-800;
}

.detail-item .label {
  @apply text-sm text-gray-600;
}

.section-title {
  @apply text-base font-medium text-gray-800 mb-4;
}

.budget-items {
  @apply space-y-4;
}

.budget-item {
  @apply p-4 bg-gray-50 rounded-lg;
}

.budget-header {
  @apply flex justify-between items-start mb-3;
}

.budget-info {
  @apply flex flex-col;
}

.budget-name {
  @apply font-medium text-gray-800;
}

.budget-category {
  @apply text-sm text-gray-600;
}

.budget-amounts {
  @apply text-sm text-gray-700;
}

.spent-amount {
  @apply font-semibold text-gray-800;
}

.separator {
  @apply mx-1;
}

.budget-progress-bar {
  @apply mb-3;
}

.progress-track {
  @apply w-full bg-gray-200 rounded-full h-2 mb-2;
}

.progress-fill {
  @apply h-2 rounded-full transition-all duration-300;
}

.progress-info {
  @apply flex justify-between items-center text-sm;
}

.progress-percentage {
  @apply font-medium text-gray-700;
}

.remaining-amount {
  @apply font-medium;
}

.status-indicators {
  @apply flex justify-end;
}

.status-badge {
  @apply inline-flex items-center px-2 py-1 rounded-full text-xs font-medium;
}

.status-badge.critical {
  @apply bg-red-100 text-red-800;
}

.status-badge.warning {
  @apply bg-orange-100 text-orange-800;
}

.status-badge.good {
  @apply bg-green-100 text-green-800;
}

.status-badge i {
  @apply mr-1;
}

.show-more {
  @apply text-center pt-4 border-t border-gray-200;
}

.show-more-btn {
  @apply text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center justify-center gap-1 w-full py-2;
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