<template>
  <div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3 mb-2">
            <i class="fas fa-chart-pie text-blue-600"></i>
            Budget Management
          </h1>
          <p class="text-gray-600">Track, manage, and optimize your spending budgets</p>
        </div>

        <div class="flex gap-3">
          <button
            @click="refreshData"
            :disabled="loading"
            class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors flex items-center gap-2"
          >
            <i class="fas fa-sync-alt" :class="{ 'animate-spin': loading }"></i>
            Refresh
          </button>
          <button
            @click="showCreateModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
          >
            <i class="fas fa-plus"></i>
            Create Budget
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer border-0 text-left w-full"
      >
        <div class="flex items-center">
          <div class="flex-1">
            <div class="text-2xl font-bold text-gray-900 mb-1">${{ formatAmount(budgetStore.totalBudgetAmount) }}</div>
            <div class="text-sm text-gray-600">Total Budget</div>
            <div class="text-xs font-medium text-green-600 flex items-center gap-1 mt-1">
              <i class="fas fa-arrow-up"></i>
              +12% from last month
            </div>
          </div>
          <div class="text-2xl text-blue-600 ml-3">
            <i class="fas fa-wallet"></i>
          </div>
        </div>
      </div>

      <div class="stat-card stat-warning">
        <div class="stat-icon">
          <i class="fas fa-credit-card"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">${{ formatAmount(budgetStore.totalSpentAmount) }}</div>
          <div class="stat-label">Total Spent</div>
          <div class="stat-change" :class="spentChangeClass">
            <i :class="spentChangeIcon"></i>
            {{ spentChangeText }}
          </div>
        </div>
      </div>

      <div class="stat-card stat-success">
        <div class="stat-icon">
          <i class="fas fa-piggy-bank"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">${{ formatAmount(budgetStore.totalRemainingAmount) }}</div>
          <div class="stat-label">Remaining</div>
          <div class="stat-percentage">{{ remainingPercentage }}% of budget</div>
        </div>
      </div>

      <div class="stat-card stat-info">
        <div class="stat-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ budgetStore.activeBudgets.length }}</div>
          <div class="stat-label">Active Budgets</div>
          <div class="stat-meta">{{ budgetStore.budgets.length }} total</div>
        </div>
      </div>
    </div>

    <!-- Budget Alerts -->
    <div v-if="budgetStore.budgetAlerts.length > 0" class="alerts-section">
      <div class="section-header">
        <h2 class="section-title">
          <i class="fas fa-exclamation-triangle"></i>
          Budget Alerts
        </h2>
        <span class="alert-count">{{ budgetStore.budgetAlerts.length }}</span>
      </div>

      <div class="alerts-grid">
        <div
          v-for="alert in budgetStore.budgetAlerts"
          :key="alert.id"
          :class="['alert-card', alert.level === 'critical' ? 'alert-critical' : 'alert-warning']"
        >
          <div class="alert-header">
            <div class="alert-icon">
              <i :class="alert.level === 'critical' ? 'fas fa-exclamation-triangle' : 'fas fa-exclamation-circle'"></i>
            </div>
            <div class="alert-info">
              <h3 class="alert-title">{{ alert.budget_name }}</h3>
              <p class="alert-message">{{ alert.message }}</p>
            </div>
          </div>
          <div class="alert-actions">
            <button @click="viewBudgetDetails(alert.budget_id)" class="btn btn-sm btn-outline">View Details</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-section">
      <div class="section-header">
        <h2 class="section-title">
          <i class="fas fa-bolt"></i>
          Quick Actions
        </h2>
      </div>

      <div class="actions-grid">
        <button @click="createDefaultBudgets" :disabled="loading" class="action-card">
          <div class="action-icon">
            <i class="fas fa-magic"></i>
          </div>
          <div class="action-content">
            <h3>Create Default Budgets</h3>
            <p>Set up budgets for common categories</p>
          </div>
        </button>

        <button @click="recalculateAllBudgets" :disabled="loading" class="action-card">
          <div class="action-icon">
            <i class="fas fa-calculator"></i>
          </div>
          <div class="action-content">
            <h3>Recalculate Spending</h3>
            <p>Refresh all budget calculations</p>
          </div>
        </button>

        <button @click="exportBudgetReport" :disabled="loading" class="action-card">
          <div class="action-icon">
            <i class="fas fa-file-export"></i>
          </div>
          <div class="action-content">
            <h3>Export Report</h3>
            <p>Download budget analysis report</p>
          </div>
        </button>

        <button @click="showBudgetAnalytics = true" class="action-card">
          <div class="action-icon">
            <i class="fas fa-chart-bar"></i>
          </div>
          <div class="action-content">
            <h3>Budget Analytics</h3>
            <p>View detailed spending analysis</p>
          </div>
        </button>
      </div>
    </div>

    <!-- Budget Categories Overview -->
    <div class="categories-section">
      <div class="section-header">
        <h2 class="section-title">
          <i class="fas fa-layer-group"></i>
          Budget by Category
        </h2>
        <div class="view-toggle">
          <button @click="viewMode = 'grid'" :class="['toggle-btn', { active: viewMode === 'grid' }]">
            <i class="fas fa-th"></i>
          </button>
          <button @click="viewMode = 'chart'" :class="['toggle-btn', { active: viewMode === 'chart' }]">
            <i class="fas fa-chart-pie"></i>
          </button>
        </div>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="categories-grid">
        <div v-for="(budgets, categoryName) in budgetStore.budgetsByCategory" :key="categoryName" class="category-card">
          <div class="category-header">
            <h3 class="category-name">{{ categoryName }}</h3>
            <span class="budget-count">{{ budgets.length }} budget{{ budgets.length !== 1 ? 's' : '' }}</span>
          </div>

          <div class="category-stats">
            <div class="category-amount">
              <div class="amount-row">
                <span class="amount-label">Spent:</span>
                <span class="amount-value">${{ formatAmount(getCategorySpent(budgets)) }}</span>
              </div>
              <div class="amount-row">
                <span class="amount-label">Budget:</span>
                <span class="amount-value">${{ formatAmount(getCategoryBudget(budgets)) }}</span>
              </div>
            </div>

            <div class="category-progress">
              <div class="progress-bar">
                <div
                  class="progress-fill"
                  :class="getCategoryProgressClass(budgets)"
                  :style="{ width: `${getCategoryProgress(budgets)}%` }"
                ></div>
              </div>
              <span class="progress-text">{{ getCategoryProgress(budgets) }}%</span>
            </div>
          </div>

          <div class="category-actions">
            <button @click="viewCategoryBudgets(categoryName)" class="btn btn-sm btn-outline">View Budgets</button>
          </div>
        </div>
      </div>

      <!-- Chart View -->
      <div v-else class="chart-container">
        <BudgetCategoryChart :data="categoryChartData" />
      </div>
    </div>

    <!-- Budget Analytics Modal -->
    <BudgetAnalyticsModal v-if="showBudgetAnalytics" @close="showBudgetAnalytics = false" />

    <!-- Create Budget Modal -->
    <BudgetModal
      v-if="showCreateModal"
      :budget="null"
      :is-editing="false"
      @close="showCreateModal = false"
      @saved="handleBudgetSaved"
    />
  </div>
