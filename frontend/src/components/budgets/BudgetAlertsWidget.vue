<template>
  <div class="budget-alerts-widget">
    <!-- Widget Header -->
    <div class="widget-header">
      <div class="header-left">
        <h3 class="widget-title">Budget Alerts</h3>
        <p class="widget-subtitle">Important budget notifications</p>
      </div>
      <div class="header-right">
        <button 
          @click="refreshAlerts" 
          class="refresh-btn"
          :disabled="budgetStore.isLoading"
        >
          <i class="fas fa-sync-alt" :class="{ 'animate-spin': budgetStore.isLoading }"></i>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="budgetStore.isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading alerts...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="budgetStore.error" class="error-state">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ budgetStore.error }}</p>
    </div>

    <!-- Alerts Content -->
    <div v-else class="widget-content">
      <!-- Alert Summary -->
      <div v-if="hasAlerts" class="alert-summary">
        <div class="summary-item critical" v-if="budgetStore.criticalAlerts.length > 0">
          <div class="summary-icon">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div class="summary-text">
            <span class="count">{{ budgetStore.criticalAlerts.length }}</span>
            <span class="label">Critical</span>
          </div>
        </div>
        
        <div class="summary-item warning" v-if="budgetStore.warningAlerts.length > 0">
          <div class="summary-icon">
            <i class="fas fa-exclamation-circle"></i>
          </div>
          <div class="summary-text">
            <span class="count">{{ budgetStore.warningAlerts.length }}</span>
            <span class="label">Warning</span>
          </div>
        </div>
      </div>

      <!-- Alert List -->
      <div v-if="displayAlerts.length > 0" class="alert-list">
        <div 
          v-for="alert in displayAlerts" 
          :key="alert.id || `${alert.budget_id}-${alert.level}`"
          :class="[
            'alert-item',
            alert.level === 'critical' ? 'alert-critical' : 'alert-warning'
          ]"
        >
          <div class="alert-icon">
            <i :class="alert.level === 'critical' ? 'fas fa-exclamation-triangle' : 'fas fa-exclamation-circle'"></i>
          </div>
          <div class="alert-content">
            <div class="alert-header">
              <h4 class="alert-budget">{{ alert.budget_name }}</h4>
              <span class="alert-level">{{ alert.level }}</span>
            </div>
            <p class="alert-message">{{ alert.message }}</p>
            <div class="alert-details">
              <span class="spent-amount">${{ formatAmount(alert.spent_amount) }}</span>
              <span class="separator">/</span>
              <span class="budget-amount">${{ formatAmount(alert.budget_amount) }}</span>
              <span class="percentage">({{ alert.percentage }}%)</span>
            </div>
          </div>
          <div class="alert-actions">
            <button 
              @click="viewBudgetDetails(alert.budget_id)" 
              class="action-btn"
              title="View Budget"
            >
              <i class="fas fa-eye"></i>
            </button>
            <button 
              @click="dismissAlert(alert)" 
              class="action-btn"
              title="Dismiss Alert"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- No Alerts State -->
      <div v-else class="no-alerts-state">
        <div class="success-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h4>All Good!</h4>
        <p>No budget alerts at this time. Your spending is on track.</p>
      </div>

      <!-- Show More Button -->
      <div v-if="budgetStore.budgetAlerts.length > maxDisplayAlerts" class="show-more">
        <button @click="toggleShowAll" class="show-more-btn">
          {{ showAllAlerts ? 'Show Less' : `Show ${budgetStore.budgetAlerts.length - maxDisplayAlerts} More` }}
          <i :class="showAllAlerts ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useBudgetStore } from '@/stores/budget'
import { useRouter } from 'vue-router'
import { formatCurrency } from '@/utils/formatters'

