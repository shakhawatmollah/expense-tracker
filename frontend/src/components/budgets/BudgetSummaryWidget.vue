<template>
  <div class="budget-summary-widget">
    <!-- Widget Header -->
    <div class="widget-header">
      <div class="header-content">
        <div class="header-icon">
          <i class="fas fa-chart-pie"></i>
        </div>
        <div class="header-text">
          <h3 class="widget-title">Budget Overview</h3>
          <p class="widget-subtitle">Current period budget status</p>
        </div>
      </div>
      <div class="header-actions">
        <button @click="refreshData" class="refresh-btn" :disabled="budgetStore.isLoading" title="Refresh data">
          <i class="fas fa-sync-alt" :class="{ 'animate-spin': budgetStore.isLoading }"></i>
        </button>
        <router-link to="/budgets" class="view-all-btn">
          <i class="fas fa-external-link-alt"></i>
          View All
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="budgetStore.isLoading" class="loading-state">
      <div class="loading-skeleton">
        <div class="skeleton-stats">
          <div v-for="i in 3" :key="i" class="skeleton-stat">
            <div class="skeleton-value"></div>
            <div class="skeleton-label"></div>
          </div>
        </div>
        <div class="skeleton-progress"></div>
        <div class="skeleton-budgets">
          <div v-for="i in 3" :key="i" class="skeleton-budget"></div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="budgetStore.error" class="error-state">
      <div class="error-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <p>{{ budgetStore.error }}</p>
      <button @click="refreshData" class="retry-btn">Try Again</button>
    </div>

    <!-- Content -->
    <div v-else class="widget-content">
      <!-- Enhanced Summary Stats -->
      <div class="summary-stats">
        <div class="stat-card total-budget">
          <div class="stat-icon">
            <i class="fas fa-wallet"></i>
          </div>
          <div class="stat-content">
            <AnimatedCounter :value="budgetStore.totalBudgetAmount" :duration="1000" prefix="$" class="stat-value" />
            <div class="stat-label">Total Budget</div>
            <div class="stat-trend">
              <i class="fas fa-arrow-up trend-icon positive"></i>
              <span class="trend-text">Active</span>
            </div>
          </div>
        </div>

        <div class="stat-card total-spent">
          <div class="stat-icon">
            <i class="fas fa-credit-card"></i>
          </div>
          <div class="stat-content">
            <AnimatedCounter :value="budgetStore.totalSpentAmount" :duration="1200" prefix="$" class="stat-value" />
            <div class="stat-label">Total Spent</div>
            <div class="stat-trend">
              <i :class="spentTrendIcon" class="trend-icon"></i>
              <span class="trend-text">{{ spentTrendText }}</span>
            </div>
          </div>
        </div>

        <div class="stat-card remaining-budget">
          <div class="stat-icon">
            <i class="fas fa-piggy-bank"></i>
          </div>
          <div class="stat-content">
            <AnimatedCounter
              :value="budgetStore.totalRemainingAmount"
              :duration="1400"
              prefix="$"
              :class="['stat-value', remainingAmountClass]"
            />
            <div class="stat-label">Remaining</div>
            <div class="stat-trend">
              <i :class="remainingTrendIcon" class="trend-icon"></i>
              <span class="trend-text">{{ remainingTrendText }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Overall Progress -->
      <div class="overall-progress">
        <div class="progress-header">
          <span class="progress-label">Overall Progress</span>
          <div class="progress-stats">
            <AnimatedCounter
              :value="budgetStore.overallBudgetProgress"
              :duration="1500"
              suffix="%"
              class="progress-percentage"
            />
            <div class="progress-indicator" :class="overallProgressIndicator">
              <i :class="overallProgressIcon"></i>
            </div>
          </div>
        </div>

        <div class="enhanced-progress-bar">
          <div class="progress-track">
            <div
              class="progress-fill"
              :class="overallProgressClass"
              :style="{
                width: `${Math.min(budgetStore.overallBudgetProgress, 100)}%`,
                '--progress': `${Math.min(budgetStore.overallBudgetProgress, 100)}%`
              }"
            >
              <div class="progress-glow"></div>
            </div>
          </div>
          <div class="progress-markers">
            <div class="marker" style="left: 25%">25%</div>
            <div class="marker" style="left: 50%">50%</div>
            <div class="marker" style="left: 75%">75%</div>
          </div>
        </div>
      </div>

      <!-- Enhanced Budget List -->
      <div v-if="topBudgets.length > 0" class="budget-list-section">
        <div class="section-header">
          <h4 class="section-title">Active Budgets</h4>
          <span class="budget-count">{{ topBudgets.length }} of {{ budgetStore.activeBudgets.length }}</span>
        </div>

        <div class="enhanced-budget-list">
          <div
            v-for="(budget, index) in topBudgets"
            :key="budget.id"
            class="enhanced-budget-item"
            :style="{ '--delay': index * 0.1 + 's' }"
          >
            <div class="budget-avatar">
              <div class="budget-icon" :style="{ backgroundColor: budget.category?.color || '#6B7280' }">
                <i :class="budget.category?.icon || 'fas fa-tag'"></i>
              </div>
              <div v-if="getBudgetProgress(budget) >= 90" class="alert-badge">
                <i class="fas fa-exclamation"></i>
              </div>
            </div>

            <div class="budget-details">
              <div class="budget-header">
                <h5 class="budget-name">{{ budget.name }}</h5>
                <div class="budget-status" :class="getBudgetStatusClass(budget)">
                  <i :class="getBudgetStatusIcon(budget)"></i>
                  <span>{{ getBudgetStatusText(budget) }}</span>
                </div>
              </div>

              <div class="budget-category">
                <i class="fas fa-folder"></i>
                {{ budget.category?.name || 'Uncategorized' }}
              </div>

              <div class="budget-progress-section">
                <div class="amount-info">
                  <span class="spent-amount">${{ formatAmount(budget.spent_amount) }}</span>
                  <span class="separator">/</span>
                  <span class="total-amount">${{ formatAmount(budget.amount) }}</span>
                  <span class="progress-percent">{{ getBudgetProgress(budget) }}%</span>
                </div>

                <div class="animated-progress-bar">
                  <div class="progress-track">
                    <div
                      class="progress-fill"
                      :class="getBudgetProgressClass(budget)"
                      :style="{
                        width: `${Math.min(getBudgetProgress(budget), 100)}%`,
                        '--progress': `${Math.min(getBudgetProgress(budget), 100)}%`
                      }"
                    >
                      <div class="progress-shine"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="budget-actions">
              <button @click="viewBudgetDetails(budget)" class="action-btn view-btn" title="View details">
                <i class="fas fa-eye"></i>
              </button>
              <button @click="editBudget(budget)" class="action-btn edit-btn" title="Edit budget">
                <i class="fas fa-edit"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Show More Button -->
        <div v-if="budgetStore.activeBudgets.length > 5" class="show-more-section">
          <button @click="showAllBudgets = !showAllBudgets" class="show-more-btn">
            <i :class="showAllBudgets ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
            {{ showAllBudgets ? 'Show Less' : `Show ${budgetStore.activeBudgets.length - 5} More` }}
          </button>
        </div>
      </div>

      <!-- Enhanced Empty State -->
      <div v-else class="enhanced-empty-state">
        <div class="empty-animation">
          <div class="empty-icon">
            <i class="fas fa-chart-pie"></i>
          </div>
          <div class="empty-sparkles">
            <div class="sparkle sparkle-1"></div>
            <div class="sparkle sparkle-2"></div>
            <div class="sparkle sparkle-3"></div>
          </div>
        </div>
        <h4 class="empty-title">No Active Budgets</h4>
        <p class="empty-description">
          Create your first budget to start tracking spending and achieve your financial goals
        </p>
        <router-link to="/budgets" class="colorful-budget-btn create-budget-btn">
          <i class="fas fa-plus"></i>
          Create Budget
          <div class="btn-sparkles">
            <div class="sparkle sparkle-1"></div>
            <div class="sparkle sparkle-2"></div>
            <div class="sparkle sparkle-3"></div>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
  import { computed, ref, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useBudgetStore } from '@/stores/budget'
  import { formatCurrency } from '@/utils/formatters'
  import AnimatedCounter from '@/components/common/AnimatedCounter.vue'

  export default {
    name: 'BudgetSummaryWidget',
    components: {
      AnimatedCounter
    },
    setup() {
      const router = useRouter()
      const budgetStore = useBudgetStore()
      const showAllBudgets = ref(false)

      // Computed properties
      const topBudgets = computed(() => {
        const budgets = budgetStore.activeBudgets.slice()
        const limit = showAllBudgets.value ? budgets.length : 5
        return budgets.sort((a, b) => b.amount - a.amount).slice(0, limit)
      })

      const remainingAmountClass = computed(() => {
        const remaining = budgetStore.totalRemainingAmount
        if (remaining < 0) return 'negative'
        if (remaining < budgetStore.totalBudgetAmount * 0.2) return 'warning'
        return 'positive'
      })

      const overallProgressClass = computed(() => {
        const progress = budgetStore.overallBudgetProgress
        if (progress >= 100) return 'critical'
        if (progress >= 80) return 'warning'
        if (progress >= 60) return 'caution'
        return 'good'
      })

      const overallProgressIndicator = computed(() => {
        const progress = budgetStore.overallBudgetProgress
        if (progress >= 100) return 'critical'
        if (progress >= 80) return 'warning'
        return 'good'
      })

      const overallProgressIcon = computed(() => {
        const progress = budgetStore.overallBudgetProgress
        if (progress >= 100) return 'fas fa-exclamation-triangle'
        if (progress >= 80) return 'fas fa-exclamation-circle'
        return 'fas fa-check-circle'
      })

      const spentTrendIcon = computed(() => {
        const progress = budgetStore.overallBudgetProgress
        if (progress >= 80) return 'fas fa-arrow-up trend-icon negative'
        if (progress >= 50) return 'fas fa-minus trend-icon warning'
        return 'fas fa-arrow-down trend-icon positive'
      })

      const spentTrendText = computed(() => {
        const progress = budgetStore.overallBudgetProgress
        if (progress >= 80) return 'High'
        if (progress >= 50) return 'Moderate'
        return 'Low'
      })

      const remainingTrendIcon = computed(() => {
        const remaining = budgetStore.totalRemainingAmount
        if (remaining < 0) return 'fas fa-arrow-down trend-icon negative'
        if (remaining < budgetStore.totalBudgetAmount * 0.2) return 'fas fa-exclamation-circle trend-icon warning'
        return 'fas fa-arrow-up trend-icon positive'
      })

      const remainingTrendText = computed(() => {
        const remaining = budgetStore.totalRemainingAmount
        if (remaining < 0) return 'Exceeded'
        if (remaining < budgetStore.totalBudgetAmount * 0.2) return 'Low'
        return 'Healthy'
      })

      // Methods
      const formatAmount = amount => {
        return formatCurrency(amount)
      }

      const getBudgetProgress = budget => {
        if (budget.amount === 0) return 0
        return Math.round((budget.spent_amount / budget.amount) * 100)
      }

      const getBudgetProgressClass = budget => {
        const progress = getBudgetProgress(budget)
        if (progress >= 100) return 'critical'
        if (progress >= 80) return 'warning'
        if (progress >= 60) return 'caution'
        return 'good'
      }

      const getBudgetStatusClass = budget => {
        const progress = getBudgetProgress(budget)
        if (progress >= 100) return 'status-critical'
        if (progress >= 80) return 'status-warning'
        return 'status-good'
      }

      const getBudgetStatusIcon = budget => {
        const progress = getBudgetProgress(budget)
        if (progress >= 100) return 'fas fa-exclamation-triangle'
        if (progress >= 80) return 'fas fa-exclamation-circle'
        return 'fas fa-check-circle'
      }

      const getBudgetStatusText = budget => {
        const progress = getBudgetProgress(budget)
        if (progress >= 100) return 'Over Budget'
        if (progress >= 80) return 'Near Limit'
        return 'On Track'
      }

      const refreshData = async () => {
        try {
          await Promise.all([budgetStore.fetchCurrentBudgets(), budgetStore.fetchBudgetSummary()])
        } catch (error) {
          console.error('Failed to refresh budget data:', error)
        }
      }

      const viewBudgetDetails = budget => {
        router.push(`/budgets/${budget.id}`)
      }

      const editBudget = budget => {
        router.push(`/budgets?edit=${budget.id}`)
      }

      // Initialize
      onMounted(async () => {
        await refreshData()
      })

      return {
        budgetStore,
        showAllBudgets,
        topBudgets,
        remainingAmountClass,
        overallProgressClass,
        overallProgressIndicator,
        overallProgressIcon,
        spentTrendIcon,
        spentTrendText,
        remainingTrendIcon,
        remainingTrendText,
        formatAmount,
        getBudgetProgress,
        getBudgetProgressClass,
        getBudgetStatusClass,
        getBudgetStatusIcon,
        getBudgetStatusText,
        refreshData,
        viewBudgetDetails,
        editBudget
      }
    }
  }