</template>

<script>
  import { ref, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useBudgetStore } from '@/stores/budget'
  import { useCategoriesStore } from '@/stores/categories'
  import { formatCurrency } from '@/utils/formatters'
  import BudgetCategoryChart from './BudgetCategoryChart.vue'
  import BudgetAnalyticsModal from './BudgetAnalyticsModal.vue'
  import BudgetModal from './BudgetModal.vue'

  export default {
    name: 'BudgetOverview',
    components: {
      BudgetCategoryChart,
      BudgetAnalyticsModal,
      BudgetModal
    },
    setup() {
      const router = useRouter()
      const budgetStore = useBudgetStore()
      const categoryStore = useCategoriesStore()

      // State
      const loading = ref(false)
      const viewMode = ref('grid')
      const showCreateModal = ref(false)
      const showBudgetAnalytics = ref(false)
      const selectedBudgetId = ref(null)

      // Computed properties
      const remainingPercentage = computed(() => {
        if (budgetStore.totalBudgetAmount === 0) return 0
        return Math.round((budgetStore.totalRemainingAmount / budgetStore.totalBudgetAmount) * 100)
      })

      const spentChangeClass = computed(() => {
        // This would normally come from API comparing to previous period
        const change = 5 // Mock data - would be calculated
        return change > 0 ? 'negative' : 'positive'
      })

      const spentChangeIcon = computed(() => {
        return spentChangeClass.value === 'positive' ? 'fas fa-arrow-down' : 'fas fa-arrow-up'
      })

      const spentChangeText = computed(() => {
        // Mock data - would come from API
        return spentChangeClass.value === 'positive' ? '-3% from last month' : '+5% from last month'
      })

      const categoryChartData = computed(() => {
        const data = []
        Object.entries(budgetStore.budgetsByCategory).forEach(([categoryName, budgets]) => {
          data.push({
            name: categoryName,
            value: getCategorySpent(budgets),
            budget: getCategoryBudget(budgets)
          })
        })
        return data
      })

      // Methods
      const formatAmount = amount => {
        return formatCurrency(amount)
      }

      const getCategorySpent = budgets => {
        return budgets.reduce((sum, budget) => sum + budget.spent_amount, 0)
      }

      const getCategoryBudget = budgets => {
        return budgets.reduce((sum, budget) => sum + budget.amount, 0)
      }

      const getCategoryProgress = budgets => {
        const spent = getCategorySpent(budgets)
        const budget = getCategoryBudget(budgets)
        if (budget === 0) return 0
        return Math.round((spent / budget) * 100)
      }

      const getCategoryProgressClass = budgets => {
        const progress = getCategoryProgress(budgets)
        if (progress >= 100) return 'progress-critical'
        if (progress >= 80) return 'progress-warning'
        return 'progress-success'
      }

      const refreshData = async () => {
        loading.value = true
        try {
          await Promise.all([
            budgetStore.fetchBudgets(),
            budgetStore.fetchCurrentBudgets(),
            budgetStore.fetchBudgetSummary(),
            budgetStore.fetchBudgetAlerts()
          ])
        } catch (error) {
          console.error('Failed to refresh budget data:', error)
        } finally {
          loading.value = false
        }
      }

      const createDefaultBudgets = async () => {
        loading.value = true
        try {
          await budgetStore.createDefaultBudgets()
          await refreshData()
        } catch (error) {
          console.error('Failed to create default budgets:', error)
        } finally {
          loading.value = false
        }
      }

      const recalculateAllBudgets = async () => {
        loading.value = true
        try {
          await budgetStore.recalculateBudgets()
          await refreshData()
        } catch (error) {
          console.error('Failed to recalculate budgets:', error)
        } finally {
          loading.value = false
        }
      }

      const exportBudgetReport = () => {
        try {
          // Prepare budget data for export
          const exportData = {
            generated_at: new Date().toISOString(),
            summary: {
              total_budget: budgetStore.totalBudgetAmount,
              total_spent: budgetStore.totalSpentAmount,
              total_remaining: budgetStore.totalRemainingAmount,
              percentage_used: Math.round((budgetStore.totalSpentAmount / budgetStore.totalBudgetAmount) * 100)
            },
            budgets: []
          }

          // Collect all budgets with details
          Object.entries(budgetStore.budgetsByCategory).forEach(([categoryName, budgets]) => {
            budgets.forEach(budget => {
              exportData.budgets.push({
                category: categoryName,
                amount: budget.amount,
                spent: budget.spent,
                remaining: budget.amount - budget.spent,
                percentage_used: Math.round((budget.spent / budget.amount) * 100),
                start_date: budget.start_date,
                end_date: budget.end_date,
                status:
                  budget.spent >= budget.amount
                    ? 'exceeded'
                    : budget.spent >= budget.amount * 0.9
                      ? 'critical'
                      : budget.spent >= budget.amount * 0.75
                        ? 'warning'
                        : 'good'
              })
            })
          })

          // Create CSV content
          const csvHeaders = [
          'Category',
          'Budget Amount',
          'Spent',
          'Remaining',
          'Usage %',
          'Start Date',
          'End Date',
          'Status'
        ]
          const csvRows = exportData.budgets.map(budget => [
            budget.category,
            budget.amount.toFixed(2),
            budget.spent.toFixed(2),
            budget.remaining.toFixed(2),
            budget.percentage_used + '%',
            budget.start_date,
            budget.end_date,
            budget.status
          ])

          const csvContent = [csvHeaders.join(','), ...csvRows.map(row => row.join(','))].join('\n')

          // Create and download file
          const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
          const link = document.createElement('a')
          const url = URL.createObjectURL(blob)
          const timestamp = new Date().toISOString().split('T')[0]

          link.setAttribute('href', url)
          link.setAttribute('download', `budget_report_${timestamp}.csv`)
          link.style.visibility = 'hidden'
          document.body.appendChild(link)
          link.click()
          document.body.removeChild(link)

          console.log('Budget report exported successfully')
        } catch (error) {
          console.error('Failed to export budget report:', error)
        }
      }

      const viewBudgetDetails = budgetId => {
        // Navigate to the budgets page with manage tab and highlight the specific budget
        router.push({
          path: '/budgets',
          query: {
            tab: 'manage',
            budgetId: budgetId
          }
        })
      }

      const viewCategoryBudgets = categoryName => {
        // Navigate to the budgets page with manage tab filtered by category
        router.push({
          path: '/budgets',
          query: {
            tab: 'manage',
            category: categoryName
          }
        })
      }

      const handleBudgetSaved = async () => {
        showCreateModal.value = false
        await refreshData()
      }

      // Initialize
      onMounted(async () => {
        await Promise.all([categoryStore.fetchCategories(), refreshData()])
      })

      return {
        budgetStore,
        loading,
        viewMode,
        showCreateModal,
        showBudgetAnalytics,
        remainingPercentage,
        spentChangeClass,
        spentChangeIcon,
        spentChangeText,
        categoryChartData,
        formatAmount,
        getCategorySpent,
        getCategoryBudget,
        getCategoryProgress,
        getCategoryProgressClass,
        refreshData,
        createDefaultBudgets,
        recalculateAllBudgets,
        exportBudgetReport,
        viewBudgetDetails,
        viewCategoryBudgets,
        handleBudgetSaved
      }
    }
  }