export default {
  name: 'BudgetAlertsWidget',
  setup() {
    const budgetStore = useBudgetStore()
    const router = useRouter()
    
    const showAllAlerts = ref(false)
    const maxDisplayAlerts = 3
    
    // Computed properties
    const hasAlerts = computed(() => {
      return budgetStore.budgetAlerts.length > 0
    })
    
    const displayAlerts = computed(() => {
      const alerts = budgetStore.budgetAlerts.slice()
      
      // Sort by level (critical first) then by percentage
      alerts.sort((a, b) => {
        if (a.level === 'critical' && b.level !== 'critical') return -1
        if (b.level === 'critical' && a.level !== 'critical') return 1
        return b.percentage - a.percentage
      })
      
      return showAllAlerts.value ? alerts : alerts.slice(0, maxDisplayAlerts)
    })
    
    // Methods
    const formatAmount = (amount) => {
      return formatCurrency(amount)
    }
    
    const refreshAlerts = async () => {
      try {
        await Promise.all([
          budgetStore.fetchBudgetAlerts(),
          budgetStore.fetchCurrentBudgets()
        ])
      } catch (error) {
        console.error('Failed to refresh alerts:', error)
      }
    }
    
    const viewBudgetDetails = (budgetId) => {
      router.push(`/budgets/${budgetId}`)
    }
    
    const dismissAlert = (alert) => {
      // In a real app, you might want to call an API to dismiss the alert
      // For now, we'll just remove it from the local state
      const index = budgetStore.budgetAlerts.findIndex(a => 
        a.budget_id === alert.budget_id && a.level === alert.level
      )
      if (index !== -1) {
        budgetStore.budgetAlerts.splice(index, 1)
      }
    }
    
    const toggleShowAll = () => {
      showAllAlerts.value = !showAllAlerts.value
    }
    
    // Initialize
    onMounted(async () => {
      try {
        await budgetStore.fetchBudgetAlerts()
      } catch (error) {
        console.error('Failed to load budget alerts:', error)
      }
    })
    
    return {
      budgetStore,
      showAllAlerts,
      maxDisplayAlerts,
      hasAlerts,
      displayAlerts,
      formatAmount,
      refreshAlerts,
      viewBudgetDetails,
      dismissAlert,
      toggleShowAll
    }
  }
}
</script>

<style scoped>
.budget-alerts-widget {
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

.refresh-btn {
  @apply text-gray-400 hover:text-gray-600 p-2 rounded-md hover:bg-gray-50 transition-colors;
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
  @apply space-y-4;
}

.alert-summary {
  @apply flex gap-4 mb-4;
}

.summary-item {
  @apply flex items-center px-3 py-2 rounded-lg;
}

.summary-item.critical {
  @apply bg-red-100 text-red-800;
}

.summary-item.warning {
  @apply bg-orange-100 text-orange-800;
}

.summary-icon {
  @apply mr-2;
}

.summary-text {
  @apply flex items-center gap-1;
}

.count {
  @apply font-bold text-lg;
}

.label {
  @apply text-sm font-medium;
}

.alert-list {
  @apply space-y-3;
}

.alert-item {
  @apply flex items-start p-4 rounded-lg border-l-4;
}

.alert-critical {
  @apply bg-red-50 border-red-500;
}

.alert-warning {
  @apply bg-orange-50 border-orange-500;
}

.alert-icon {
  @apply mr-3 mt-0.5 text-lg;
}

.alert-critical .alert-icon {
  @apply text-red-500;
}

.alert-warning .alert-icon {
  @apply text-orange-500;
}

.alert-content {
  @apply flex-1 min-w-0;
}

.alert-header {
  @apply flex items-center justify-between mb-1;
}

.alert-budget {
  @apply font-medium text-gray-800 text-sm;
}

.alert-level {
  @apply text-xs px-2 py-1 rounded-full font-medium uppercase;
}

.alert-critical .alert-level {
  @apply bg-red-200 text-red-800;
}

.alert-warning .alert-level {
  @apply bg-orange-200 text-orange-800;
}

.alert-message {
  @apply text-sm text-gray-700 mb-2;
}

.alert-details {
  @apply text-xs text-gray-600;
}

.spent-amount {
  @apply font-medium text-gray-800;
}

.separator {
  @apply mx-1;
}

.percentage {
  @apply font-medium;
}

.alert-actions {
  @apply flex flex-col gap-1 ml-2;
}

.action-btn {
  @apply text-gray-400 hover:text-gray-600 p-1 rounded transition-colors;
}

.no-alerts-state {
  @apply text-center py-8;
}

.success-icon {
  @apply text-4xl text-green-500 mb-3;
}

.no-alerts-state h4 {
  @apply text-lg font-medium text-gray-700 mb-2;
}

.no-alerts-state p {
  @apply text-gray-600;
}

.show-more {
  @apply text-center pt-2 border-t border-gray-100;
}

.show-more-btn {
  @apply text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center justify-center gap-1 w-full py-2;
}
</style>