</script>

<style scoped>
  /* Main Widget Container */
  .budget-summary-widget {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .budget-summary-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
  }

  /* Header Section */
  .widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(229, 231, 235, 0.8);
  }

  .header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .header-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
  }

  .header-text {
    flex: 1;
  }

  .widget-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
  }

  .widget-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0.25rem 0 0 0;
  }

  .header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .refresh-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: rgba(107, 114, 128, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .refresh-btn:hover:not(:disabled) {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    transform: scale(1.05);
  }

  .view-all-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 12px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .view-all-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  }

  /* Loading State */
  .loading-state {
    padding: 2rem 0;
  }

  .loading-skeleton {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  .skeleton-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
  }

  .skeleton-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
  }

  .skeleton-value {
    width: 80px;
    height: 1.5rem;
    background: #e5e7eb;
    border-radius: 8px;
    animation: pulse 2s infinite;
  }

  .skeleton-label {
    width: 60px;
    height: 1rem;
    background: #e5e7eb;
    border-radius: 4px;
    animation: pulse 2s infinite;
  }

  .skeleton-progress {
    width: 100%;
    height: 1rem;
    background: #e5e7eb;
    border-radius: 8px;
    animation: pulse 2s infinite;
  }

  .skeleton-budgets {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .skeleton-budget {
    width: 100%;
    height: 3rem;
    background: #e5e7eb;
    border-radius: 12px;
    animation: pulse 2s infinite;
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

  /* Error State */
  .error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
    text-align: center;
  }

  .error-icon {
    font-size: 2rem;
    color: #ef4444;
    margin-bottom: 1rem;
  }

  .retry-btn {
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .retry-btn:hover {
    background: #5a67d8;
  }

  /* Summary Stats */
  .summary-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .stat-card {
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(229, 231, 235, 0.8);
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  }

  .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.125rem;
  }

  .total-budget .stat-icon {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  }

  .total-spent .stat-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
  }

  .remaining-budget .stat-icon {
    background: linear-gradient(135deg, #10b981, #059669);
  }

  .stat-content {
    flex: 1;
  }

  .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
  }

  .stat-value.negative {
    color: #ef4444;
  }

  .stat-value.warning {
    color: #f59e0b;
  }

  .stat-value.positive {
    color: #10b981;
  }

  .stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
  }

  .stat-trend {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
  }

  .trend-icon {
    font-size: 0.75rem;
  }

  .trend-icon.positive {
    color: #10b981;
  }

  .trend-icon.warning {
    color: #f59e0b;
  }

  .trend-icon.negative {
    color: #ef4444;
  }

  .trend-text {
    font-weight: 500;
  }

  /* Enhanced Progress Bar */
  .overall-progress {
    margin-bottom: 2rem;
  }

  .progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }

  .progress-label {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
  }

  .progress-stats {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .progress-percentage {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
  }

  .progress-indicator {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
  }

  .progress-indicator.critical {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
  }

  .progress-indicator.warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
  }

  .progress-indicator.good {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
  }

  .enhanced-progress-bar {
    position: relative;
  }

  .progress-track {
    width: 100%;
    height: 12px;
    background: #f3f4f6;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
  }

  .progress-fill {
    height: 100%;
    border-radius: 6px;
    position: relative;
    transition: all 1.5s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
  }

  .progress-fill.critical {
    background: linear-gradient(90deg, #ef4444, #dc2626);
  }

  .progress-fill.warning {
    background: linear-gradient(90deg, #f59e0b, #d97706);
  }

  .progress-fill.caution {
    background: linear-gradient(90deg, #eab308, #ca8a04);
  }

  .progress-fill.good {
    background: linear-gradient(90deg, #10b981, #059669);
  }

  .progress-glow {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: progressGlow 2s ease-in-out infinite;
  }

  @keyframes progressGlow {
    0% {
      left: -100%;
    }
    100% {
      left: 100%;
    }
  }

  .progress-markers {
    position: absolute;
    top: -1.5rem;
    left: 0;
    right: 0;
    height: 1rem;
  }

  .marker {
    position: absolute;
    transform: translateX(-50%);
    font-size: 0.75rem;
    color: #9ca3af;
    font-weight: 500;
  }

  /* Budget List Section */
  .budget-list-section {
    margin-top: 2rem;
  }

  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }

  .section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
  }

  .budget-count {
    font-size: 0.875rem;
    color: #6b7280;
    background: rgba(107, 114, 128, 0.1);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
  }

  .enhanced-budget-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .enhanced-budget-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(229, 231, 235, 0.8);
    border-radius: 16px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: budgetSlideIn 0.6s ease-out;
    animation-delay: var(--delay);
    animation-fill-mode: both;
  }

  .enhanced-budget-item:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  }

  @keyframes budgetSlideIn {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .budget-avatar {
    position: relative;
    flex-shrink: 0;
  }

  .budget-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.125rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .alert-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 20px;
    height: 20px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    animation: badgePulse 2s infinite;
  }

  @keyframes badgePulse {
    0%,
    100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.1);
    }
  }

  .budget-details {
    flex: 1;
    min-width: 0;
  }

  .budget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
  }

  .budget-name {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
  }

  .budget-status {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
  }

  .status-critical {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
  }

  .status-warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
  }

  .status-good {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
  }

  .budget-category {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.75rem;
  }

  .budget-progress-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .amount-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
  }

  .spent-amount {
    font-weight: 600;
    color: #1f2937;
  }

  .separator {
    color: #9ca3af;
  }

  .total-amount {
    color: #6b7280;
  }

  .progress-percent {
    font-size: 0.75rem;
    font-weight: 600;
    color: #4b5563;
    background: rgba(107, 114, 128, 0.1);
    padding: 0.125rem 0.5rem;
    border-radius: 8px;
    margin-left: auto;
  }

  .animated-progress-bar {
    width: 100%;
  }

  .animated-progress-bar .progress-track {
    height: 6px;
    background: #f3f4f6;
    border-radius: 3px;
    overflow: hidden;
  }

  .animated-progress-bar .progress-fill {
    height: 100%;
    border-radius: 3px;
    position: relative;
    transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
  }

  .progress-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: progressShine 2s ease-in-out infinite;
    animation-delay: 0.5s;
  }

  @keyframes progressShine {
    0% {
      left: -100%;
    }
    100% {
      left: 100%;
    }
  }

  .budget-actions {
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .enhanced-budget-item:hover .budget-actions {
    opacity: 1;
  }

  .action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .view-btn {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
  }

  .view-btn:hover {
    background: rgba(59, 130, 246, 0.2);
    transform: scale(1.1);
  }

  .edit-btn {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
  }

  .edit-btn:hover {
    background: rgba(107, 114, 128, 0.2);
    transform: scale(1.1);
  }

  /* Show More Section */
  .show-more-section {
    text-align: center;
    margin-top: 1rem;
  }

  .show-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(229, 231, 235, 0.8);
    border-radius: 12px;
    color: #374151;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .show-more-btn:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  /* Enhanced Empty State */
  .enhanced-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    text-align: center;
  }

  .empty-animation {
    position: relative;
    margin-bottom: 2rem;
  }

  .empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #9ca3af;
    animation: floatBounce 3s ease-in-out infinite;
  }

  @keyframes floatBounce {
    0%,
    100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-10px);
    }
  }

  .empty-sparkles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
  }

  .sparkle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: #667eea;
    border-radius: 50%;
    opacity: 0;
    animation: sparkle-twinkle 2s infinite;
  }

  .sparkle-1 {
    top: 20%;
    left: 20%;
    animation-delay: 0s;
  }

  .sparkle-2 {
    top: 60%;
    right: 30%;
    animation-delay: 0.7s;
  }

  .sparkle-3 {
    bottom: 25%;
    left: 70%;
    animation-delay: 1.4s;
  }

  @keyframes sparkle-twinkle {
    0%,
    100% {
      opacity: 0;
      transform: scale(0);
    }
    50% {
      opacity: 1;
      transform: scale(1);
    }
  }

  .empty-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
  }

  .empty-description {
    font-size: 1rem;
    color: #6b7280;
    margin-bottom: 2rem;
    max-width: 400px;
    line-height: 1.6;
  }

  /* Colorful Budget Button */
  .colorful-budget-btn {
    position: relative;
    padding: 12px 24px;
    border: none;
    border-radius: 16px;
    font-weight: 700;
    font-size: 0.875rem;
    color: white;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    text-decoration: none;
  }

  .create-budget-btn {
    background: linear-gradient(45deg, #667eea 0%, #764ba2 20%, #f093fb 40%, #f5576c 60%, #4facfe 80%, #00f2fe 100%);
    background-size: 300% 300%;
    animation: rainbow-flow 3s ease infinite;
  }

  .create-budget-btn:hover {
    background-size: 400% 400%;
    animation-duration: 1.5s;
    box-shadow: 0 12px 40px rgba(102, 126, 234, 0.5);
    transform: translateY(-2px) scale(1.02);
  }

  .btn-sparkles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    overflow: hidden;
    border-radius: 16px;
  }

  @keyframes rainbow-flow {
    0%,
    100% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .summary-stats {
      grid-template-columns: 1fr;
      gap: 0.75rem;
    }

    .header-content {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.75rem;
    }

    .enhanced-budget-item {
      padding: 1rem;
      flex-direction: column;
      align-items: flex-start;
      gap: 0.75rem;
    }

    .budget-header {
      width: 100%;
    }

    .budget-actions {
      opacity: 1;
      align-self: flex-end;
    }
  }
</style>