</script>

<style scoped>
  .budget-overview {
    @apply p-6 bg-gray-50 min-h-screen;
  }

  .overview-header {
    @apply flex justify-between items-start mb-8 bg-white rounded-lg shadow-sm p-6;
  }

  .header-content {
    @apply flex-1;
  }

  .page-title {
    @apply text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3;
  }

  .page-title i {
    @apply text-blue-600;
  }

  .page-subtitle {
    @apply text-gray-600 text-lg;
  }

  .header-actions {
    @apply flex gap-3;
  }

  .stats-grid {
    @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8;
  }

  .stat-card {
    @apply bg-white rounded-lg shadow-sm p-6 flex items-center;
  }

  .stat-primary {
    @apply border-l-4 border-blue-500;
  }

  .stat-warning {
    @apply border-l-4 border-orange-500;
  }

  .stat-success {
    @apply border-l-4 border-green-500;
  }

  .stat-info {
    @apply border-l-4 border-purple-500;
  }

  .stat-icon {
    @apply text-3xl mr-4;
  }

  .stat-primary .stat-icon {
    @apply text-blue-500;
  }

  .stat-warning .stat-icon {
    @apply text-orange-500;
  }

  .stat-success .stat-icon {
    @apply text-green-500;
  }

  .stat-info .stat-icon {
    @apply text-purple-500;
  }

  .stat-content {
    @apply flex-1;
  }

  .stat-value {
    @apply text-2xl font-bold text-gray-900 mb-1;
  }

  .stat-label {
    @apply text-sm text-gray-600 mb-1;
  }

  .stat-change {
    @apply text-xs font-medium flex items-center gap-1;
  }

  .stat-change.positive {
    @apply text-green-600;
  }

  .stat-change.negative {
    @apply text-red-600;
  }

  .stat-percentage {
    @apply text-xs text-gray-500;
  }

  .stat-meta {
    @apply text-xs text-gray-500;
  }

  .alerts-section {
    @apply mb-8;
  }

  .section-header {
    @apply flex justify-between items-center mb-4;
  }

  .section-title {
    @apply text-xl font-semibold text-gray-900 flex items-center gap-2;
  }

  .alert-count {
    @apply bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium;
  }

  .alerts-grid {
    @apply grid grid-cols-1 md:grid-cols-2 gap-4;
  }

  .alert-card {
    @apply bg-white rounded-lg shadow-sm p-4 flex justify-between items-center;
  }

  .alert-critical {
    @apply border-l-4 border-red-500;
  }

  .alert-warning {
    @apply border-l-4 border-orange-500;
  }

  .alert-header {
    @apply flex items-center flex-1;
  }

  .alert-icon {
    @apply text-xl mr-3;
  }

  .alert-critical .alert-icon {
    @apply text-red-500;
  }

  .alert-warning .alert-icon {
    @apply text-orange-500;
  }

  .alert-title {
    @apply font-medium text-gray-900 mb-1;
  }

  .alert-message {
    @apply text-sm text-gray-600;
  }

  .quick-actions-section {
    @apply mb-8;
  }

  .actions-grid {
    @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4;
  }

  .action-card {
    @apply bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer border-0 text-left w-full;
  }

  .action-icon {
    @apply text-2xl text-blue-600 mb-3;
  }

  .action-content h3 {
    @apply font-semibold text-gray-900 mb-1;
  }

  .action-content p {
    @apply text-sm text-gray-600;
  }

  .categories-section {
    @apply mb-8;
  }

  .view-toggle {
    @apply flex gap-1 bg-gray-100 rounded-lg p-1;
  }

  .toggle-btn {
    @apply px-3 py-1 rounded-md text-sm font-medium transition-colors;
  }

  .toggle-btn.active {
    @apply bg-white text-blue-600 shadow-sm;
  }

  .categories-grid {
    @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6;
  }

  .category-card {
    @apply bg-white rounded-lg shadow-sm p-6;
  }

  .category-header {
    @apply flex justify-between items-center mb-4;
  }

  .category-name {
    @apply font-semibold text-gray-900;
  }

  .budget-count {
    @apply text-xs text-gray-500;
  }

  .category-stats {
    @apply mb-4;
  }

  .category-amount {
    @apply mb-3;
  }

  .amount-row {
    @apply flex justify-between items-center mb-1;
  }

  .amount-label {
    @apply text-sm text-gray-600;
  }

  .amount-value {
    @apply text-sm font-medium text-gray-900;
  }

  .category-progress {
    @apply flex items-center gap-3;
  }

  .progress-bar {
    @apply flex-1 bg-gray-200 rounded-full h-2;
  }

  .progress-fill {
    @apply h-2 rounded-full transition-all duration-300;
  }

  .progress-success {
    @apply bg-green-500;
  }

  .progress-warning {
    @apply bg-orange-500;
  }

  .progress-critical {
    @apply bg-red-500;
  }

  .progress-text {
    @apply text-xs font-medium text-gray-600;
  }

  .chart-container {
    background: white;
    border-radius: 0.5rem;
    box-shadow:
      0 1px 3px 0 rgba(0, 0, 0, 0.1),
      0 1px 2px 0 rgba(0, 0, 0, 0.06);
    padding: 1.5rem;
  }

  /* Enhanced Create Budget Button Styling */
  .create-budget-btn {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border: none;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    position: relative;
    overflow: hidden;
  }

  .create-budget-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
  }

  .create-budget-btn:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
  }

  .create-budget-btn:hover::before {
    left: 100%;
  }

  .create-budget-btn:active {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
  }

  .create-budget-btn:focus {
    outline: none;
    box-shadow:
      0 0 0 3px rgba(59, 130, 246, 0.1),
      0 4px 12px rgba(59, 130, 246, 0.25);
  }

  .create-budget-btn .fas {
    font-size: 12px;
    margin-right: 0;
  }

  .create-budget-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
  }

  .create-budget-btn:disabled:hover {
    transform: none;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
  }
</style>
